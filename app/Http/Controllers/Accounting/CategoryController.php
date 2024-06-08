<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CategoryController extends Controller
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

            $category = new AccountCategory();
            $category->name = $request->name;
            $category->debit_account = $request->debit_account;
            $category->credit_account = $request->credit_account;
            $category->type = $request->type;
            $category->color = $request->color;
            $category->save();

            return response()->json(AccountCategory::get());

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AccountCategory $category): JsonResponse
    {
        try {
            $category->name = $request->name;
            $category->type = $request->type;
            $category->color = $request->color;
            $category->debit_account = $request->debit_account;
            $category->credit_account = $request->credit_account;
            $category->save();

            return $this->__list();
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccountCategory $category): JsonResponse
    {
        try {
            $category->delete();

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
        return response()->json(AccountCategory::latest()->get());
    }
}
