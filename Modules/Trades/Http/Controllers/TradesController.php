<?php

namespace Modules\Trades\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Modules\Audits\Exports\DealingSheetsExport;
use App\Http\Controllers\Controller;
use App\Models\Accounting\FinancialYear;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Trades\Entities\Trade;
use Modules\Trades\Exports\TradeSheetsExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class TradesController extends Controller
{

    public function filter_trade(Request $request): JsonResponse
    {
        try{
            $per_page = !empty($request->per_page) ? $request->per_page: getenv("PERPAGE");
            $query = Trade::latest("trade_date");

            if(!empty($request->client)){
                $query = $query->orWhere("client_id",strtolower($request->client));
            }

            if(!empty($request->value)){
                $query = $query->orWhere("reference","LIKE",'%'.$request->value.'%');
            }

            if(!empty($request->q)){
                $query = $query->orWhere("status","LIKE",'%'.$request->q.'%');
            }

            $order = $query->paginate($per_page);
            return  response()->json($order);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function filter_timely(Request $request): JsonResponse
    {
        try{
            $financialYear = FinancialYear::where("status",1)->first();

            $query = Trade::query();
            $per_page = !empty($request->per_page) ? $request->per_page: getenv("PERPAGE");

            $now = now(getenv("TIMEZONE"));

            if(strtolower($request->q) == "today" ) {
                $today = $now->toDateString();
                $query = $query->whereDate("trade_date", $today);
            }

            if(strtolower($request->q) == "weekly" ) {
                $start = $now->startOfWeek()->toDateString();
                $end = $now->endOfWeek()->toDateString();
                $query = $query->where("trade_date", ">=", $start)->where("trade_date", "<=", $end);
            }

            if(strtolower($request->q) == "monthly" ) {
                $start = $now->startOfMonth()->toDateString();
                $end = $now->endOfMonth()->toDateString();
                $query = $query->where("trade_date", ">=", $start)->where("trade_date", "<=", $end);
            }

            if(strtolower($request->q) == "annually" ) {
                $query = $query->where("trade_date", ">=", $financialYear->year_start)->where("trade_date", "<=", $financialYear->year_end);
            }

            $query = $query->latest("trade_date");

            $order = $query->paginate($per_page);
            return  response()->json($order);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

      public function search_trade(Request $request): JsonResponse
    {
        try{
            $query = Trade::latest("trade_date");

            $clients = User::where("name","LIKE",'%'.$request->q.'%')->pluck("id");
            if(!empty($clients)){
                $query = $query->whereIn("client_id",$clients);
            }

            if(!empty($request->q)){
                $query = $query->orWhere("uid",'LIKE','%'.$request->q.'%');
            }

            $order = $query->get();
            return  response()->json($order);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function trades(Request $request): JsonResponse
    {
        try{
            $per_page = !empty($request->per_page) ? $request->per_page: getenv("PERPAGE");
            $trades = DB::table("dealing_sheets")
                ->select([
                    'dealing_sheets.*',
                    'securities.name as security_name',
                    'users.name as client_name',
                    'orders.uid as order_number'
                ])
                ->leftJoin("securities","dealing_sheets.security_id","=","securities.id")
                ->leftJoin("orders","dealing_sheets.order_id","=","orders.id")
                ->leftJoin("users","dealing_sheets.client_id","=","users.id")
                ->latest("trade_date")
                ->paginate($per_page);
            return  response()->json($trades);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function stats(){
        try {
            $financialYear = FinancialYear::where("status","active")->first();
            $now = now(getenv("TIMEZONE"));

            $today = $now->toDateString();
            $data['today_count'] = Trade::whereDate("trade_date",$today)->count();
            $data['today'] = Trade::whereDate("trade_date",$today)->sum("brokerage");

            $start = $now->startOfWeek()->toDateString();
            $end = $now->endOfWeek()->toDateString();
            $data['weekly_count'] = Trade::where("trade_date",">=",$start)->where("trade_date","<=",$end)->count();
            $data['weekly'] = Trade::where("trade_date",">=",$start)->where("trade_date","<=",$end)->sum("brokerage");

            $start = $now->startOfMonth()->toDateString();
            $end = $now->endOfMonth()->toDateString();
            $data['monthly_count'] = Trade::where("trade_date",">=",$start)->where("trade_date","<=",$end)->count();
            $data['monthly'] = Trade::where("trade_date",">=",$start)->where("trade_date","<=",$end)->sum("brokerage");

            $data['annually_count'] = Trade::where("trade_date",">=",$financialYear->year_start)->where("trade_date","<=",$financialYear->year_end)->count();
            $data['annually'] = Trade::where("trade_date",">=",$financialYear->year_start)->where("trade_date","<=",$financialYear->year_end)->sum("brokerage");
            return response()->json($data);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function export(Request $request): BinaryFileResponse
    {
        return (new TradeSheetsExport)->status($request->status??"")->from($request->from)->end($request->end)->download('exports.xlsx');
    }

}
