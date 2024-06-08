<?php

namespace Modules\Custodians\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Custodians\Entities\Custodian;
use Throwable;

class CustodiansController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {

            $validation =   Validator::make($request->all(), [
               "ledger" => ['required','max:191','unique:custodians'],
               "name" => ['required','max:191','unique:custodians'],
               "email" => ['nullable','email'],
               "contact_person" => ['nullable','max:191']
            ]);

            if($validation->invalid()){
             return $this->onErrorResponse($validation->messages()->first());
            }

            $data = new Custodian();
            $data->user_id = auth()->id();
            $data->ledger = $request->ledger;
            $data->contact_person = $request->contact_person??"";
            $data->name = $request->name;
            $data->email = $request->email;
            $data->save();

            return $this->custodians();

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function update(Request $request): JsonResponse
    {
        try {

            $validation =   Validator::make($request->all(), [
                "ledger" => ['required','max:191'],
                "name" => ['required','max:191',Rule::unique("custodians","ledger")->ignore($request->id,"id")],
                "email" => ['nullable','email'],
                "contact_person" => ['nullable','max:191']
            ]);

            if($validation->invalid()){
                return $this->onErrorResponse($validation->messages()->first());
            }

            $data = Custodian::findOrFail($request->id);
            $data->ledger = $request->ledger;
            $data->contact_person = $request->contact_person??"";
            $data->name = $request->name;
            $data->email = $request->email;
            $data->save();

            return $this->custodians();

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function delete(Request $request): JsonResponse
    {
        try {

            $data = Custodian::findOrFail($request->id);
            $data->delete();

            return $this->custodians();

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function custodians(): JsonResponse
    {
        try {
            return response()->json(Custodian::latest()->get());
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }
}
