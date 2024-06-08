<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class WhatsappController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function verify(Request $request)
    {
        echo $request->hub_challenge;
    }
}
