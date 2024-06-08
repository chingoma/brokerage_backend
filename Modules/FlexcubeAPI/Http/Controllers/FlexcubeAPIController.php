<?php

namespace Modules\FlexcubeAPI\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Modules\FlexcubeAPI\DTOs\Mltoffsetdetail;
use Modules\FlexcubeAPI\DTOs\MultioffsetmaterFull;
use Modules\FlexcubeAPI\Helpers\FlexcubeFunctions;

class FlexcubeAPIController extends Controller
{

    private string $baseUrl = 'http://192.168.1.30:9001/ITRUSTAPIDE/v1/api';

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $results = FlexcubeFunctions::postDebit();
        return response()->json($results);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('flexcubeapi::show');
    }



}
