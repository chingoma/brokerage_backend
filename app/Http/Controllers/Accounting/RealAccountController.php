<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\RealAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class RealAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return $this->__list();
    }

    /**
     * Display a listing of the resource.
     */
    public function show(Request $request, $id): JsonResponse
    {
        return response()->json(RealAccount::find($id));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {

            DB::beginTransaction();
            $data = new RealAccount();
            $data->account_name = $request->account_name;
            $data->account_number = $request->account_number;
            $data->bank_name = $request->bank_name;
            $data->save();

            $data->save();

            DB::commit();

            return $this->__list();

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            DB::beginTransaction();
            $data = RealAccount::find($id);
            $data->account_name = $request->account_name;
            $data->account_number = $request->account_number;
            $data->bank_name = $request->bank_name;
            $data->save();
            DB::commit();

            return $this->__list();
        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $data = RealAccount::find($id);
            $data->delete();

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
        return response()->json(RealAccount::latest()->paginate(env('PERPAGE')));
    }
}
