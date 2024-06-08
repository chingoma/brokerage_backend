<?php

namespace App\Jobs\Seeders;

use App\Models\Accounting\AccountCategory;
use App\Models\Accounting\Transaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Accounting\Entities\SimpleTransaction;
use Modules\Receipts\DTOs\CreateReceiptTransactionDTO;
use Modules\Receipts\Entities\Receipt;
use Modules\Receipts\Pipes\CreateReceiptPipe;
use Modules\Receipts\Pipes\CreateReceiptTransactionPipe;
use Modules\Receipts\Pipes\CreateSimpleTransactionReceiptPipe;
use Modules\Wallet\Entities\Wallet;
use stdClass;
use Throwable;

class ReceiptsSeederJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private mixed $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(): void
    {

        try {
            $data = new stdClass();
            $category = AccountCategory::where('type', 'receipt')->first()->id;
            $data->amount = random_int(500000000, 1000000000);
            $data->payee = $this->user->id;
            $data->category = $category;
            $data->description = 'Opening balance';
            $requestData = CreateReceiptTransactionDTO::fromJson(json_encode($data));
            $pipes = [
                CreateReceiptTransactionPipe::class,
                CreateReceiptPipe::class,
                CreateSimpleTransactionReceiptPipe::class,
            ];

            app(Pipeline::class)
                ->send($requestData)
                ->through($pipes)
                ->then(function ($simpleTransaction) {
                    return $simpleTransaction;
                });

            Transaction::where('client_id', $this->user->id)->update(['status' => 'approved']);

            $transaction = SimpleTransaction::where('client_id', $this->user->id)->first();
            $transaction->status = 'approved';
            $transaction->save();

            $receipt = Receipt::where('client_id', $this->user->id)->first();
            $receipt->status = 'approved';
            $receipt->save();

            $wallet = Wallet::firstOrCreate(['user_id' => $this->user->id]);
            $wallet->actual_balance = $transaction->amount;
            $wallet->available_balance = $transaction->amount;
            $wallet->save();

        } catch (Throwable $throwable) {
            report($throwable);
        }

    }
}
