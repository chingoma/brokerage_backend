<?php

namespace App\Jobs\DSE;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\DSE\DTOs\InvestorAccountDetailsDTO;
use Modules\DSE\Helpers\DSEHelper;

class AccountSyncAllJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
    }

    public function handle(): void
    {
        try {
            //            $users = \DB::table("users")
            //                ->where("dse_synced","no")
            //                ->whereIn("type",['minor','individual','joint','corporate'])
            //                ->get();
            //            if(!empty($users)){
            //                foreach ($users as $user){
            //                    $profile = Profile::where("user_id",$user->id)->firstOrFail();
            //                    if(strlen($profile->identity) >= 20){
            //                        $dse  = new \stdClass();
            //                        $dse->birthDistrict = $profile->district;
            //                        $dse->birthWard = $profile->ward;
            //                        $dse->country = "TZ";
            //                        $dse->dob = $profile->dob;
            //                        $dse->email = $user->email;
            //                        $dse->firstName = $profile->firstname;
            //                        $dse->gender = $profile->gender;
            //                        $dse->lastName = $profile->lastname;
            //                        $dse->middleName = $profile->middlename;
            //                        $dse->nationality = $profile->nationality;
            //                        $dse->nidaNumber = str_ireplace("-","",$profile->identity);
            //                        $dse->phoneNumber = $user->mobile;
            //                        $dse->photo ="";
            //                        $dse->residentRegion = $profile->region;
            //                        $dse->physicalAddress = $profile->address;
            //                        $dse->placeOfBirth =$profile->place_birth;
            //                        $dse->region = $profile->region;
            //                        $dse->requestId =  $user->id;
            //                        $dse->residentDistrict = $profile->district;;
            //                        $dse->residentHouseNo ="";
            //                        $dse->residentPostCode ="";
            //                        $dse->residentVillage ="";
            //                        $dseAccount = InvestorAccountDetailsDTO::fromJson(json_encode($dse));
            //                        DSEHelper::createAccount($dseAccount);
            //                    }
            //
            //                }
            //            }
        } catch (\Throwable $throwable) {
            \Log::error($throwable->getMessage());
            report($throwable);
        }
    }
}
