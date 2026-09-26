<?php

namespace App\Console\Commands;

use App\Services\UptimeMonitorService;
use Illuminate\Console\Command;

class CheckHostingUptime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hosting:check-uptime {--notify-admin= : Optional admin email address to alert}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ping client managed hosting domains, verify HTTP 200 status, and send email alerts for downtime';

    /**
     * Execute the console command.
     */
    public function handle(UptimeMonitorService $monitor): int
    {
        $adminEmail = $this->option('notify-admin');

        $this->info("Scanning all active managed hosting domains for uptime...");

        $results = $monitor->checkAll($adminEmail);

        $this->info("Scan complete: {$results['total']} checked. {$results['up']} UP (Green), {$results['down']} DOWN (Red).");

        return Command::SUCCESS;
    }
}
