<?php

namespace App\Http\Controllers\Accounting;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Accounting\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class InvoiceController extends Controller
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

            $invoice = new Invoice();
            $invoice->business_id = Helper::business()->id;
            $invoice->name = $request->name;
            $invoice->save();

            return response()->json(Invoice::get());

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        try {
            $invoice->name = $request->name;
            $invoice->save();

            return $this->__list();
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        try {
            $invoice->delete();

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
        return response()->json(Invoice::latest()->get());
    }
}
