<?php

namespace Modules\Bonds\Http\Controllers;

use App\Helpers\BondsHelper;
use App\Helpers\Helper;
use App\Helpers\Pdfs\ContractNoteBondPdf;
use App\Helpers\Pdfs\ContractNoteBondPrimaryPdf;
use App\Http\Controllers\Controller;
use App\Models\Accounting\Transaction;
use App\Models\User;
use Dotenv\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use JetBrains\PhpStorm\NoReturn;
use Modules\Bonds\Entities\BondExecution;
use Modules\Bonds\Entities\BondOrder;
use Throwable;

class BondExecutionsController extends Controller
{
    #[NoReturn]
    public function downloadContractNote(Request $request)
    {
        $dealingSheet = BondExecution::find($request->id);
        $order = BondOrder::find($dealingSheet->order_id);
        if(strtolower($order->market == "primary")){
            $pdf = new ContractNoteBondPrimaryPdf();
        }else{
            $pdf = new ContractNoteBondPdf();
        }
        $filename = $pdf->create($order, $dealingSheet);

        header('Access-Control-Allow-Origin: *');
        //        header('Access-Control-Allow-Origin: https://demo.brokerlink.co.tz');
        //        header("strict-transport-security: max-age=600");
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($filename).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: '.filesize($filename));
        readfile($filename);
        exit;
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $order = BondExecution::select('bond_executions.*', DB::raw('users.name'))
                ->where('bond_executions.id', 'LIKE', "%{$request->q}%")
                ->orWhere('bond_executions.status', 'LIKE', "%{$request->q}%")
                ->orWhere('users.name', 'LIKE', "%{$request->q}%")
                ->leftJoin('users', 'users.id', '=', 'bond_executions.client_id')
                ->get();

            return response()->json($order);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function update_status(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $order = BondExecution::find($request->id);
            $orderData = BondOrder::find($order->order_id);


            if ($request->status == 'approved') {
                if ($order->updated_by == $request->header('id')) {
                    return response()->json(['message' => 'Maker Checker Failed'], 500);
                }
            }

            Transaction::where('reference', $order->slip_no)->delete();

            if (! empty($orderData)) {

                if (strtolower($request->status) == 'pending') {
                    BondsHelper::clearFees($order);
                }

                if (strtolower($request->status) == 'cancelled') {
                    BondsHelper::clearFees($order);
                }

                if (strtolower($request->status) == 'approved') {
                    BondsHelper::setCommissions($order);
                }
            }

            $order->status = $request->status;
            $order->updated_by = $request->header('id');
            $order->save();
            if (! empty($orderData)) {
                if (strtolower($request->status) == 'approved') {
                    if (strtolower($orderData->type == 'buy')) {
                        BondsHelper::_process_order_buy($order);
                    } else {
                        BondsHelper::_process_order_sell($order);
                    }
                }

                BondsHelper::updateOrderStatus($orderData->id);
            }
            DB::commit();

            Helper::newEventStream();
            return response()->json($order);
        } catch (Throwable $throwable) {
            DB::rollback();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function reject(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $order = BondExecution::find($request->id);
            Transaction::where('reference', $order->slip_no)->delete();

            BondsHelper::clearFees($order);

            $order->status = "rejected";
            $order->updated_by = $request->header('id');
            $order->save();
            DB::commit();

            Helper::newEventStream();
            return $this->onSuccessResponse("Contract note rejected successfully");
        } catch (Throwable $throwable) {
            DB::rollback();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function approve(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $order = BondExecution::find($request->id);
            $orderData = BondOrder::find($order->order_id);

            if ($order->updated_by == $request->header('id')) {
                return response()->json(['message' => 'Maker Checker Failed'], 500);
            }

            Transaction::where('reference', $order->slip_no)->delete();

            if (strtolower($request->status) == 'approved') {
                BondsHelper::setCommissions($order);
            }

            $order->status = "approved";
            $order->updated_by = $request->header('id');
            $order->save();
            if (! empty($orderData)) {
                if (strtolower($orderData->type == 'buy')) {
                    BondsHelper::_process_order_buy($order);
                } else {
                    BondsHelper::_process_order_sell($order);
                }
                BondsHelper::updateOrderStatus($orderData->id);
            }

            DB::commit();

//            Helper::newEventStream();
            return $this->onSuccessResponse("Contract note approved successfully");
        } catch (Throwable $throwable) {
            DB::rollback();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function approveFix(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $order = BondExecution::find("018fd308-6128-7136-8a14-0d0b1aa455cd");
            $orderData = BondOrder::find($order->order_id);
            $order->price = 100.9844;

            Transaction::where('reference', $order->slip_no)->delete();

            if (strtolower($request->status) == 'approved') {
                BondsHelper::setCommissions($order);
            }

            $order->status = "approved";
            $order->updated_by = $request->header('id');
            $order->save();
            if (! empty($orderData)) {
                if (strtolower($orderData->type == 'buy')) {
                    BondsHelper::_process_order_buy($order);
                } else {
                    BondsHelper::_process_order_sell($order);
                }
            }

            DB::commit();

//            Helper::newEventStream();
            return $this->onSuccessResponse("Contract note approved successfully");
        } catch (Throwable $throwable) {
            DB::rollback();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function send_contract_note(Request $request): JsonResponse
    {
        $order = BondExecution::findOrFail($request->id);
        $order->email_sent = 'yes';
        $order->save();
        $orderData = BondOrder::find($order->order_id);
        $this->send_bond_executed($orderData, $order);

        return response()->json($order);
    }

    public function confirmation_by_reference(Request $request): JsonResponse
    {
        try {
            $order = BondExecution::where('slip_no', $request->id)->firstOrFail();

            return response()->json($order);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json(['status' => false, 'message' => 'Failed to update '.$throwable->getMessage()], 500);
        }
    }

    public function confirmation_update(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $order = BondExecution::findOrFail($request->id);
            $orderData = BondOrder::findOrfail($order->order_id);
            Transaction::where('reference', $order->slip_no)->delete();
            BondsHelper::clearFees($order);
            $order->type = $orderData->type;
            $order->market = $orderData->market;
            $order->category = $orderData->category;
            $order->bond_type = $orderData->type;
            $order->settlement_date = Helper::settlementDateBond($order->trade_date);
            $order->executed = floatval(str_ireplace(',', '', $request->executed));
            $order->price = floatval(str_ireplace(',', '', $request->price));
            $order->face_value = str_ireplace(',', '', $request->executed);
            $order->amount = ($order->price * $order->face_value) / 100;
            $order->status = 'pending';
            $order->slip_no = str_ireplace(',', ' ', $request->slip_no);
            $order->updated_by = $request->header('id');
            if ($orderData->has_custodian == 'yes') {
                $order->has_custodian = $orderData->has_custodian;
                $order->custodian_id = $orderData->custodian_id;
            }
            BondsHelper::setCommissions($order);
            $order->save();
            DB::commit();

            return response()->json($order);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json(['status' => false, 'message' => 'Failed to update '.$throwable->getMessage()], 500);
        }
    }

    public function trades(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $order = BondExecution::latest('trade_date')->paginate($per_page);

            return response()->json($order);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function confirmations(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $order = DB::table("bond_executions")
                ->select([
                    'bond_executions.*',
                    'bonds.security_name',
                    'bond_orders.uid as order_number',
                    'users.name as client_name',
                ])
                ->leftJoin("bonds","bond_executions.bond_id","=","bonds.id")
                ->leftJoin("bond_orders","bond_executions.order_id","=","bond_orders.id")
                ->leftJoin("users","bond_executions.client_id","=","users.id")
                ->latest('auto')->paginate($per_page);

            return response()->json($order);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function order_confirmations(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $order = BondExecution::latest('trade_date')->where('order_id', $request->id)->paginate($per_page);

            return response()->json($order);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function customer_confirmations(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $order = DB::table("bond_executions")
                ->select([
                    'bond_executions.*',
                    'bonds.security_name',
                    'bond_orders.uid as order_number'
                ])
                ->leftJoin("bonds","bond_executions.bond_id","=","bonds.id")
                ->leftJoin("bond_orders","bond_executions.order_id","=","bond_orders.id")
                ->latest("trade_date")
                ->where('bond_executions.client_id', $request->id)
                ->paginate($per_page);

            return response()->json($order);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function dealing_sheets(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $order = BondOrder::latest('date')
                ->where('status', '!=', 'cancelled')
                ->where('status', '!=', 'pending')
                ->where('status', '!=', 'new')
                ->where('status', '!=', 'rejected')
                ->where('status', '!=', 'complete')
                ->paginate($per_page);

            return response()->json($order);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function confirmation(Request $request): JsonResponse
    {
        try {
            $order = DB::table("bond_executions")
                ->select([
                    'bond_executions.*',
                    'bonds.security_name',
                    'bond_orders.uid as order_number',
                    'users.name as client_name',
                ])
                ->leftJoin("bonds","bond_executions.bond_id","=","bonds.id")
                ->leftJoin("bond_orders","bond_executions.order_id","=","bond_orders.id")
                ->leftJoin("users","bond_executions.client_id","=","users.id")
                ->where("bond_executions.id",$request->id)
                ->first();

            return response()->json($order);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }


    public function store(Request $request): JsonResponse
    {

        try {

            $validated = \Illuminate\Support\Facades\Validator::make($request->all(), [
                "ytm" => ['required', 'numeric'],
                "executed" => ['required', 'numeric'],
                "price" => ["required", "numeric"],
                "slip_no" => ["required",Rule::unique('bond_executions', 'slip_no')],
            ]);

            if ($validated->fails()) {
                return response()->json(['status' => false, 'message' => $validated->messages()->first()], 500);
            }

            DB::beginTransaction();

            $systemDate = Helper::systemDateTime();
            $reference = Str::squish(str_ireplace(',', ' ', $request->slip_no));
            $referenceCheck = BondExecution::where('slip_no', $reference)->first();
            if (! empty($referenceCheck)) {
                return response()->json(['status' => false, 'message' => 'Found A Record with same Trade Confirmation'], 500);
            }
            $orderData = BondOrder::findOrfail($request->order);
            $bond = DB::table('bonds')->where('id', $orderData->bond_id)->first();
            $client = DB::table('users')->where('id', $orderData->client_id)->first();

            if(empty($client->bot_cds_number) && empty($request->bot_cds_number) && $orderData->market=='primary'){
                return response()->json(['status' => false, 'message' => 'Please enter BOT CDS number of the customer'], 500);
            }

            if($orderData->market=='primary'){

                $validated = \Illuminate\Support\Facades\Validator::make($request->all(), [
                    "auction_date" => ['required', 'date'],
                    "auction_number" => ['required'],
                    "bot_cds_number" => ['required'],
                ]);

                if ($validated->fails()) {
                    return response()->json(['status' => false, 'message' => $validated->messages()->first()], 500);
                }

                $tempUser = User::find($client->id);
                $tempUser->bot_cds_number = $request->bot_cds_number;
                $tempUser->bot_security_number = $request->bot_security_number;
                $tempUser->save();
            }
            $category = DB::table('customer_categories')->where('id', $client->category_id)->first();
            $scheme = DB::table('bond_schemes')->find($category->bond_scheme);
            $order = new BondExecution();
//            $order->trade_date = Helper::formattedDate($request->date);
            $order->trade_date = $systemDate['timely'];
            $order->settlement_date = Helper::settlementDateBond($systemDate['today']);
//            $order->settlement_date = Helper::settlementDateBond($order->trade_date);
            $order->face_value = str_ireplace(',', '', $request->executed);
            $order->other_charges = floatval(str_ireplace(',', '', $request->other_charges));
            $order->slip_no = str_ireplace(',', ' ', $request->slip_no);
            $order->balance = $orderData->balance - $request->executed;
            $order->price = str_ireplace(',', '', $request->price);
            $order->amount = floatval(str_ireplace(',', '', $request->price)) * floatval(str_ireplace(',', '', $request->executed)) / 100;
            $order->status = 'pending';
            $order->executed = str_ireplace(',', '', $request->executed);
            $order->auction_date = $request->auction_date;
            $order->auction_number = $request->auction_number;
            $order->bot_cds_number = !empty($request->bot_cds_number) ? $request->bot_cds_number : $client->bot_cds_number;
            $order->bot_security_number = !empty($request->bot_security_number) ? $request->bot_security_number : $client->bot_security_number;
            $order->type = $orderData->type;
            $order->ytm = $request->ytm;
            $order->market = $orderData->market;
            $order->category = $orderData->category;
            $order->bond_id = $bond->id;
            $order->bond_type = $bond->type;
            $order->order_id = $orderData->id;
            $order->client_id = $client->id;
            $order->financial_year_id = Helper::business()->financial_year;
            BondsHelper::setCommissions($order);
            $order->brokerage_rate = $scheme->flat_rate;
            if ($orderData->has_custodian == 'yes') {
                $order->has_custodian = $orderData->has_custodian;
                $order->custodian_id = $orderData->custodian_id;
            }

            $totalTraded = DB::table('bond_executions')
                ->where('status', '!=', 'cancelled')
                ->where('bond_id', $orderData->id)->sum('payout');

            $totalUnTraded = $orderData->payout - $totalTraded - $order->other_charges;

            if ($totalUnTraded < $order->payout) {
                return $this->onErrorResponse('Untraded order balance is below '.number_format($order->payout).'. Untraded balance is '.number_format($totalUnTraded));
            }

            $order->save();

            if($order->market == "primary" ) {
                BondsHelper::BondPrimaryExecutionUID($order);
            }else{
                BondsHelper::BondExecutionUID($order);
            }

            $orderData->save();

            BondsHelper::updateOrderStatus($orderData->id);
            DB::commit();

            return $this->onSuccessResponse('Order confirmation executed successfully.');

        } catch (Throwable $ex) {
            DB::rollBack();
            report($ex);
            return $this->onErrorResponse('System could not process your request, Contact Support #BOND-ORDER-PROCESS-FAIL-0300');
        }
    }

}
