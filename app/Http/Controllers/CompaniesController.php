<?php

namespace App\Http\Controllers;

use App\Models\Security;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class CompaniesController extends Controller
{
    public function bonds(Request $request)
    {
        return response()->json(Security::where('type', 'bond')->get(), 200);
    }

    public function companies(Request $request)
    {
        return response()->json(Security::where('type', 'security')->get(), 200);
    }

    public function sectors(Request $request)
    {
        return response()->json(DB::table("security_sectors")->get(), 200);
    }

    public function create(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                "name" => ['required', 'string', 'max:255',Rule::unique("securities", "name")],
                "fullname" => ['required', 'string', 'max:255',Rule::unique("securities", "fullname")],
                "sector" => ['required', 'string', 'max:255'],
                "logo" => ['required','file','mimes:jpg,png,jpeg'],
            ]);

            if($validator->fails()){
                return response()->json(['status' => false, 'message' => $validator->messages()->first()], 500);
            }

            DB::beginTransaction();

            $company = new Security();
            $company->name = $request->name;
            $company->ledger = $request->ledger??"";
            $company->sector_id = $request->sector;
            $company->fullname = $request->fullname;
            $company->type = 'security';
            if (! empty($request->logo) && $request->hasFile('logo')) {
                $file = $request->file('logo')->storePublicly();
                $company->logo = $file;
            }

            $company->save();

            DB::commit();

            return response()->json(['status' => true]);

        } catch (Throwable $ex) {
            DB::rollBack();
            report($ex);

            return response()->json(['status' => false, 'message' => $ex->getMessage()], 500);
        }

    }

    public function edit(Request $request): JsonResponse
    {

        $validator = Validator::make($request->all(), [
            "name" => ['required', 'string', 'max:255',Rule::unique("securities", "name")->ignore($request->id)],
            "sector" => ['required', 'string', 'max:255'],
            "fullname" => ['required', 'string', 'max:255',Rule::unique("securities", "name")->ignore($request->id)]
        ]);
        if($validator->fails()){
            return response()->json(['status' => false, 'message' => $validator->messages()->first()], 500);
        }
        try {
            DB::beginTransaction();

            $company = Security::find($request->id);
            $company->ledger = $request->ledger??"";
            $company->name = strtoupper($request->name);
            $company->fullname = $request->fullname;
            $company->sector_id = $request->sector;
            if ($request->hasFile('logo')) {
                $fileName = time().uuid_create().'.'.$request->file('logo')->extension();
                $request->file('logo')->storeAs('public/images', $fileName);
                $company->logo = asset('storage/images/'.$fileName);
            }

            $company->save();

            DB::commit();

            return response()->json(['status' => true]);

        } catch (Throwable $ex) {
            DB::rollBack();
            report($ex);

            return response()->json(['status' => false, 'message' => $ex->getMessage()], 500);
        }
    }
}
