<?php

namespace Modules\DSE\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\DSE\DTOs\BuyShareDTO;
use Modules\DSE\DTOs\DSEPayloadDTO;
use Modules\DSE\DTOs\InvestorAccountDetailsDTO;
use Modules\DSE\DTOs\IPOBuyOrderDTO;
use Modules\DSE\DTOs\PledgeTransactionsDTO;
use Modules\DSE\DTOs\PullBuyOrderDTO;
use Modules\DSE\DTOs\PullSellOrderDTO;
use Modules\DSE\DTOs\SellShareDTO;
use Modules\DSE\Helpers\DSEHelper;
use Throwable;

class DSEController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function callback_investor_registration(Request $request)
    {

        try {
            $request = (object) $request;
            if ($request->code == 9000) {
                $investorUser = User::where('id', $request->data['requestId'])
                    ->first();
                if (! empty($investorUser)) {
                    $investorUser->dse_account = $request->data['csdAccount'];
                    $investorUser->dse_synced = 'yes';
                    $investorUser->dse_status_message = '';
                    $investorUser->save();

                    return response()->json('Callback success with request ID '.$request->data['requestId']);
                } else {
                    \Log::error('DSE could not fine request ID '.$request->data['requestId']);

                    return response()->json('DSE could not fine request ID '.$request->data['requestId']);
                }
            } else {

                $investorUser = User::where('id', $request->data['requestId'] ?? '')
                    ->first();
                if (! empty($investorUser)) {
                    $error = DSEHelper::errorMapper($request->code);
                    $investorUser->dse_status_message = $error->message;
                    $investorUser->save();
                }

                return response()->json($request);
            }
        } catch (Throwable $throwable) {
            \Log::error($throwable->getMessage());
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * @return JsonResponse
     */
    public function pull_ipo_companies()
    {
        try {
            return response()->json(DSEHelper::getIPOCompanies());
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * @return JsonResponse
     */
    public function create_ipo_buy_order(Request $request)
    {
        try {
            $data = IPOBuyOrderDTO::fromRequest($request);

            return response()->json(DSEHelper::createIPOBuyOrder($data));
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * @return JsonResponse
     */
    public function buy_shares(Request $request)
    {
        try {
            $data = BuyShareDTO::fromRequest($request);

            return response()->json(DSEHelper::buyShares($data));
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * @return JsonResponse
     */
    public function sell_shares(Request $request)
    {
        try {
            $data = SellShareDTO::fromRequest($request);

            return response()->json(DSEHelper::sellShares($data));
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * @return JsonResponse
     */
    public function market_data(Request $request)
    {
        try {
            return response()->json(DSEHelper::marketData());
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * @return JsonResponse
     */
    public function market_data_statistics(Request $request)
    {
        try {
            $data = DSEPayloadDTO::fromRequest($request);

            return response()->json(DSEHelper::marketDataStatistics($data));
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * @return JsonResponse
     */
    public function get_buy_order_details(Request $request)
    {
        try {
            return response()->json(DSEHelper::getBuyOrderDetails($request->nida, $request->reference));
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * @return JsonResponse
     */
    public function get_sell_order_details(Request $request)
    {
        try {
            return response()->json(DSEHelper::getSellOrderDetails($request->nida, $request->reference));
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * @return JsonResponse
     */
    public function create_token()
    {
        try {
            return response()->json(DSEHelper::createToken());
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * @return JsonResponse
     */
    public function brokers()
    {
        try {
            return response()->json(DSEHelper::getBrokers());
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * @return JsonResponse
     */
    public function get_buy_orders(Request $request)
    {
        try {
            $data = PullBuyOrderDTO::fromRequest($request);

            return response()->json(DSEHelper::getBuyOrders($data));
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * @return JsonResponse
     */
    public function get_sell_orders(Request $request)
    {
        try {
            $data = PullSellOrderDTO::fromRequest($request);

            return response()->json(DSEHelper::getSellOrders($data));
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * @return JsonResponse
     */
    public function account_details(Request $request)
    {
        try {
            $data = DSEPayloadDTO::fromRequest($request);

            return response()->json(DSEHelper::accountDetails($data));
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * @return JsonResponse
     */
    public function investor_holdings(Request $request)
    {
        try {
            $data = DSEPayloadDTO::fromRequest($request);

            return response()->json(DSEHelper::investorHoldings($data));
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * @return JsonResponse
     */
    public function create_account(Request $request)
    {
        try {
            $accountData = InvestorAccountDetailsDTO::fromRequest($request);

            return response()->json(DSEHelper::createAccount($accountData));
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * @return JsonResponse
     */
    public function verifyAccount(Request $request)
    {
        try {
            $data = DSEPayloadDTO::fromRequest($request);

            return response()->json(DSEHelper::verifyAccount($data));
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * @return JsonResponse
     */
    public function pledge_transactions(Request $request)
    {
        try {
            $data = PledgeTransactionsDTO::fromRequest($request);

            return response()->json(DSEHelper::pledge_transactions($data));
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * @return JsonResponse
     */
    public function release_transaction(Request $request)
    {
        try {
            $data = PledgeTransactionsDTO::fromRequest($request);

            return response()->json(DSEHelper::releaseTransaction($data));
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * @return JsonResponse
     */
    public function verifyLinkage(Request $request)
    {
        try {
            $payload = DSEPayloadDTO::fromRequest($request);
            return response()->json(DSEHelper::verifyLinkage($payload));
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }


    /**
     * @return JsonResponse
     */
    public function signature(Request $request)
    {
        try {
            return response()->json(DSEHelper::signedPayload(['nida' => '1232145347609456845']));
        } catch (Throwable $throwable) {
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('dse::index');
    }
}
