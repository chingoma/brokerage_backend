<?php

namespace Modules\Schemes\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Schemes\Entities\EquityScheme;
use Throwable;

class EquitySchemesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $list = EquityScheme::orderBy('name')->get();

            return response()->json($list);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'min:1', 'max:191'],
                'step_one' => ['nullable', 'numeric'],
                'broker_fee' => ['nullable', 'string', 'min:1', 'max:191'],
                'mode' => ['required', 'string', 'min:1', 'max:191'],
                'flat_rate' => ['nullable', 'numeric'],
                'step_two' => ['nullable', 'numeric'],
                'step_three' => ['nullable', 'numeric'],
                'dse_fee' => ['nullable', 'numeric'],
                'csdr_fee' => ['nullable', 'numeric'],
                'cmsa_fee' => ['nullable', 'numeric'],
                'fidelity_fee' => ['nullable', 'numeric'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 102,
                    'message' => $validator->messages()->first(),
                    'errors' => $validator->errors(),
                ], 400);
            }

            $scheme = new EquityScheme();
            $this->__fill($scheme, $request);
            $scheme->save();

            DB::commit();
            $list = EquityScheme::orderBy('name')->get();

            return response()->json($list);
        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $scheme = EquityScheme::find($request->id);

            return response()->json($scheme);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $scheme = EquityScheme::find($request->id);
            $this->__fill($scheme, $request);
            $scheme->save();

            DB::commit();
            $list = EquityScheme::orderBy('name')->get();

            return response()->json($list);
        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        try {
            DB::beginTransaction();
            $scheme = EquityScheme::find($request->id);
            $scheme->delete();

            DB::commit();
            $list = EquityScheme::orderBy('name')->get();

            return response()->json($list);
        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    private function __fill(EquityScheme $scheme, $request)
    {
        $scheme->name = $request->name;
        $scheme->broker_fee = $request->broker_fee;
        $scheme->mode = $request->mode;
        if ($request->mode == 'default') {
            $scheme->flat_rate = 0;
            $scheme->step_one = $request->step_one;
            $scheme->step_two = $request->step_two;
            $scheme->step_three = $request->step_three;
        } else {
            $scheme->flat_rate = $request->flat_rate;
            $scheme->step_one = 0;
            $scheme->step_two = 0;
            $scheme->step_three = 0;
        }
        $scheme->dse_fee = $request->dse_fee;
        $scheme->cmsa_fee = $request->cmsa_fee;
        $scheme->fidelity_fee = $request->fidelity_fee;
        $scheme->csdr_fee = $request->csdr_fee;
    }
}
