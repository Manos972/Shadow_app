<?php

namespace App\Console\Commands;

use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Illuminate\Console\Command;

class ProcessRecurringTransactions extends Command
{
    protected $signature = 'recurring:process';
    protected $description = 'Process due recurring transactions';

    public function handle(): int
    {
        $due = RecurringTransaction::where('is_active', true)
            ->where('next_run_date', '<=', today())
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', today());
            })
            ->get();

        $processed = 0;
        foreach ($due as $recurring) {
            Transaction::create([
                'user_id' => $recurring->user_id,
                'account_id' => $recurring->account_id,
                'category_id' => $recurring->category_id,
                'type' => $recurring->type,
                'amount' => $recurring->amount,
                'description' => $recurring->description . ' (récurrent)',
                'date' => today(),
                'recurring_id' => (string) $recurring->id,
            ]);

            $recurring->advanceNextRunDate();
            $processed++;
        }

        $this->info("Processed {$processed} recurring transactions.");
        return self::SUCCESS;
    }
}
