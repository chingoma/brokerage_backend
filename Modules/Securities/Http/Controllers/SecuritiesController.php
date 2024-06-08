<?php

namespace Modules\Securities\Http\Controllers;

use App\Exports\Customers\CustomersExportAll;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Securities\Entities\Security;
use Modules\Securities\Exports\SecurityInvestorsExport;

class SecuritiesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            if(!auth()->user()->tokenCan("companies_read")){
                return $this->onUnauthorized();
            }
            $securities = Security::get();
            return response()->json($securities);
        }catch (\Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function investors(Request  $request)
    {
        try {
//            if(!auth()->user()->tokenCan("companies_read")){
//                return $this->onUnauthorized();
//            }

            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $query = DB::table('dealing_sheets')
                ->select([
                    'securities.id',
                    'users.name',
                    'users.type',
                    'users.email',
                    'dealing_sheets.client_id',
                ])
                ->groupBy(['dealing_sheets.security_id','dealing_sheets.client_id'])
                ->selectRaw("sum(IF(dealing_sheets.type='buy',dealing_sheets.executed,0)) - sum(IF(dealing_sheets.type='sell',dealing_sheets.executed,0)) as volume")
                ->selectRaw("users.flex_acc_no as uid")
                ->where("dealing_sheets.security_id",$request->id)
                ->where("dealing_sheets.status","approved")
                ->whereNull('securities.deleted_at')
                ->whereNull('dealing_sheets.deleted_at')
                ->leftJoin('securities', 'dealing_sheets.security_id', '=', 'securities.id')
                ->leftJoin('users', 'dealing_sheets.client_id', '=', 'users.id');

            $investors = $query->paginate($per_page);

            return response()->json($investors);
        }catch (\Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }


    public function investors_download(Request  $request)
    {
        try {
//            if(!auth()->user()->tokenCan("companies_read")){
//                return $this->onUnauthorized();
//            }
//            header('Access-Control-Allow-Origin: *');
            return (new SecurityInvestorsExport($request->id))->download('exports.xlsx');

        }catch (\Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function details(Request $request)
    {
        try {

//            if(!auth()->user()->tokenCan("companies_read")){
//                return $this->onUnauthorized();
//            }

            $query = DB::table('dealing_sheets')
                ->select(['securities.*'])
                ->selectRaw("(sum(IF(dealing_sheets.type='buy',dealing_sheets.executed,0)) - sum(IF(dealing_sheets.type='sell',dealing_sheets.executed,0))) as volume")
                ->selectRaw("avg(dealing_sheets.price) as price")
                ->selectRaw("(sum(IF(dealing_sheets.type='buy',dealing_sheets.executed,0)) - sum(IF(dealing_sheets.type='sell',dealing_sheets.executed,0))) * avg(dealing_sheets.price) as turnover")
                ->where("dealing_sheets.status","approved")
                ->where("dealing_sheets.security_id",$request->id)
                ->whereNull('securities.deleted_at')
                ->whereNull('dealing_sheets.deleted_at')
                ->groupBy(['dealing_sheets.security_id'])
                ->leftJoin('securities', 'dealing_sheets.security_id', '=', 'securities.id');

            $security = $query->groupBy(['dealing_sheets.security_id'])
                ->first();

            return response()->json($security);

        }catch (\Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

}
