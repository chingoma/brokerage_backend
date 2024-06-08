<?php

namespace App\Helpers\Clients;

use App\Models\Corporate;
use App\Models\JointProfile;
use App\Models\NextOfKin;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\CRM\Entities\CustomerCustodian;
use Modules\Custodians\Entities\Custodian;
use Nnjeim\World\Models\Country;

class ProfileCopy
{
    public function __construct(?string $id)
    {

        $this->id = $id;
        $user = User::find($id);
        if (empty($user)) {
            $user = new User();
        }
        if (! empty($user->id)) {
            $profile = \App\Models\Profile::firstOrCreate(['user_id' => $id]);
        } else {
            $profile = new \App\Models\Profile();
        }

        $category = DB::table('customer_categories')->find($user->category_id);

        if (empty($category)) {
            $category = DB::table('customer_categories')->where('default', 'yes')->first();
        }

        $minor = DB::table('users')->where('parent_id', $this->id)->first();
        if (! empty($minor)) {
            $this->has_minor = 'yes';
        } else {
            $this->has_minor = 'no';
        }
        //Account

        $this->dse_account_verified = $user->dse_account_verified;
        $this->dse_account_linkage = $user->dse_account_linkage;
        $this->risk_status = $user->risk_status;
        $this->created_at = $user->created_at;
        $this->created_by = $user->created_by;
        $this->reviewed_by = $user->reviewed_by;
        $this->approved_by = $user->approved_by;
        $this->updated_by = $user->updated_by;
        $this->once_auth = $user->once_auth ?? '';
        $this->category_id = $user->category_id ?? '';
        $this->parent_id = $user->parent_id;
        $this->parent_relationship = $user->parent_relationship;
        $this->a_name = strtoupper($user->name);
        $this->a_email = strtolower($user->email);
        $this->a_mobile = $user->mobile;
        $this->flex_acc_no = $user->flex_acc_no;
        $this->type = $user->type;
        $this->status = $user->status;
        $this->timezone = $user->timezone;
        $this->self_registration = $user->self_registration;
        $this->has_custodian = $user->has_custodian;
        $this->custodian_approved = $user->custodian_approved;
        $this->dse_account = $user->dse_account ?? '';
        $this->custodian_id = $user->custodian_id;
        $castId = CustomerCustodian::where('user_id', $user->id)->status('status', 'active')->first();
        $custo = Custodian::find($castId->id);
        $this->custodian = strtoupper($custo->name ?? '');
        if (! empty($user->custodian_id)) {
            $this->custodians = CustomerCustodian::where('user_id', $user->id)->get();
        }

        $this->bot_account = $user->bot_account ?? '';
        $this->bank_id = $user->bank_id;
        $this->bank_account_name = strtoupper($user->bank_account_name);
        $this->bank_account_number = strtoupper($user->bank_account_number);
        $this->bank_branch = strtoupper($user->bank_branch);
        $this->subscription = $user->subscription;
        $this->subscription_phone = $user->subscription_phone;
        $this->uid = $user->uid;
        $this->is_admin = $user->is_admin;

        $buy = DB::table('dealing_sheets')->where('type', 'buy')->where('status', 'approved')->where('client_id', $id)->sum('executed');
        $sold = DB::table('dealing_sheets')->where('type', 'sell')->where('status', 'approved')->where('client_id', $id)->sum('executed');
        $sell = DB::table('orders')->where('type', 'sell')->where('status', '!=', 'cancelled')->where('client_id', $id)->sum('executed');
        $this->volume = $buy - $sell - $sold;

        $bbuy = DB::table('bond_executions')->where('type', 'buy')->where('status', 'approved')->where('client_id', $id)->sum('executed');
        $bsold = DB::table('bond_executions')->where('type', 'sell')->where('status', 'approved')->where('client_id', $id)->sum('executed');
        $bsell = DB::table('bond_orders')->where('type', 'sell')->where('status', '!=', 'cancelled')->where('client_id', $id)->sum('face_value');
        $this->bond = $bbuy - $bsell - $bsold;
        $this->bonds = $bbuy - $bsell - $bsold;

        $this->wallet_balance = UsersHelper::wallet_balance($user->id);

        $country = Country::find($profile->country_id);

        $this->firstname = strtoupper($profile->firstname ?? '');
        $this->middlename = strtoupper($profile->middlename ?? '');
        $this->lastname = strtoupper($profile->lastname ?? '');
        $this->email = $profile->email;
        $this->mobile = $profile->mobile;
        $this->title = $profile->title;
        $this->gender = $profile->gender;

        $this->equities = \DB::table('dealing_sheets as executions')
            ->where('executions.client_id', $id)
            ->where('executions.status', 'approved')
            ->groupBy(['securities.name', 'executions.security_id'])
            ->selectRaw('securities.name as security')
            ->selectRaw("sum(IF(executions.type='buy',executions.executed,0)) as total_buy")
            ->selectRaw("sum(IF(executions.type='sell',executions.executed,0)) as total_sell")
            ->selectRaw("sum(IF(executions.type='buy',executions.executed,0)) - sum(IF(executions.type='sell',executions.executed,0)) as total")
            ->leftJoin('securities', 'executions.security_id', '=', 'securities.id')->get();

        $this->bondies = \DB::table('bond_executions as executions')
            ->where('executions.client_id', $id)
            ->where('executions.status', 'approved')
            ->groupBy(['bonds.security_name', 'executions.bond_id'])
            ->selectRaw('bonds.security_name as bond')
            ->selectRaw("sum(IF(executions.type='buy',executions.executed,0)) as total_buy")
            ->selectRaw("sum(IF(executions.type='sell',executions.executed,0)) as total_sell")
            ->selectRaw("sum(IF(executions.type='buy',executions.executed,0)) - sum(IF(executions.type='sell',executions.executed,0)) as total")
            ->leftJoin('bonds', 'executions.bond_id', '=', 'bonds.id')->get();

        switch (strtolower($profile->gender)) {
            case 'm':
                $this->gender = 'Male';
                break;
            case 'f':
                $this->gender = 'FEMALE';
            case 'female':
                $this->gender = 'FEMALE';
                break;
            case 'male':
                $this->gender = 'MALE';
                break;
            default:
        }

        $this->region = $profile->region;
        $this->district = $profile->district;
        $this->ward = $profile->ward;
        $this->place_birth = $profile->place_birth;
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
        $this->category = $category;
        $this->category_id = $category->id ?? '';
        $this->address = $profile->address;
        $this->nationality = $profile->nationality;
        $this->position = $profile->position;
        $this->tin = $profile->tin;
        $this->employment_status = $profile->employment_status;
        $this->employer_name = strtoupper($profile->employer_name);
        $this->current_occupation = $profile->current_occupation ?? '';
        $this->business_sector = $profile->business_sector;
        $this->other_employment = strtoupper($profile->other_employment);
        $this->other_title = strtoupper($profile->other_title);
        $this->other_business = $profile->other_business;

        if ($user->type != 'corporate') {
            $kin = NextOfKin::firstOrCreate(['parent' => $id]);
            $this->k_name = strtoupper($kin->name ?? '');
            $this->k_email = strtolower($kin->email ?? '');
            $this->k_mobile = $kin->mobile ?? '';
            $this->k_relationship = $kin->relationship ?? '';
        }

        if ($user->type == 'corporate') {
            $corporate = Corporate::where('user_id', $user->id)->first();
            $this->board_resolution = $corporate->board_resolution;
            $this->tin_certificate = $corporate->tin_certificate;
            $this->certificate_incorporation = $corporate->certificate_incorporation;
            $this->corporate_type = $corporate->corporate_type;
            $this->other_corporate_type = $corporate->other_corporate_type;
            $this->corporate_name = strtoupper($corporate->corporate_name);
            $this->a_name = strtoupper($corporate->corporate_name);
            $this->corporate_telephone = $corporate->corporate_telephone;
            $this->corporate_email = $corporate->corporate_email;
            $this->corporate_trade_name = strtoupper($corporate->corporate_trade_name);
            $this->business_sector = strtoupper($corporate->business_sector);
            $this->corporate_address = strtoupper($corporate->corporate_address);
            $this->corporate_building = strtoupper($corporate->corporate_building);
            $this->corporate_reg_number = strtoupper($corporate->corporate_reg_number);
            $this->corporate_tin = $corporate->corporate_tin;
        }

        if ($user->type == 'joint') {
            if (! empty($user->id)) {
                $profile = JointProfile::firstOrCreate(['user_id' => $user->id]);
                $country = Country::find($profile->country_id);
                $this->j_region = $profile->region;
                $this->j_district = $profile->district;
                $this->j_ward = $profile->ward;
                $this->j_place_birth = $profile->place_birth;
                $this->j_name = strtoupper($profile->name);
                $this->j_email = strtolower($profile->email);
                $this->j_mobile = strtoupper($profile->mobile);
                $this->j_firstname = strtoupper($profile->firstname ?? '');
                $this->j_middlename = strtoupper($profile->middlename ?? '');
                $this->j_lastname = strtoupper($profile->lastname ?? '');
                $this->j_title = $profile->title;
                $this->j_gender = $profile->gender;
                switch (strtolower($profile->gender)) {
                    case 'm':
                        $this->j_gender = 'Male';
                        break;
                    case 'f':
                        $this->j_gender = 'FEMALE';
                    case 'female':
                        $this->j_gender = 'FEMALE';
                        break;
                    case 'male':
                        $this->j_gender = 'MALE';
                        break;
                    default:
                }
                $this->j_dob = $profile->dob;
                $this->j_tin_file = $profile->tin_file;
                $this->j_id_type = $profile->id_type;
                $this->j_identity = $profile->identity;
                $this->j_identity_file = $profile->identity_file;
                $this->j_passport_file = $profile->passport_file;
                $this->j_signature_file = $profile->signature_file;
                $this->j_country_id = $profile->country_id;
                $this->j_country = $country->name ?? '';
                $this->j_country_iso = $country->iso2 ?? '';
                $this->j_category = strtoupper($category->name ?? '');
                $this->j_category_id = $category->id ?? '';
                $this->j_address = strtoupper($profile->address);
                $this->j_nationality = $profile->nationality;
                $this->j_position = $profile->position;
                $this->j_tin = $profile->tin;
                $this->j_employment_status = $profile->employment_status;
                $this->j_employer_name = strtoupper($profile->employer_name);
                $this->j_current_occupation = $profile->current_occupation ?? '';
                $this->j_business_sector = $profile->business_sector;
                $this->j_other_employment = $profile->other_employment;
                $this->j_other_title = strtoupper($profile->other_title);
                $this->j_other_business = strtoupper($profile->other_business);
            }
        }

        $this->name = match ($user->type) {
            'corporate' => $user->name,
            'joint' => strtoupper($this->firstname.' '.$this->middlename.' '.$this->lastname.' & '.$this->j_firstname.' '.$this->j_middlename.' '.$this->j_lastname),
            default => strtoupper($this->firstname.' '.$this->middlename.' '.$this->lastname),
        };
    }
}
