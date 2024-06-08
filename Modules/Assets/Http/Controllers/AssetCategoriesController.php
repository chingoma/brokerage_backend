<?php

namespace Modules\Assets\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Assets\Entities\AssetCategory;
use Modules\Assets\Entities\AssetSubCategory;
use Throwable;

class AssetCategoriesController extends Controller
{
    public function Settings(Request $request): JsonResponse
    {
        try {
            $response['accounts'] = Account::latest()->get();

            return response()->json($response);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function SubCategories(string $id): JsonResponse
    {
        try {
            $list = AssetSubCategory::where('asset_category_id', $id)->get();

            return response()->json($list);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            return $this->__list($request);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $data = new AssetCategory();
            $data->name = $request->name;
            $data->code = $request->code;
            $data->debit_account = $request->debit_account;
            $data->credit_account = $request->credit_account;

            $data->save();
            DB::commit();

            return $this->__list($request);
        } catch (Throwable $throwable) {
            DB::rollback();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Show the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            return response()->json(AssetCategory::findOrFail($id));
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            DB::beginTransaction();
            $data = AssetCategory::findOrFail($id);
            $data->name = $request->name;
            $data->code = $request->code;
            $data->debit_account = $request->debit_account;
            $data->credit_account = $request->credit_account;
            $data->save();
            DB::commit();

            return $this->__list($request);
        } catch (Throwable $throwable) {
            DB::rollback();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            AssetCategory::findOrFail($id)->delete();

            return $this->__list(\request());
        } catch (Throwable $throwable) {
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    private function __list(Request $request): JsonResponse
    {
        $per_page = ! empty($per_page) ? $per_page : env('PERPAGE');
        $list = AssetCategory::latest()->get();

        return response()->json($list);
    }
}
