<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class DepartmentsController extends Controller
{
    public function departments(Request $request)
    {
        return response()->json(Department::get(), 200);
    }

    public function create(Request $request)
    {

        try {
            DB::beginTransaction();
            $department = new Department();
            $department->name = $request->name;
            $department->save();

            DB::commit();

            return response()->json(['status' => true, 'departments' => Department::get()], 200);

        } catch (Throwable $ex) {
            DB::rollBack();
            report($ex);

            return response()->json(['status' => false, 'message' => 'registration failed failed '.$ex->getMessage()], 500);
        }
    }

    public function edit(Request $request)
    {

        try {
            DB::beginTransaction();
            $department = Department::find($request->id);
            $department->name = $request->name;
            $department->save();

            DB::commit();

            return response()->json(['status' => true, 'departments' => Department::get()], 200);

        } catch (Throwable $ex) {
            DB::rollBack();
            report($ex);

            return response()->json(['status' => false, 'message' => 'registration failed failed '.$ex->getMessage()], 500);
        }
    }

    public function delete(Request $request)
    {

        try {
            DB::beginTransaction();
            $department = Department::find($request->id);
            $department->delete();

            DB::commit();

            return response()->json(Department::get(), 200);

        } catch (Throwable $ex) {
            DB::rollBack();
            report($ex);

            return response()->json(['status' => false, 'message' => 'registration failed failed '.$ex->getMessage()], 500);
        }
    }
}
