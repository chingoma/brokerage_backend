<?php

namespace App\Http\Controllers\FileManager;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\FileManager\FileManager;
use App\Models\FileManager\FileManagerCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class FileManagerCategoryController extends Controller
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

            $category = new FileManagerCategory();
            $category->business_id = Helper::business()->id;
            $category->name = $request->name;
            $category->save();

            return response()->json(FileManagerCategory::get());

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FileManagerCategory $category): JsonResponse
    {
        try {
            $category = FileManagerCategory::find($request->id);
            $category->name = $request->name;
            $category->save();

            return $this->__list();
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  FileManagerCategory  $category
     */
    public function destroy(Request $request): JsonResponse
    {
        try {
            $files = FileManager::where('file_manager_category_id', $request->id)->count();
            if ($files > 0) {
                return response()->json('You can not delete this category, delete files first', 500);
            }
            $category = FileManagerCategory::find($request->id);
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
        return response()->json(FileManagerCategory::latest()->get());
    }
}
