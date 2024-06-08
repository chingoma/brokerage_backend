<?php

namespace App\Http\Controllers;

use App\Models\Sector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class SectorsController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            $data = new Sector();
            $data->name = $request->name;
            $data->save();

            return $this->sectors();

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function update(Request $request): JsonResponse
    {
        try {

            $data = Sector::findOrFail($request->id);
            $data->name = $request->name;
            $data->save();

            return $this->sectors();

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function delete(Request $request): JsonResponse
    {
        try {

            $data = Sector::find($request->id);
            $data->delete();

            return $this->sectors();

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function sectors(): JsonResponse
    {
        try {
            return response()->json(Sector::orderBy('name')->get());
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }
}
