<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class BanksController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            $data = new Bank();
            $data->name = $request->name;
            $data->bic = $request->bic;
            $data->save();

            return $this->banks();

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function update(Request $request): JsonResponse
    {
        try {

            $data = Bank::findOrFail($request->id);
            $data->name = $request->name;
            $data->bic = $request->bic;
            $data->save();

            return $this->banks();

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function delete(Request $request): JsonResponse
    {
        try {

            $data = Bank::find($request->id);
            $data->delete();

            return $this->banks();

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function banks(): JsonResponse
    {
        try {
            return response()->json(Bank::latest()->orderBy('name', 'asc')->get());
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }
}
