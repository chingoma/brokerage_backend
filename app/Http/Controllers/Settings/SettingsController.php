<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use function auth;

class SettingsController extends Controller
{
    public function settings_data(Request $request)
    {

        $accountSettings = [
            'general' => auth()->user()->profile,
        ];
        $response = [
            'accountSetting' => $accountSettings,
        ];

        return response()->json($response, 200);
    }
}
