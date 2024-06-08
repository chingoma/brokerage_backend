<?php

namespace App\Http\Controllers\FileManager;

use App\Http\Controllers\Controller;
use App\Models\Accounting\RealAccount;
use App\Models\FileManager\FileManager;
use App\Models\FileManager\FileManagerFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class FileManagerController extends Controller
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
    public function show($id): JsonResponse
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
            $data = new FileManager();

            $data->name = $request->name;
            $data->description = $request->description;
            $data->file_manager_category_id = $request->category;
            if (! empty($request->date)) {
                $data->date = date('Y-m-d', strtotime($request->date));
            }
            if (! empty($request->due_date)) {
                $data->due_date = date('Y-m-d', strtotime($request->due_date));
            }

            if ($request->hasfile('files')) {
                foreach ($request->file('files') as $key => $file) {
                    $data1 = new FileManagerFile();
                    $data1->name = $request->file[$key];
                    $data1->file_manager_id = $data->id;
                    $data1->business_id = $request->user()->profile->business_id;
                    $path = $file->store('public/documents');
                    $data1->file_id = $file->hashName();
                    $data1->extension = $file->extension();
                    $data1->path = $path;
                    $data1->save();
                }

            }

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
    public function update(Request $request, FileManager $data): JsonResponse
    {
        try {
            DB::beginTransaction();
            $data = FileManager::find($request->id);
            $data->name = $request->name;
            $data->file_manager_category_id = $request->category;
            $data->description = $request->description;
            if (! empty($request->date)) {
                $data->date = date('Y-m-d', strtotime($request->date));
            }
            if (! empty($request->due_date)) {
                $data->due_date = date('Y-m-d', strtotime($request->due_date));
            }
            $data->save();

            if ($request->hasfile('files')) {
                foreach ($request->file('files') as $key => $file) {
                    $data1 = new FileManagerFile();
                    $data1->name = $request->file[$key];
                    $data1->file_manager_id = $data->id;
                    $data1->business_id = $request->user()->profile->business_id;
                    $path = $file->store('public/documents');
                    $data1->file_id = $file->hashName();
                    $data1->extension = $file->extension();
                    $data1->path = $path;
                    $data1->save();
                }

            }
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
    public function destroy(Request $request, FileManager $data): JsonResponse
    {
        try {
            $data = FileManager::find($request->id);
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
        return response()->json(RealAccount::latest()->get());
    }
}
