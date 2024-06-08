<?php

namespace Modules\Assets\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Assets\Entities\AssetIssue;
use Throwable;

class AssetIssuesController extends Controller
{
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
            $data = new AssetIssue();
            $data->description = $request->description ?? '';
            $data->comments = $request->comments ?? '';
            $data->expected_fix_date = $request->expected_fix_date;
            $data->resolved_date = $request->resolved_date ?? '';
            $data->status = $request->status;
            $data->asset_id = $request->asset;
            $data->raised_by = $request->raised_by;
            $data->approved_by = $request->approved_by ?? '';

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
            return response()->json(AssetIssue::findOrFail($id));
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
            $data = AssetIssue::findOrFail($id);
            $data->description = $request->description ?? '';
            $data->comments = $request->comments ?? '';
            $data->expected_fix_date = $request->expected_fix_date;
            $data->resolved_date = $request->resolved_date ?? '';
            $data->status = $request->status;
            $data->asset_id = $request->asset;
            $data->raised_by = $request->raised_by;
            $data->approved_by = $request->approved_by ?? '';
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
            AssetIssue::findOrFail($id)->delete();

            return $this->__list(\request());
        } catch (Throwable $throwable) {
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    private function __list(Request $request): JsonResponse
    {
        $per_page = ! empty($per_page) ? $per_page : env('PERPAGE');
        $list = AssetIssue::latest()->paginate($per_page);

        return response()->json($list);
    }
}
