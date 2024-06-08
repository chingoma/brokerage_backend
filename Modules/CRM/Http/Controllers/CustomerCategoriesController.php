<?php

namespace Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\CRM\Entities\CustomerCategory;
use Throwable;

class CustomerCategoriesController extends Controller
{
    public function add_category(Request $request): JsonResponse
    {
        try {

            $data = new CustomerCategory();
            $data->name = $request->name;
            $data->default = $request->default;
            $data->manager_id = $request->manager;
            $data->description = $request->description;
            $data->equity_scheme = trim($request->equity_scheme);
            $data->bond_scheme = trim($request->bond_scheme);
            $data->save();

            return $this->categories_data();

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function update_category(Request $request): JsonResponse
    {
        try {

            $data = CustomerCategory::find($request->id);
            $data->name = $request->name;
            $data->default = $request->default;
            $data->manager_id = $request->manager;
            $data->description = $request->description;
            $data->equity_scheme = trim($request->equity_scheme);
            $data->bond_scheme = trim($request->bond_scheme);
            $data->save();

            return $this->categories_data();

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function delete_category(Request $request): JsonResponse
    {
        try {

            $data = CustomerCategory::find($request->id);
            $data->delete();

            return $this->categories_data();

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function categories_data(): JsonResponse
    {
        try {
            $data['categories'] = \DB::table('customer_categories')
                ->whereNull('customer_categories.deleted_at')
                ->select(['customer_categories.*'])
                ->selectRaw('users.name as manager')
                ->selectRaw('users.id as manager_id')
                ->selectRaw('equity_schemes.name as equity_scheme_name')
                ->selectRaw('bond_schemes.name as bond_scheme_name')
                ->leftJoin('users', 'customer_categories.manager_id', '=', 'users.id')
                ->leftJoin('equity_schemes', 'customer_categories.equity_scheme', '=', 'equity_schemes.id')
                ->leftJoin('bond_schemes', 'customer_categories.bond_scheme', '=', 'bond_schemes.id')
                ->get();
            $data['bonds'] = \DB::table('bond_schemes')
                ->whereNull('deleted_at')
                ->select(['name', 'id'])
                ->get();
            $data['equities'] = \DB::table('equity_schemes')
                ->whereNull('deleted_at')
                ->select(['name', 'id'])
                ->get();
            $data['managers'] = \DB::table('users')
                ->whereNull('deleted_at')
                ->select(['name', 'id'])
                ->whereNull('type')
                ->where('is_admin', true)
                ->get();

            return response()->json($data);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }
}
