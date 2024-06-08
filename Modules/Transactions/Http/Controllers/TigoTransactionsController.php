<?php

namespace Modules\Transactions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TigoTransactionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = \DB::table('tigo_requests');
            if (! empty($request->client)) {
                $query = $query->where('customer_id', $request->client);
            }
            if (! empty($request->start)) {
                $query = $query->whereDate('date', '>=', date('Y-m-d', strtotime($request->start)));
            }
            if (! empty($request->end)) {
                $query = $query->whereDate('date', '<=', date('Y-m-d', strtotime($request->end)));
            }
            $result = $query->paginate();

            return response()->json($result);
        } catch (\Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }
}
