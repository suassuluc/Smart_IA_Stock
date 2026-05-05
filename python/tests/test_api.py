"""Testes de contrato da API FastAPI (health, /predict, fallback e caminho com histórico)."""

from datetime import date, timedelta

from fastapi.testclient import TestClient

from main import app

client = TestClient(app)


def test_health() -> None:
    response = client.get("/health")
    assert response.status_code == 200
    assert response.json() == {"status": "ok"}


def test_predict_empty_sales_history() -> None:
    body = {
        "products": [{"id": 1, "stock_quantity": 5}],
        "sales_history": [],
    }
    r = client.post("/predict", json=body)
    assert r.status_code == 200
    data = r.json()
    assert "predictions" in data
    assert len(data["predictions"]) == 1
    p = data["predictions"][0]
    assert p["product_id"] == 1
    assert "predicted_until" in p
    assert "predicted_quantity" in p
    assert "days_until_stockout" in p


def test_predict_sparse_sales_uses_fallback_or_ml() -> None:
    """Poucos dados: deve responder 200 e respeitar o contrato (fallback ou ML)."""
    body = {
        "products": [{"id": 1, "stock_quantity": 100}],
        "sales_history": [
            {"product_id": 1, "sold_at": "2025-06-01", "quantity": 3},
            {"product_id": 1, "sold_at": "2025-06-02", "quantity": 1},
        ],
    }
    r = client.post("/predict", json=body)
    assert r.status_code == 200
    preds = r.json()["predictions"]
    assert len(preds) == 1
    assert preds[0]["product_id"] == 1


def test_predict_rich_history_ml_path_no_error() -> None:
    """Histórico longo o suficiente para treinar sem erro."""
    today = date.today()
    sales = []
    for i in range(100):
        d = today - timedelta(days=i)
        sales.append(
            {
                "product_id": 1,
                "sold_at": d.isoformat(),
                "quantity": 1 + (i % 5),
            }
        )
    body = {
        "products": [
            {"id": 1, "stock_quantity": 50},
            {"id": 2, "stock_quantity": 0},
        ],
        "sales_history": sales,
    }
    r = client.post("/predict", json=body)
    assert r.status_code == 200
    preds = r.json()["predictions"]
    assert len(preds) == 2
    ids = {p["product_id"] for p in preds}
    assert ids == {1, 2}
    for p in preds:
        assert "predicted_until" in p
        assert "days_until_stockout" in p
