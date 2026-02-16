<?php

namespace App\Console\Commands;

use App\Services\StockPredictionService;
use Illuminate\Console\Command;
use RuntimeException;

class RefreshPredictionsCommand extends Command
{
    protected $signature = 'predictions:refresh';

    protected $description = 'Atualiza as previsões de esgotamento de estoque via serviço Python';

    public function handle(StockPredictionService $service): int
    {
        try {
            $service->refreshPredictions();
            $this->info('Previsões atualizadas com sucesso.');

            return self::SUCCESS;
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
