<?php

namespace App\Jobs\DSE;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\DSE\DTOs\InvestorAccountDetailsDTO;
use Modules\DSE\Helpers\DSEHelper;

class CreateDseAccountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private mixed $account;

    public function __construct(mixed $account)
    {
        $this->account = $account;
    }

    public function handle(): void
    {
        //        $dseAccount = InvestorAccountDetailsDTO::fromJson(json_encode($this->account));
        //        DSEHelper::createAccount($dseAccount);
    }
}
