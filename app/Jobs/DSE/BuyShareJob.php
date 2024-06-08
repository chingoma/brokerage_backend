<?php

namespace App\Jobs\DSE;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\DSE\DTOs\BuyShareDTO;
use Modules\DSE\Helpers\DSEHelper;

class BuyShareJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public BuyShareDTO $buyShareDTO;

    /**
     * Create a new job instance.
     */
    public function __construct(BuyShareDTO $buyShareDTO)
    {
        $this->buyShareDTO = $buyShareDTO;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            //           DSEHelper::buyShares($this->buyShareDTO);
        } catch (\Throwable $throwable) {
            \Log::error($throwable->getMessage());
            report($throwable);
        }
    }
}
