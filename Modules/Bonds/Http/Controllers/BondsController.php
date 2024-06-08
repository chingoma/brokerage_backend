<?php

namespace Modules\Bonds\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Bonds\Entities\Bond;
use Modules\Bonds\Entities\BondAuction;
use Modules\Bonds\Imports\AuctionsImport;
use Modules\MarketData\Imports\MarketDataImport;
use Throwable;

class BondsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function settings(Request $request)
    {
        return response()->json([]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        return $this->bonds($request);
    }

    public function auctions(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $response = BondAuction::latest('date')->paginate($per_page);

            return response()->json($response);
        } catch (\Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function auctions_filter(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $response = \DB::table('bond_auctions')
                ->whereDate('date', '>=', $request->start)
                ->whereDate('date', '<=', $request->end)
                ->latest('date')->paginate($per_page);

            return response()->json($response);
        } catch (\Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }
    public function add_auctions(Request $request): JsonResponse
    {
        try {
            DB::table("bond_auctions")->truncate();
            Excel::import(new AuctionsImport($request->date), request()->file('file'));
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $response = \DB::table('bond_auctions')->latest('date')->paginate($per_page);

            return response()->json($response);
        } catch (\Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $bond = new Bond();
            $bond->security_name = $request->security_name;
            $bond->market = $request->market;
            if (strtolower($request->market) == 'secondary') {
                $bond->number = $request->number;
                $bond->type = $request->type;
            } else {
                $bond->number = '';
                $bond->type = '';
            }
            $bond->yield_time_maturity = $request->yield_time_maturity;
            $bond->category = $request->category;
            $bond->isin = $request->isin;
            $bond->coupon = $request->coupon;
            $bond->tenure = $request->tenure;
            $bond->issue_date = $request->issue_date;
            $bond->maturity_date = $request->maturity_date;
            $bond->issued_amount = $request->issued_amount;
            $bond->save();
            DB::commit();

            return $this->bonds($request);
        } catch (Throwable $ex) {
            DB::rollBack();
            report($ex);

            return $this->onErrorResponse($ex->getMessage());
        }
    }

    /**
     * Show the specified resource.
     *
     * @param  string  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        try {
            $bond = Bond::findOrFail($id);

            return response()->json($bond);
        } catch (Throwable $ex) {
            DB::rollBack();
            report($ex);

            return $this->onErrorResponse($ex->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @return JsonResponse
     */
    public function update(Request $request, string $id)
    {
        try {
            DB::beginTransaction();
            $bond = Bond::findOrFail($id);
            $bond->security_name = $request->security_name;
            $bond->market = $request->market;
            $bond->yield_time_maturity = $request->yield_time_maturity;
            $bond->category = $request->category;
            if (strtolower($request->market) == 'secondary') {
                $bond->number = $request->number;
                $bond->type = $request->type;
            } else {
                $bond->number = '';
                $bond->type = '';
            }
            $bond->isin = $request->isin;
            $bond->coupon = $request->coupon;
            $bond->tenure = $request->tenure;
            $bond->issue_date = $request->issue_date;
            $bond->maturity_date = $request->maturity_date;
            $bond->issued_amount = $request->issued_amount;
            $bond->save();
            DB::commit();

            return $this->bonds($request);
        } catch (Throwable $ex) {
            DB::rollBack();
            report($ex);

            return $this->onErrorResponse($ex->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            $bond = Bond::findOrFail($id);
            $bond->delete();
        } catch (Throwable $throwable) {
            report($throwable);
        }

        return $this->bonds($request);

    }

    public function bonds(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : env('PERPAGE');
            $query = Bond::latest('created_at');

            $bonds = $query->paginate($per_page);

            return response()->json($bonds);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }
}
