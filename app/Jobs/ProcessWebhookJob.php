<?php

namespace App\Jobs;

use App\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\WebhookLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\Parsers\ParserFactory;
use InvalidArgumentException;

class ProcessWebhookJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        private readonly string $payload,
        private readonly string $bank,
        private readonly int $webhookLogId,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $log = WebhookLog::find($this->webhookLogId);

        try {
            $parser = ParserFactory::create($this->bank);
            $transactions = $parser->parse($this->payload);

            $importedCount = 0;
            $duplicatedCount = 0;

            foreach ($transactions as $transaction) {
                $created = Transaction::firstOrCreate(
                    [
                        'bank_name' => $this->bank,
                        'reference' => $transaction['reference'],
                    ],
                    [
                        'amount' => $transaction['amount'],
                        'transaction_date' => $transaction['transaction_date'],
                        'note' => $transaction['note'],
                        'internal_reference' => $transaction['internal_reference'],
                        'raw_payload' => $transaction['raw_line'],
                    ]
                );

                if ($created->wasRecentlyCreated) {
                    $importedCount++;
                } else {
                    $duplicatedCount++;
                }
            }

            if ($log) {
                $log->update([
                    'status' => 'success',
                    'transactions_imported' => $importedCount,
                    'transactions_duplicated' => $duplicatedCount,
                ]);
            }
            Log::info('Webhook processed: ', [
                'bank' => $this->bank,
                'imported' => $importedCount,
                'duplicated' => $duplicatedCount,
            ]);
        } catch (\Exception $e) {
            if ($log) {
                $log->update([
                    'status' => 'error',
                    'error_message' => $e->getMessage(),
                ]);
            }
            Log::error('Error processing webhook: ', [
                'bank' => $this->bank,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
