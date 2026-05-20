<?php

namespace App\Console\Commands;

use App\Services\AlertService;
use Illuminate\Console\Command;

class ProcessAlerts extends Command
{
    protected $signature = 'alerts:process';
    protected $description = 'Process stock price alerts and send notifications';

    public function __construct(private AlertService $alertService) {
        parent::__construct();
    }

    public function handle(): int
    {
        $triggered = $this->alertService->processAlerts();
        $this->info("Processed alerts. {$triggered} triggered.");
        return self::SUCCESS;
    }
}
