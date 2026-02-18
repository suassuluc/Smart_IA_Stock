"""
API de previsão de esgotamento de estoque.
Utiliza modelo de ML (scikit-learn) para aprender padrões de venda e prever consumo.
Fallback para média simples quando há poucos dados.
"""
from datetime import date, timedelta
from typing import Any

import pandas as pd
from fastapi import FastAPI
from pydantic import BaseModel
from sklearn.ensemble import RandomForestRegressor
from sklearn.preprocessing import LabelEncoder

app = FastAPI(title="Stock Prediction API", version="2.0")

LOOKBACK_DAYS = 90
MIN_DAYS_WITH_SALES_FOR_ML = 14
TARGET_WINDOW_DAYS = 7
RANDOM_STATE = 42

# Nomes das colunas de features (para garantir ordem consistente)
FEATURE_NAMES = [
    "avg_7d",
    "avg_14d",
    "avg_28d",
    "avg_84d",
    "trend",
    "days_with_sales_30",
    "weekday_0",
    "weekday_1",
    "weekday_2",
    "weekday_3",
    "weekday_4",
    "weekday_5",
    "weekday_6",
]


class ProductInput(BaseModel):
    id: int
    stock_quantity: int


class SaleRecordInput(BaseModel):
    product_id: int
    sold_at: str  # YYYY-MM-DD
    quantity: int


class PredictRequest(BaseModel):
    products: list[ProductInput]
    sales_history: list[SaleRecordInput]


class PredictionOutput(BaseModel):
    product_id: int
    predicted_until: str  # YYYY-MM-DD
    predicted_quantity: int
    days_until_stockout: int | None


class PredictResponse(BaseModel):
    predictions: list[PredictionOutput]


def _sales_to_daily_df(sales_history: list[dict]) -> pd.DataFrame:
    """Converte histórico de vendas em DataFrame diário (product_id, date, quantity)."""
    if not sales_history:
        return pd.DataFrame(columns=["product_id", "sold_at", "quantity"])
    df = pd.DataFrame(sales_history)
    df["sold_at"] = pd.to_datetime(df["sold_at"]).dt.date
    daily = df.groupby(["product_id", "sold_at"], as_index=False)["quantity"].sum()
    return daily


def _features_for_product_at_date(
    daily: pd.DataFrame,
    product_id: int,
    ref_date: date,
) -> dict[str, float] | None:
    """
    Calcula features para um produto em uma data de referência.
    Usa apenas dados anteriores a ref_date.
    Retorna None se não houver dados suficientes.
    """
    product_daily = daily[daily["product_id"] == product_id].copy()
    if product_daily.empty:
        return None
    product_daily = product_daily[product_daily["sold_at"] < ref_date].sort_values("sold_at")
    if len(product_daily) < MIN_DAYS_WITH_SALES_FOR_ML:
        return None

    # Janelas (em dias)
    cutoff_7 = ref_date - timedelta(days=7)
    cutoff_14 = ref_date - timedelta(days=14)
    cutoff_28 = ref_date - timedelta(days=28)
    cutoff_84 = ref_date - timedelta(days=84)

    def mean_daily(sub: pd.DataFrame) -> float:
        if sub.empty:
            return 0.0
        total = sub["quantity"].sum()
        days = (sub["sold_at"].max() - sub["sold_at"].min()).days or 1
        return total / max(days, 1)

    sub_7 = product_daily[product_daily["sold_at"] >= cutoff_7]
    sub_14 = product_daily[product_daily["sold_at"] >= cutoff_14]
    sub_28 = product_daily[product_daily["sold_at"] >= cutoff_28]
    sub_84 = product_daily[product_daily["sold_at"] >= cutoff_84]

    avg_7d = mean_daily(sub_7) if len(sub_7) > 0 else 0.0
    avg_14d = mean_daily(sub_14) if len(sub_14) > 0 else 0.0
    avg_28d = mean_daily(sub_28) if len(sub_28) > 0 else 0.0
    avg_84d = mean_daily(sub_84) if len(sub_84) > 0 else 0.0

    # Tendência: média última semana vs. média das 3 semanas anteriores
    prev_21_start = ref_date - timedelta(days=28)
    sub_prev_21 = product_daily[
        (product_daily["sold_at"] >= prev_21_start) & (product_daily["sold_at"] < cutoff_7)
    ]
    avg_prev_21 = mean_daily(sub_prev_21) if len(sub_prev_21) > 0 else 1e-6
    trend = avg_7d / (avg_prev_21 + 1e-9)

    cutoff_30 = ref_date - timedelta(days=30)
    sub_30 = product_daily[product_daily["sold_at"] >= cutoff_30]
    days_with_sales_30 = sub_30["sold_at"].nunique() if len(sub_30) > 0 else 0

    # Média por dia da semana (0=segunda, 6=domingo) nos últimos 28 dias
    weekday_avgs = [0.0] * 7
    if len(sub_28) > 0:
        sub_28_copy = sub_28.copy()
        sub_28_copy["weekday"] = pd.to_datetime(sub_28_copy["sold_at"]).dt.dayofweek
        by_wd = sub_28_copy.groupby("weekday")["quantity"].sum()
        for wd in range(7):
            weekday_avgs[wd] = float(by_wd.get(wd, 0)) / max(len(sub_28_copy[sub_28_copy["weekday"] == wd]), 1)

    return {
        "avg_7d": avg_7d,
        "avg_14d": avg_14d,
        "avg_28d": avg_28d,
        "avg_84d": avg_84d,
        "trend": trend,
        "days_with_sales_30": float(days_with_sales_30),
        **{f"weekday_{i}": weekday_avgs[i] for i in range(7)},
    }


def build_training_data(
    daily: pd.DataFrame,
    product_ids: list[int],
) -> tuple[pd.DataFrame, pd.Series] | None:
    """
    Constrói matriz de features e vetor target para treino.
    Uma linha por (product_id, ref_date): features até ref_date, target = consumo médio diário em [ref_date, ref_date+7).
    """
    rows = []
    for product_id in product_ids:
        product_daily = daily[daily["product_id"] == product_id]
        if product_daily.empty or len(product_daily) < MIN_DAYS_WITH_SALES_FOR_ML:
            continue
        min_d = product_daily["sold_at"].min()
        max_d = product_daily["sold_at"].max()
        # ref_date avança de 7 em 7 dias; precisamos de 84 dias antes e 7 dias de target depois
        ref = min_d + timedelta(days=84)
        while ref + timedelta(days=TARGET_WINDOW_DAYS) <= max_d:
            feats = _features_for_product_at_date(daily, product_id, ref)
            if feats is None:
                ref += timedelta(days=7)
                continue
            # Target: consumo médio diário nos próximos 7 dias
            target_start = ref
            target_end = ref + timedelta(days=TARGET_WINDOW_DAYS)
            future = product_daily[
                (product_daily["sold_at"] >= target_start)
                & (product_daily["sold_at"] < target_end)
            ]
            if future.empty:
                target_val = 0.0
            else:
                target_val = future["quantity"].sum() / TARGET_WINDOW_DAYS
            rows.append({"product_id": product_id, **feats, "target": target_val})
            ref += timedelta(days=7)

    if len(rows) < 5:
        return None
    df = pd.DataFrame(rows)
    X = df[FEATURE_NAMES + ["product_id"]].copy()
    X["product_id"] = X["product_id"].astype(int)
    y = df["target"]
    return X, y


def train_model(X: pd.DataFrame, y: pd.Series, product_encoder: LabelEncoder):
    """Treina um RandomForest global com product_id codificado."""
    X_enc = X.copy()
    X_enc["product_id"] = product_encoder.transform(X["product_id"].astype(str))
    model = RandomForestRegressor(n_estimators=50, max_depth=8, random_state=RANDOM_STATE, min_samples_leaf=2)
    model.fit(X_enc, y)
    return model


def predict_daily_consumption(
    features: dict[str, float],
    product_id: int,
    model,
    product_encoder: LabelEncoder,
) -> float:
    """Prevê consumo médio diário para um produto usando o modelo treinado."""
    try:
        pid_enc = product_encoder.transform([str(product_id)])[0]
    except (ValueError, IndexError):
        return features.get("avg_28d", 0.0) or features.get("avg_7d", 0.0)
    row = {n: features.get(n, 0.0) for n in FEATURE_NAMES}
    row["product_id"] = pid_enc
    X = pd.DataFrame([row], columns=FEATURE_NAMES + ["product_id"])
    pred = model.predict(X)[0]
    return max(0.0, float(pred))


def _fallback_avg_prediction(
    product_id: int,
    stock: int,
    df: pd.DataFrame,
    cutoff: date,
) -> dict[str, Any]:
    """Previsão por média simples (fallback quando não há dados para ML)."""
    product_sales = df[(df["product_id"] == product_id) & (df["sold_at"] >= cutoff)]
    if product_sales.empty or stock <= 0:
        return {
            "product_id": product_id,
            "predicted_until": date.today().isoformat(),
            "predicted_quantity": 0,
            "days_until_stockout": None,
        }
    daily = product_sales.groupby("sold_at")["quantity"].sum()
    avg_per_day = daily.mean()
    if avg_per_day <= 0:
        return {
            "product_id": product_id,
            "predicted_until": date.today().isoformat(),
            "predicted_quantity": 0,
            "days_until_stockout": None,
        }
    days_until_stockout = max(1, int(round(stock / float(avg_per_day))))
    predicted_until = date.today() + timedelta(days=days_until_stockout)
    return {
        "product_id": product_id,
        "predicted_until": predicted_until.isoformat(),
        "predicted_quantity": 0,
        "days_until_stockout": days_until_stockout,
    }


def compute_predictions(products: list[dict], sales_history: list[dict]) -> list[dict[str, Any]]:
    """
    Previsão de esgotamento: usa modelo de ML quando há dados suficientes,
    senão fallback para média simples. Mantém contrato da API.
    """
    today = date.today()
    product_stock = {p["id"]: p["stock_quantity"] for p in products}
    results = []

    if not sales_history:
        return [
            {
                "product_id": p["id"],
                "predicted_until": today.isoformat(),
                "predicted_quantity": 0,
                "days_until_stockout": None,
            }
            for p in products
        ]

    df = pd.DataFrame(sales_history)
    df["sold_at"] = pd.to_datetime(df["sold_at"]).dt.date
    cutoff = today - timedelta(days=LOOKBACK_DAYS)
    df_cut = df[df["sold_at"] >= cutoff]

    daily = _sales_to_daily_df(sales_history)
    product_ids_with_data = daily["product_id"].unique().tolist()

    # Tentar treinar modelo global
    model = None
    product_encoder = LabelEncoder()
    training = build_training_data(daily, product_ids_with_data)
    if training is not None:
        X_train, y_train = training
        product_encoder.fit(X_train["product_id"].astype(str).unique().tolist())
        model = train_model(X_train, y_train, product_encoder)

    for product_id, stock in product_stock.items():
        if stock <= 0:
            results.append({
                "product_id": product_id,
                "predicted_until": today.isoformat(),
                "predicted_quantity": 0,
                "days_until_stockout": None,
            })
            continue

        features = _features_for_product_at_date(daily, product_id, today)
        use_ml = model is not None and features is not None

        if use_ml:
            daily_consumption = predict_daily_consumption(
                features, product_id, model, product_encoder
            )
            if daily_consumption <= 0:
                use_ml = False
        if not use_ml:
            results.append(
                _fallback_avg_prediction(product_id, stock, df_cut, cutoff)
            )
            continue

        days_until_stockout = max(1, int(round(stock / daily_consumption)))
        predicted_until = today + timedelta(days=days_until_stockout)
        results.append({
            "product_id": product_id,
            "predicted_until": predicted_until.isoformat(),
            "predicted_quantity": 0,
            "days_until_stockout": days_until_stockout,
        })

    return results


@app.post("/predict", response_model=PredictResponse)
def predict(request: PredictRequest) -> PredictResponse:
    products = [p.model_dump() for p in request.products]
    sales_history = [s.model_dump() for s in request.sales_history]
    predictions = compute_predictions(products, sales_history)
    return PredictResponse(predictions=[PredictionOutput(**p) for p in predictions])


@app.get("/health")
def health() -> dict:
    return {"status": "ok"}
