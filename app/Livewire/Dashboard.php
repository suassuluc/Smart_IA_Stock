<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\SaleItem;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    /** Semanas para o gráfico de tendência de vendas */
    public const TREND_WEEKS = 12;

    /** Dias para calcular média de vendas (sugestão de reposição) */
    public const PROJECTION_DAYS = 90;

    /** Dias no futuro para considerar "alerta" de esgotamento */
    public const ALERT_DAYS = 14;

    /** Dias de cobertura para sugestão de reposição (quando vai esgotar) */
    public const RESTOCK_COVER_DAYS = 30;

    public function render()
    {
        $products = Product::with('latestPrediction')->orderBy('name')->get();

        $trendLabels = [];
        $trendData = [];

        $weeksAgo = now()->subWeeks(self::TREND_WEEKS)->startOfWeek();
        for ($i = 0; $i < self::TREND_WEEKS; $i++) {
            $weekStart = $weeksAgo->copy()->addWeeks($i);
            $weekEnd = $weekStart->copy()->endOfWeek();
            $trendLabels[] = $weekStart->format('d/m');
            $sold = SaleItem::query()
                ->whereHas('sale', fn ($q) => $q->whereBetween('sold_at', [$weekStart, $weekEnd]))
                ->sum('quantity');
            $trendData[] = (int) $sold;
        }

        $alertProducts = $products->filter(function ($product) {
            if ($product->isLowStock()) {
                return true;
            }
            $days = $product->daysUntilStockOut();
            return $days !== null && $days >= 0 && $days <= self::ALERT_DAYS;
        })
            ->sortBy([
                fn ($p) => ! $p->isLowStock(),  // estoque abaixo do mínimo primeiro
                fn ($p) => $p->daysUntilStockOut() ?? 9999,  // esgota mais cedo primeiro
                fn ($p) => $p->stock_quantity,  // menor quantidade em estoque primeiro
            ])
            ->values();

        $since = now()->subDays(self::PROJECTION_DAYS);
        $restockSuggestions = [];
        foreach ($alertProducts as $product) {
            $sold = SaleItem::query()
                ->where('product_id', $product->id)
                ->whereHas('sale', fn ($q) => $q->where('sold_at', '>=', $since))
                ->sum('quantity');
            $avgPerDay = $sold / self::PROJECTION_DAYS;

            if ($product->isLowStock()) {
                $suggestedQty = max(0, $product->minimum_stock - $product->stock_quantity);
                $reason = __('Estoque abaixo do mínimo');
            } else {
                $daysLeft = $product->daysUntilStockOut();
                if ($daysLeft !== null && $daysLeft <= self::ALERT_DAYS && $avgPerDay > 0) {
                    $neededForCover = (int) ceil($avgPerDay * self::RESTOCK_COVER_DAYS);
                    $suggestedQty = max(0, $neededForCover - $product->stock_quantity);
                    $reason = __('Esgota em ~:count dias — repor para cobrir :days dias', ['count' => $daysLeft, 'days' => self::RESTOCK_COVER_DAYS]);
                } else {
                    $suggestedQty = 0;
                    $reason = '';
                }
            }

            if ($suggestedQty > 0) {
                $restockSuggestions[] = [
                    'product' => $product,
                    'suggested_qty' => $suggestedQty,
                    'reason' => $reason,
                ];
            }
        }

        return view('livewire.pages.dashboard', [
            'trendLabels' => $trendLabels,
            'trendData' => $trendData,
            'trendWeeks' => self::TREND_WEEKS,
            'alertProducts' => $alertProducts,
            'alertDays' => self::ALERT_DAYS,
            'restockSuggestions' => $restockSuggestions,
            'restockCoverDays' => self::RESTOCK_COVER_DAYS,
        ]);
    }
}
