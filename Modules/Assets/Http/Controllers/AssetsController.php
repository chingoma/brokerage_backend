<?php

namespace Modules\Assets\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Accounting\Account;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Assets\Entities\AssetCategory;
use Modules\Assets\Entities\Assets;
use Modules\Assets\Entities\AssetSubCategory;
use Throwable;

class AssetsController extends Controller
{
    public function Settings(Request $request): JsonResponse
    {
        try {
            $response['categories'] = AssetCategory::latest()->get();
            $response['sub_categories'] = AssetSubCategory::latest()->get();
            $response['accounts'] = Account::latest()->get();
            $response['departments'] = Department::latest()->get();

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
            $category = AssetCategory::findOrFail($request->category);
            $subCategory = AssetSubCategory::findOrFail($request->sub_category);
            $department = Department::findOrFail($request->department);
            $data = new Assets();
            $data->name = $request->name;
            $data->model = $request->model ?? '';
            $data->serial = $request->serial ?? '';
            $data->description = $request->description ?? '';
            $data->price = floatval($request->price);
            $data->date_of_purchase = $request->date_of_purchase;
            $data->date_of_manufacture = $request->date_of_manufacture ?? '';
            $data->location = $request->location ?? '';
            $data->employees = $request->employees ?? '';
            $data->status = $request->status;
            $data->category_id = $request->category;
            $data->supplier_id = $request->supplier ?? '';
            $data->sub_category_id = $request->sub_category;
            $data->department_id = $request->department;
            $data->debit_account = ! empty($request->debit_account) ? $request->debit_account : $category->debit_account;
            $data->credit_account = ! empty($request->credit_account) ? $request->credit_account : $category->credit_account;

            $data->save();
            $uid = 'CACL-'.$category->code.'-'.$department->code.'-'.$subCategory->code;
            Helper::assetUID(model: $data, id: $uid);

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
            return response()->json(Assets::findOrFail($id));
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
            $category = AssetCategory::findOrFail($request->category);
            DB::beginTransaction();
            $data = Assets::findOrFail($id);
            $data->name = $request->name;
            $data->model = $request->model ?? '';
            $data->serial = $request->serial ?? '';
            $data->description = $request->description ?? '';
            $data->price = floatval($request->price);
            $data->date_of_purchase = $request->date_of_purchase;
            $data->date_of_manufacture = $request->date_of_manufacture ?? '';
            $data->location = $request->location ?? '';
            $data->employees = $request->employees ?? '';
            $data->status = $request->status;
            $data->category_id = $request->category;
            $data->supplier_id = $request->supplier ?? '';
            $data->sub_category_id = $request->sub_category;
            $data->department_id = $request->department;
            $data->debit_account = ! empty($request->debit_account) ? $request->debit_account : $category->debit_account;
            $data->credit_account = ! empty($request->credit_account) ? $request->credit_account : $category->credit_account;
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
            Assets::findOrFail($id)->delete();

            return $this->__list(\request());
        } catch (Throwable $throwable) {
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    private function __list(Request $request): JsonResponse
    {
        $per_page = ! empty($per_page) ? $per_page : env('PERPAGE');
        $list = Assets::latest()->paginate($per_page);

        return response()->json($list);
    }
}
