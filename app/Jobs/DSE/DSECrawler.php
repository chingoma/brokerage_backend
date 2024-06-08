<?php

namespace App\Jobs\DSE;

use App\Helpers\Helper;
use App\Mail\CustomEmail;
use App\Mail\DSECrawler\DSECrawlerDone;
use App\Mail\DSECrawler\DSECrawlerFailed;
use App\Models\MarketDataStockPostgres;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class DSECrawler implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        //check for today's data
        $dateRaw = Helper::systemDateTime();
        $result = MarketDataStockPostgres::where("date",$dateRaw['today'])->get();
        if(!$result){
            $mailable = new CustomEmail('DSE Stock Market Data Pull Status (Success)', "DSE Stock Market Data Pull Status (Success)");
        }else{
            $mailable = new CustomEmail('DSE Stock Market Data Pull Status (Failed)', "DSE Stock Market Data Pull Status (Failed)");
        }
        Mail::to('ict@itrust.co.tz')->queue($mailable);
        Mail::to('canwork.job@gmail.com')->queue($mailable);

    }
}
