<?php

namespace App\Console\Commands;

use App\Services\GenerateSystemAlertsService;
use Illuminate\Console\Command;

class GenerateSystemAlerts extends Command
{
    protected $signature = 'alerts:generate';

    protected $description = 'Gera notificações dos alertas do ERP';

    public function handle(
        GenerateSystemAlertsService $service
    ): int {
        $created = $service->execute();

        $this->info(
            "{$created} notificação(ões) gerada(s) com sucesso."
        );

        return self::SUCCESS;
    }
}