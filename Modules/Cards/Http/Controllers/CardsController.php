<?php

namespace Modules\Cards\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CardsController extends Controller
{
    public function topCustomersEquities(): JsonResponse
    {
        try {

            $top = \DB::table('dealing_sheets')
                ->where('dealing_sheets.status', 'approved')
                ->groupBy(['users.name', 'users.id'])
                ->selectRaw('users.name')
                ->selectRaw('users.id')
                ->selectRaw('customer_categories.name as category')
                ->selectRaw('sum(dealing_sheets.brokerage) as total')
                ->leftJoin('users', 'dealing_sheets.client_id', '=', 'users.id')
                ->leftJoin('customer_categories', 'users.category_id', '=', 'customer_categories.id')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get();

            return \response()->json($top);
        } catch (\Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse('');
        }
    }

    public function topCustomersBonds(): JsonResponse
    {
        try {

            $top = \DB::table('bond_executions')
                ->where('bond_executions.status', 'approved')
                ->groupBy(['users.name', 'users.id'])
                ->selectRaw('users.name')
                ->selectRaw('users.id')
                ->selectRaw('customer_categories.name as category')
                ->selectRaw('sum(bond_executions.brokerage) as total')
                ->leftJoin('users', 'bond_executions.client_id', '=', 'users.id')
                ->leftJoin('customer_categories', 'users.category_id', '=', 'customer_categories.id')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get();

            return \response()->json($top);
        } catch (\Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse('');
        }
    }

    public function equityBondRevenue(): JsonResponse
    {
        try {

            $equity = \DB::table('dealing_sheets')
                ->where('dealing_sheets.status', 'approved')
                ->sum('brokerage');

            $bond = \DB::table('bond_executions')
                ->where('bond_executions.status', 'approved')
                ->sum('brokerage');

            return \response()->json(['equity' => $equity, 'bond' => $bond]);
        } catch (\Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse('');
        }
    }
}
