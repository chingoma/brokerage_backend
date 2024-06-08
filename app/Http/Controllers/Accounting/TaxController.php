<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Tax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class TaxController extends Controller
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

            $tax = new Tax();
            $tax->name = $request->name;
            $tax->rate = $request->rate;
            $tax->status = $request->status;
            $tax->save();

            return response()->json(Tax::get());

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tax $tax): JsonResponse
    {
        try {
            $tax->name = $request->name;
            $tax->rate = $request->rate;
            $tax->status = $request->status;
            $tax->save();

            return $this->__list();
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tax $tax): JsonResponse
    {
        try {
            $tax->delete();

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
        return response()->json(Tax::get());
    }
}
