<?php

namespace JagdishJP\Billdesk\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use JagdishJP\Billdesk\Billdesk;
use JagdishJP\Billdesk\Messages\TransactionEnquiry;
use JagdishJP\Billdesk\Models\Transaction;

class TransactionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billdesk:transaction-status {reference_id? : Comma saperated Order Reference Id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get status of payment.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $reference_ids = $this->argument('reference_id');

        if ($reference_ids) {
            $reference_ids = explode(',', $reference_ids);
            $reference_ids = Transaction::whereIn('reference_id', $reference_ids)->get('reference_id')->toArray();
        }
        else {
            $reference_ids = Transaction::whereNull('transaction_status')->orWhere('transaction_status', TransactionEnquiry::STATUS_PENDING_CODE)->get('reference_id')->toArray();
        }

        if ($reference_ids) {
            try {
                $bar = $this->output->createProgressBar(count($reference_ids));
                $bar->start();
                foreach ($reference_ids as $row) {
                    $status[] = Billdesk::getTransactionStatus($row['reference_id']);
                }

                $this->newLine();
                $this->newLine();

                $this->table(collect(Arr::first($status))->keys()->toArray(), $status);
                $this->newLine();

                $bar->finish();
            }
            catch (Exception $e) {
                $this->error($e->getMessage());
                logger('Transaction Status', [
                    'message' => $e->getMessage(),
                ]);
            }
        }
        else {
            $this->error('There is no Pending transactions.');
            logger('Transaction Status', [
                'message' => 'There is no Pending transactions.',
            ]);
        }
    }
}
