<?php

namespace Modules\MarketData\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Modules\MarketData\Entities\InvestorsData;
use Modules\MarketData\Imports\FundInvestorsDataImport;
use Modules\MarketData\Imports\MarketDataImport;

class MarketDataController extends Controller
{
    public function company(Request $request): JsonResponse
    {
        try {
            $response = \DB::table('securities')->find($request->id);
            $response->stats = \DB::table('market_data')->where('company_id', $request->id)->latest('date')->first();
            $stats = \DB::table('market_data')->where('company_id', $request->id)->get();
            $series = [];
            if (! empty($stats)) {
                foreach ($stats as $stat) {
                    $series[] = [strtotime($stat->date) * 1000, $stat->open];
                }
                $response->start = strtotime(now()->subDays(7)) * 1000;
                $response->end = strtotime(now()) * 1000;
            }
            $response->chart = $series;

            return response()->json($response);
        } catch (\Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function companies(Request $request): JsonResponse
    {
        try {
            $response = \DB::table('securities')->select('id', 'name')->orderBy('name')->get();

            return response()->json($response);
        } catch (\Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function market_data(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : env('PERPAGE');
            $response = \DB::table('market_data')->latest('date')->paginate($per_page);

            return response()->json($response);
        } catch (\Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function market_data_filter(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : env('PERPAGE');
            $response = \DB::table('market_data')
                ->whereDate('date', '>=', $request->start)
                ->whereDate('date', '<=', $request->end)
                ->latest('date')->paginate($per_page);

            return response()->json($response);
        } catch (\Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function add_market_data(Request $request): JsonResponse
    {
        try {
            Excel::import(new FundInvestorsDataImport($request->date), request()->file('file'));
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $response = \DB::table('market_data')->latest('date')->paginate($per_page);

            return response()->json($response);
        } catch (\Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }
}
