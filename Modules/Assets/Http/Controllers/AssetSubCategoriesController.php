<?php

namespace Modules\Assets\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Assets\Entities\AssetCategory;
use Modules\Assets\Entities\AssetSubCategory;
use Throwable;

class AssetSubCategoriesController extends Controller
{
    public function Settings(Request $request): JsonResponse
    {
        try {
            $response['categories'] = AssetCategory::latest()->get();

            return response()->json($response);
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
            $data = new AssetSubCategory();
            $data->name = $request->name;
            $data->code = $request->code;
            $data->asset_category_id = $request->category;

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
            return response()->json(AssetSubCategory::findOrFail($id));
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
            $data = AssetSubCategory::findOrFail($id);
            $data->name = $request->name;
            $data->code = $request->code;
            $data->asset_category_id = $request->category;
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
            AssetSubCategory::findOrFail($id)->delete();

            return $this->__list(\request());
        } catch (Throwable $throwable) {
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    private function __list(Request $request): JsonResponse
    {
        $per_page = ! empty($per_page) ? $per_page : env('PERPAGE');
        $list = AssetSubCategory::latest()->get();

        return response()->json($list);
    }
}
