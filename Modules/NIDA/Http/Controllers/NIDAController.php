<?php

namespace Modules\NIDA\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\NIDA\Helpers\NIDAFunctions;

class NIDAController extends Controller
{
    public function getNida(Request $request): JsonResponse {
        try {
            $validator = Validator::make($request->all(), [
                "nidaNumber" => ['required']
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => false,
                    "message" => $validator->messages()->first(),
                    "errors"  => $validator->errors()
                ],400);
            }

        return NIDAFunctions::getDetails($request->nidaNumber);

        }catch (\Throwable $throwable){
            report($throwable);
            return response()->json([
                "status" => false,
                "message" => $throwable->getMessage(),
                "errors"  => []
            ],400);
        }
    }
}
