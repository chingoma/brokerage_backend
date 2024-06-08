<?php

namespace App\Helpers\Clients;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Wallet\Entities\Wallet;

class Portfolio
{
    public function __construct(?string $id)
    {

        $this->id = $id;
        $user = User::find($id);
        if (empty($user)) {
            $user = new User();
        }
        if (! empty($user->id)) {
            $profile = DB::table('profiles')->where('user_id', $id)->first();
        } else {
            $profile = new \App\Models\Profile();
        }

        $category = DB::table('customer_categories')->find($user->category_id);

        if (empty($category)) {
            $category = DB::table('customer_categories')->where('default', 'yes')->first();
        }
        $this->category = $category;
        $this->bond_scheme = DB::table('bond_schemes')->find($category->bond_scheme);
        $this->equity_scheme = DB::table('equity_schemes')->find($category->equity_scheme);
        $minor = DB::table('users')->where('parent_id', $this->id)->first();
        if (! empty($minor)) {
            $this->has_minor = 'yes';
        } else {
            $this->has_minor = 'no';
        }
        //Account

        $this->bot_cds_number = !empty($user->bot_cds_number) ?$user->bot_cds_number :"";
        $this->bot_security_number = !empty($user->bot_security_number) ?$user->bot_security_number :"";
        $this->onboard_status = $user->onboard_status;
        $this->source_of_income = $user->source_of_income;
        $this->income_frequency = $user->income_frequency;
        $this->risk_status = $user->risk_status;
        $this->a_email = strtolower($user->email);
        $this->a_mobile = $user->mobile;
        $this->flex_acc_no = $user->flex_acc_no;
        $this->type = $user->type;
        $this->status = $user->status;
        $this->self_registration = $user->self_registration;
        $this->has_custodian = $user->has_custodian;
        $this->custodian_approved = $user->custodian_approved;
        $this->dse_account = $user->dse_account ?? '';
        $this->custodian_id = $user->custodian_id;
        $castId = DB::table('customer_custodians')->where('user_id', $user->id)->where('status', 'active')->first();
        $custo = DB::table('custodians')->find($castId->id ?? '');
        $this->custodian = strtoupper($custo->name ?? 'no');
        $this->custodians = DB::table('customer_custodians')
            ->select(['custodians.*'])
            ->selectRaw('custodians.id as custodian_id')
            ->where('customer_custodians.user_id', $user->id)
            ->leftJoin('custodians', 'customer_custodians.custodian_id', '=', 'custodians.id')
            ->get();
        $this->bot_account = $user->bot_account ?? '';
        $this->bank_id = $user->bank_id;
        $this->bank_account_name = strtoupper($user->bank_account_name);
        $this->bank_account_number = strtoupper($user->bank_account_number);
        $this->uid = $user->uid;
        $this->is_admin = $user->is_admin;

        $equity_bought = DB::table('dealing_sheets')
            ->where('type', 'buy')
            ->where('status', 'approved')
            ->where('client_id', $this->id)
            ->sum('executed');

        $equity_sold = DB::table('dealing_sheets')
            ->where('type', 'sell')
            ->where('status', 'approved')
            ->where('client_id', $this->id)
            ->sum('executed');

        $equity_selling = DB::table('orders')
            ->whereNull('deleted_at')
            ->where('status', '!=', 'cancelled')
            ->where('type', 'sell')
            ->where('client_id', $this->id)
            ->sum('volume');

        $this->actual_volume = $equity_bought - $equity_sold;
        $this->volume = $equity_bought - $equity_selling;

        $bond_bought = DB::table('bond_executions')
            ->where('type', 'buy')
            ->where('status', 'approved')
            ->where('client_id', $this->id)
            ->sum('executed');

        $bond_sold = DB::table('bond_executions')
            ->where('type', 'sell')
            ->where('status', 'approved')
            ->where('client_id', $this->id)
            ->sum('executed');

        $bond_selling = DB::table('bond_orders')
            ->where('type', 'sell')
            ->where('status', '!=', 'cancelled')
            ->where('client_id', $this->id)
            ->sum('face_value');

        $this->bond = $bond_bought - $bond_selling;
        $this->bonds = $bond_bought - $bond_selling;
        $this->actual_bond = $bond_bought - $bond_sold;
        $wallet = Wallet::firstOrCreate(['user_id' => $this->id]);
        $this->wallet_balance = $wallet->actual_balance;
        $this->wallet_available = $wallet->available_balance;
        $this->wallet_status = UsersHelper::wallet_status($user->id);

        $this->email = $profile->email;
        $this->mobile = $this->a_mobile;
        $this->title = $profile->title;
        $this->gender = $profile->gender;

        $this->name = $user->name;
        $this->a_name = $this->name;

        $this->equities = \DB::table('dealing_sheets as executions')
            ->where('executions.client_id', $id)
            ->where('executions.status', 'approved')
            ->groupBy(['securities.name', 'executions.security_id'])
            ->selectRaw('securities.id as id')
            ->selectRaw('securities.name as security')
            ->selectRaw("sum(IF(executions.type='buy',executions.executed,0)) as total_buy")
            ->selectRaw("sum(IF(executions.type='sell',executions.executed,0)) as total_sell")
            ->selectRaw("sum(IF(executions.type='buy',executions.executed,0)) - sum(IF(executions.type='sell',executions.executed,0)) as total")
            ->leftJoin('securities', 'executions.security_id', '=', 'securities.id')->get();

        $this->bondies = \DB::table('bond_executions as executions')
            ->where('executions.client_id', $id)
            ->where('executions.status', 'approved')
            ->groupBy(['bonds.security_name', 'executions.bond_id'])
            ->selectRaw('executions.id as id')
            ->selectRaw('bonds.security_name as bond')
            ->selectRaw("sum(IF(executions.type='buy',executions.executed,0)) as total_buy")
            ->selectRaw("sum(IF(executions.type='sell',executions.executed,0)) as total_sell")
            ->selectRaw("sum(IF(executions.type='buy',executions.executed,0)) - sum(IF(executions.type='sell',executions.executed,0)) as total")
            ->leftJoin('bonds', 'executions.bond_id', '=', 'bonds.id')->get();

    }
}
