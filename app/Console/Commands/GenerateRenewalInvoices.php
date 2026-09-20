<?php

namespace App\Console\Commands;

use App\Models\HostingAccount;
use Illuminate\Console\Command;

class GenerateRenewalInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate-renewals {--days= : Custom days lead time threshold} {--dry-run : Simulate generation without saving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically generate pending renewal invoices for active hosting accounts approaching their renewal date';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $customDays = $this->option('days') ? (int) $this->option('days') : null;
        $isDryRun = (bool) $this->option('dry-run');

        $this->info("Scanning active hosting accounts for upcoming renewals...");

        $accounts = HostingAccount::with(['user', 'server', 'invoices'])
            ->where('status', 'active')
            ->where('auto_invoice', true)
            ->whereNotNull('renews_at')
            ->get();

        $generatedCount = 0;
        $rows = [];

        foreach ($accounts as $account) {
            // Check eligibility
            $isDue = false;
            if ($customDays !== null) {
                $threshold = now()->addDays($customDays);
                $isDue = $account->renews_at->lessThanOrEqualTo($threshold);
            } else {
                $isDue = $account->isDueForRenewalInvoice();
            }

            if ($isDue) {
                $renewalAmount = (float) ($account->renewal_price ?? $account->initial_price ?? 0.00);

                if ($isDryRun) {
                    $rows[] = [
                        $account->domain,
                        $account->user?->name ?? 'Unknown',
                        $account->renews_at?->format('Y-m-d') ?? 'N/A',
                        'INR ' . number_format($renewalAmount, 2),
                        '[SIMULATED]',
                    ];
                } else {
                    $invoice = $account->generateRenewalInvoice($renewalAmount, 'pending');
                    $rows[] = [
                        $account->domain,
                        $account->user?->name ?? 'Unknown',
                        $account->renews_at?->format('Y-m-d') ?? 'N/A',
                        $invoice->currency . ' ' . number_format($invoice->amount, 2),
                        $invoice->invoice_number,
                    ];
                }
                $generatedCount++;
            }
        }

        if ($generatedCount > 0) {
            $this->table(
                ['Domain', 'Client User', 'Renewal Due', 'Amount', 'Invoice #'],
                $rows
            );
            $action = $isDryRun ? "Simulated" : "Generated";
            $this->info("Successfully {$action} {$generatedCount} renewal invoice(s).");
        } else {
            $this->info("No hosting accounts are currently due for renewal invoicing.");
        }

        return Command::SUCCESS;
    }
}
