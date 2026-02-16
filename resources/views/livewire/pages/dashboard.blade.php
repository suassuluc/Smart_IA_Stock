<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-6">
            {{ __('Dashboard') }}
        </h2>

        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

        @if ($alertProducts->isNotEmpty())
            @php
                $alertCount = $alertProducts->count();
                $limit = 10;
            @endphp
            <div class="mb-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-amber-500 dark:border-amber-500" x-data="{ expanded: false }">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">
                        {{ __('Alertas de estoque') }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        {{ __('Produtos que podem esgotar em até :days dias ou já estão abaixo do estoque mínimo.', ['days' => $alertDays ?? 14]) }}
                    </p>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($alertProducts as $index => $product)
                            <li x-show="expanded || {{ $index }} < {{ $limit }}"
                                class="py-3 first:pt-0 last:pb-0 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-gray-800 dark:text-gray-200">
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $product->name }}:</span>
                                @if ($product->isLowStock())
                                    <span class="text-red-600 dark:text-red-400">— {{ __('Estoque baixo') }}</span>
                                @else
                                    @php $days = $product->daysUntilStockOut(); @endphp
                                    @if ($days !== null)
                                        <span class="text-gray-600 dark:text-gray-300">{{ __('Esgota em ~:count dias', ['count' => $days]) }}</span>
                                        <span class="text-gray-400 dark:text-gray-500">({{ $product->latestPrediction?->predicted_until?->format('d/m/Y') }})</span>
                                    @endif
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    @if ($alertCount > $limit)
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button type="button"
                                    x-show="!expanded"
                                    @click="expanded = true"
                                    class="px-3 py-1.5 text-sm font-medium rounded-md bg-white dark:bg-gray-200 text-gray-800 dark:text-gray-900 border border-gray-300 dark:border-gray-500 hover:bg-gray-50 dark:hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                {{ __('Mostrar mais (:count itens)', ['count' => $alertCount - $limit]) }}
                            </button>
                            <button type="button"
                                    x-show="expanded"
                                    @click="expanded = false"
                                    class="px-3 py-1.5 text-sm font-medium rounded-md bg-white dark:bg-gray-200 text-gray-800 dark:text-gray-900 border border-gray-300 dark:border-gray-500 hover:bg-gray-50 dark:hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                {{ __('Mostrar menos') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Gráfico de tendência de vendas -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        {{ __('Tendência de vendas') }}
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                        {{ __('Total de unidades vendidas por semana (últimas :weeks semanas).', ['weeks' => $trendWeeks]) }}
                    </p>
                    <div class="h-80" wire:ignore
                         x-data="chartTrend(@js($trendLabels), @js($trendData))"
                         x-init="init()">
                        <canvas id="dashboard-chart-trend"></canvas>
                    </div>
                    @if (count($trendLabels) === 0)
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                            {{ __('Sem dados de vendas no período.') }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- Sugestão de reposição -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        {{ __('Sugestão de reposição') }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        {{ __('Produtos que merecem atenção com quantidade sugerida para repor (estoque mínimo ou cobertura de :days dias).', ['days' => $restockCoverDays ?? 30]) }}
                    </p>
                    @if (count($restockSuggestions) > 0)
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($restockSuggestions as $item)
                                <li class="py-3 first:pt-0 last:pb-0">
                                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $item['product']->name }}</span>
                                        <span class="text-sm font-semibold text-amber-600 dark:text-amber-400">
                                            {{ __('Repor :qty un.', ['qty' => $item['suggested_qty']]) }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $item['reason'] }}</p>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Nenhuma sugestão no momento. Estoque dentro do esperado.') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('chartTrend', (labels, data) => ({
        chart: null,
        init() {
            if (typeof Chart === 'undefined') return;
            const el = this.$el.querySelector('#dashboard-chart-trend');
            if (!el) return;
            this.chart = new Chart(el.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '{{ __("Unidades vendidas") }}',
                        data: data,
                        borderColor: 'rgb(99, 102, 241)',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    }));
});
</script>
