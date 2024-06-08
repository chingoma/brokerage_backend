<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return $this->__list();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {

            $data = new PaymentMethod();
            $data->name = $request->name;
            $data->description = $request->description;
            $data->save();

            return response()->json(PaymentMethod::get());

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        try {
            $paymentMethod = PaymentMethod::find($request->id);
            $paymentMethod->name = $request->name;
            $paymentMethod->description = $request->description;
            $paymentMethod->save();

            return $this->__list();
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentMethod $paymentMethod): JsonResponse
    {
        try {
            $paymentMethod->delete();

            return $this->__list();
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Display a listing of the resource.
     */
    private function __list(): JsonResponse
    {
        return response()->json(PaymentMethod::latest()->get());
    }
}
