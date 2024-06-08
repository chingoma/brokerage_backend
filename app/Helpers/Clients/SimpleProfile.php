<?php

namespace App\Helpers\Clients;

use App\Models\Corporate;
use App\Models\JointProfile;
use App\Models\NextOfKin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Wallet\Entities\Wallet;
use Nnjeim\World\Models\Country;

class SimpleProfile
{
    public ?string $id;
    public string $name;
    /**
     * @var mixed|string
     */
    public mixed $wallet_balance;
    public string $email;
    public $mobile;
    public $flex_acc_no;
    public $status;
    public $uid;
    public $type;
    /**
     * @var mixed|string
     */
    public mixed $wallet_available;
    /**
     * @var mixed|string
     */
    public mixed $wallet_status;

    public function __construct(?string $id)
    {
        $this->id = $id;

        $user = DB::table('users')->find($id);
        $profile = DB::table('profiles')->where('user_id', $this->id)->first();
        $jProfile = DB::table('joint_profiles')->where('user_id', $this->id)->first();
        $corporate =  DB::table('corporates')->where('user_id', $user->id)->first();
        $this->email = strtolower($user->email);
        $this->mobile = $user->mobile;
        $this->flex_acc_no = $user->flex_acc_no;
        $this->type = $user->type;
        $this->status = $user->status;
        $this->uid = $user->uid;

        $this->name = match ($user->type) {
            'corporate' => $corporate->corporate_name,
            'joint' => strtoupper($profile->firstname.' '.$profile->middlename.' '.$profile->lastname.' & '.$jProfile->firstname.' '.$jProfile->middlename.' '.$jProfile->lastname),
            default => strtoupper($profile->firstname.' '.$profile->middlename.' '.$profile->lastname),
        };
    }
}
