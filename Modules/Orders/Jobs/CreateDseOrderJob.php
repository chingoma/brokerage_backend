<?php

namespace Modules\Orders\Jobs;

use App\Helpers\Helper;
use App\Mail\CustomEmail;
use App\Models\Profile;
use App\Rules\ValidationHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Modules\DSE\DTOs\BuyShareDTO;
use Modules\DSE\Helpers\DSEHelper;

class CreateDseOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private mixed $account;

    public function __construct(mixed $account)
    {
        $this->account = $account;
    }

    public function handle(): void
    {
        try {
            $date = Helper::systemDateTime();
            $tasks = \DB::table("orders")
                ->where("dse_status", "new")
                ->whereDate("date", $date['today'])
                ->get();
            if (!empty($tasks)) {
                foreach ($tasks as $task) {
                    $client = Profile::where("user_id",$task->client_id)->first();
                    if(ValidationHelper:: nidaChecking($client->identity)){

                        $standardClass = new \stdClass();
                        $data = new  BuyShareDTO::fromJson(json_encode($standardClass));
                        DSEHelper::buyShares($data);
                    }else{
                        $mailable = (new CustomEmail("DSE Order Status"," Failed to submit order to DSE due to Invalid NIDA NUMBER"))
                            ->onQueue(getenv("REDIS_QUEUE_PREFIX")."-brokerlink-admin-api-notification-emails");
                        Mail::to("kelvin@lockminds.com")->queue($mailable);
                    }
                }
            }
        }catch (\Throwable $throwable){
            report($throwable);
            $mailable = (new CustomEmail("DSE Order Status"," Failed to submit order to DSE due to  \ln".$throwable->getMessage()))
                ->onQueue(getenv("REDIS_QUEUE_PREFIX")."-brokerlink-admin-api-notification-emails");
            Mail::to("kelvin@lockminds.com")->queue($mailable);
        }
    }
}
