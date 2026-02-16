"""
API de previsão de esgotamento de estoque.
Recebe produtos e histórico de vendas; retorna previsão de data de esgotamento por produto.
"""
from datetime import date, timedelta
from typing import Any

import pandas as pd
from fastapi import FastAPI
from pydantic import BaseModel

app = FastAPI(title="Stock Prediction API", version="1.0")

LOOKBACK_DAYS = 90


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


def compute_predictions(products: list[dict], sales_history: list[dict]) -> list[dict[str, Any]]:
    """Calcula, por produto, a data prevista de esgotamento (stock_quantity chega a 0)."""
    if not sales_history:
        return [
            {
                "product_id": p["id"],
                "predicted_until": date.today().isoformat(),
                "predicted_quantity": 0,
                "days_until_stockout": None,
            }
            for p in products
        ]

    df = pd.DataFrame(sales_history)
    df["sold_at"] = pd.to_datetime(df["sold_at"]).dt.date
    cutoff = date.today() - timedelta(days=LOOKBACK_DAYS)
    df = df[df["sold_at"] >= cutoff]

    product_stock = {p["id"]: p["stock_quantity"] for p in products}
    results = []

    for product_id, stock in product_stock.items():
        product_sales = df[df["product_id"] == product_id]
        if product_sales.empty or stock <= 0:
            results.append({
                "product_id": product_id,
                "predicted_until": date.today().isoformat(),
                "predicted_quantity": 0,
                "days_until_stockout": None,
            })
            continue

        daily = product_sales.groupby("sold_at")["quantity"].sum()
        avg_per_day = daily.mean()
        if avg_per_day <= 0:
            results.append({
                "product_id": product_id,
                "predicted_until": date.today().isoformat(),
                "predicted_quantity": 0,
                "days_until_stockout": None,
            })
            continue

        days_until_stockout = max(1, int(round(stock / float(avg_per_day))))
        predicted_until = date.today() + timedelta(days=days_until_stockout)
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
