<?php

namespace App\Jobs\DSE;

use App\Models\Profile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\DSE\DTOs\DSEPayloadDTO;
use Modules\DSE\Helpers\DSEHelper;

class VerifyDSEAccountsLinkage implements ShouldQueue
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
        try {
            //            $users = \DB::table("users")
            //                ->whereNotNull("dse_account")
            //                ->where("dse_account_linkage","not linked")
            //                ->whereIn("type",['minor','individual','joint','corporate'])
            //                ->get();
            //            if(!empty($users)){
            //                foreach ($users as $user){
            //                    $profile = Profile::where("user_id",$user->id)->firstOrFail();
            //                    if(strlen($profile->identity) >= 20) {
            //                        $dse = new \stdClass();
            //                        $dse->nidaNumber = str_ireplace("-","",$profile->identity);
            //                        $dseAccount = DSEPayloadDTO::fromJson(json_encode($dse));
            //                        DSEHelper::verifyLinkage($dseAccount);
            //                    }
            //                }
            //            }
        } catch (\Throwable $throwable) {
            \Log::error($throwable->getMessage());
            report($throwable);
        }
    }
}
