<?php

namespace App\Helpers;

use App\Models\Profile;
use App\Models\User;
use Nnjeim\World\Models\Country;

class AdminProfile
{
    public function __construct()
    {

        $this->id = auth()->id();
        $id = auth()->id();
        $user = User::find($id);
        $profile = Profile::where('user_id', $id)->first();
        $this->risk_status = $user->risk_status;
        $this->created_at = $user->created_at;
        $this->created_by = $user->created_by;
        $this->reviewed_by = $user->reviewed_by;
        $this->approved_by = $user->approved_by;
        $this->updated_by = $user->updated_by;
        $this->once_auth = $user->once_auth ?? '';
        $this->type = $user->type;
        $this->status = $user->status;
        $this->timezone = $user->timezone;
        $this->bot_account = $user->bot_account ?? '';
        $this->bank_id = $user->bank_id;
        $this->bank_account_name = strtoupper($user->bank_account_name);
        $this->bank_account_number = strtoupper($user->bank_account_number);
        $this->bank_branch = strtoupper($user->bank_branch);
        $this->subscription = $user->subscription;
        $this->subscription_phone = $user->subscription_phone;
        $this->uid = $user->uid;
        $country = Country::find($profile->country_id);
        $this->firstname = strtoupper($profile->firstname ?? '');
        $this->middlename = strtoupper($profile->middlename ?? '');
        $this->lastname = strtoupper($profile->lastname ?? '');
        $this->email = $profile->email;
        $this->mobile = $profile->mobile;
        $this->title = $profile->title;
        $this->gender = $profile->gender;

        switch (strtolower($profile->gender)) {
            case 'female':
                $this->gender = 'FEMALE';
                break;
            case 'male':
                $this->gender = 'MALE';
                break;
            default:
        }

        $this->dob = $profile->dob;
        $this->id_type = $profile->id_type;
        $this->identity = $profile->identity;
        $this->identity_file = $profile->identity_file;
        $this->passport_file = $profile->passport_file;
        $this->signature_file = $profile->signature_file;
        $this->tin_file = $profile->tin_file;
        $this->country_id = $profile->country_id;
        $this->country = $country->name ?? '';
        $this->country_iso = $country->iso2 ?? '';
        $this->address = $profile->address;
        $this->nationality = $profile->nationality;
    }
}
