<?php

namespace App\Services;

use App\Models\Prediction;
use App\Models\Product;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class StockPredictionService
{
    /**
     * Meses de histórico de vendas para enviar ao predictor.
     */
    public const HISTORY_MONTHS = 12;

    /**
     * Busca dados no Laravel, chama o serviço Python e persiste as previsões.
     * Em caso de falha (timeout, erro HTTP), mantém as previsões já gravadas e lança/registra o erro.
     *
     * @throws \RuntimeException quando o predictor não está disponível ou retorna erro
     */
    public function refreshPredictions(): void
    {
        $url = rtrim(config('services.predictor.url'), '/').'/predict';
        $timeout = config('services.predictor.timeout', 30);

        $payload = $this->buildPayload();

        $response = Http::timeout($timeout)
            ->acceptJson()
            ->post($url, $payload);

        if (! $response->successful()) {
            Log::warning('Stock predictor request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new RuntimeException('Serviço de previsão indisponível ou retornou erro.');
        }

        $data = $response->json();
        $predictions = $data['predictions'] ?? [];

        foreach ($predictions as $row) {
            $productId = (int) $row['product_id'];
            $predictedUntil = $row['predicted_until'] ?? null;
            $predictedQuantity = (int) ($row['predicted_quantity'] ?? 0);

            if (! $predictedUntil) {
                continue;
            }

            Prediction::updateOrCreate(
                ['product_id' => $productId],
                [
                    'predicted_quantity' => $predictedQuantity,
                    'predicted_until' => $predictedUntil,
                ]
            );
        }
    }

    /**
     * Monta o payload para POST /predict: produtos (id, stock_quantity) e histórico de vendas.
     */
    public function buildPayload(): array
    {
        $products = Product::query()
            ->select('id', 'stock_quantity')
            ->orderBy('id')
            ->get();

        $since = now()->subMonths(self::HISTORY_MONTHS);

        $saleItems = SaleItem::query()
            ->select('sale_items.product_id', 'sale_items.quantity', 'sales.sold_at')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.sold_at', '>=', $since)
            ->get();

        $sales_history = $saleItems->map(fn ($item) => [
            'product_id' => $item->product_id,
            'sold_at' => Carbon::parse($item->sold_at)->format('Y-m-d'),
            'quantity' => (int) $item->quantity,
        ])->values()->all();

        return [
            'products' => $products->map(fn ($p) => [
                'id' => $p->id,
                'stock_quantity' => (int) $p->stock_quantity,
            ])->values()->all(),
            'sales_history' => $sales_history,
        ];
    }
}
