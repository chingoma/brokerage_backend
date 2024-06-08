<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    // TODO
    // fix notifications

    public function notifications(Request $request)
    {
        $response['orders'] = 0; //\DB::table("orders")->where("status", 'pending')->get()->count();
        $response['customers'] = 0; //\DB::table("users")->where("status", 'pending')->whereIn("type",['minor','individual','joint','corporate'])->count();
        $response['transactions'] = 0; //\DB::table("transactions")->where("status", 'pending')->groupBy("reference")->get()->count();
        $response['sheets'] = 0; //\DB::table("dealing_sheets")->where("status", 'pending')->get()->count();
        $response['counter'] = 0; // $response['orders']  + $response['sheets'] + $response['transactions'] + $response['customers'];

        return response()->json($response);
    }

}
