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

class AccountSyncJob implements ShouldQueue
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
            //            $profile = Profile::where("user_id",$this->account)->firstOrFail();
            //            $user = User::findOrFail($this->account);
            //            $dse  = new \stdClass();
            //            $dse->birthDistrict = $profile->district;
            //            $dse->birthWard = $profile->ward;
            //            $dse->country = "TZ";
            //            $dse->dob = $profile->dob;
            //            $dse->email = $user->email;
            //            $dse->firstName = $profile->firstname;
            //            $dse->gender = $profile->gender;
            //            $dse->lastName = $profile->lastname;
            //            $dse->middleName = $profile->middlename;
            //            $dse->nationality = $profile->nationality;
            //            $dse->nidaNumber = str_ireplace("-","",$profile->identity);
            //            $dse->phoneNumber = $user->mobile;
            //            $dse->photo ="";
            //            $dse->residentRegion = $profile->region;
            //            $dse->physicalAddress = $profile->address;
            //            $dse->placeOfBirth =$profile->place_birth;
            //            $dse->region = $profile->region;
            //            $dse->requestId =  $this->account;
            //            $dse->residentDistrict = $profile->district;;
            //            $dse->residentHouseNo ="HOUSE102";
            //            $dse->residentPostCode ="1200";
            //            $dse->residentVillage ="Masaki";
            //            $dseAccount = InvestorAccountDetailsDTO::fromJson(json_encode($dse));
            //            DSEHelper::createAccount($dseAccount);
        } catch (\Throwable $throwable) {
            \Log::error($throwable->getMessage());
            report($throwable);
        }
    }
}
