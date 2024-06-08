<?php

namespace Modules\DealingSheets\Http\Controllers;

use App\Exports\Orders\DealingSheetsExport;
use App\Helpers\Clients\Profile;
use App\Helpers\EquitiesHelper;
use App\Helpers\Helper;
use App\Helpers\Pdfs\ContractNotePdf;
use App\Http\Controllers\Controller;
use App\Models\Accounting\Transaction;
use App\Models\DealingSheet;
use App\Models\DealingSheetFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\NoReturn;
use Modules\Orders\Entities\Order;
use Modules\Orders\Entities\OrderList;
use Modules\Whatsapp\Helpers\WhatsappMessagesHelper;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class DealingSheetsController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        try{
            $order = DealingSheet::select("dealing_sheets.*",DB::raw("users.name"))
                ->where('dealing_sheets.id', 'LIKE', "%{$request->q}%")
                ->orWhere('dealing_sheets.status', 'LIKE', "%{$request->q}%")
                ->orWhere("users.name", "LIKE", "%{$request->q}%")
                ->leftJoin('users', 'users.id', '=', 'dealing_sheets.client_id')
                ->get();
            return  response()->json($order);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function delete_document(Request $request){

        try {
            DB::beginTransaction();

            $data = DealingSheetFile::find($request->id);
            if(!empty($data)){
                $data->delete();
            }
            DB::commit();

            return response()->json(DealingSheet::find($request->file_id), 200);

        }catch (Throwable $ex){
            DB::rollBack();
            report($ex);
            return response()->json(['status' => false, 'message' => "creation failed ".$ex->getMessage()], 500);
        }
    }

    public function create_document(Request $request){

        try {
            DB::beginTransaction();

            if($request->hasfile('files'))
            {
                foreach($request->file('files') as $key => $file)
                {
                    $data = new DealingSheetFile();
                    $data->name = $request->file_names[$key];
                    $data->dealing_sheet_id = $request->file_id;
                    $path = $file->store('public/business/profiles');
                    $data->file_id = str_ireplace("public/business/profiles/","",$path);
                    $data->extension = $file->extension();
                    $data->path = str_ireplace("public/","",$path);
                    $data->save();
                }

            }

            DB::commit();


            return response()->json(DealingSheet::find($request->file_id), 200);

        }catch (Throwable $ex){
            DB::rollBack();
            report($ex);
            return response()->json(['status' => false, 'message' => "creation failed ".$ex->getMessage()], 500);
        }
    }

    public function update_status(Request $request): JsonResponse
    {
       try {
            DB::beginTransaction();

           $order = DealingSheet::findOrFail($request->id);
           $orderData = Order::find($order->order_id);

           if(!empty($order)) {
               if ($order->status == "cancelled") {
                   return $this->onErrorResponse("Confirmation was cancelled.");
               }

               if ($order->status == "approved") {
                   return $this->onErrorResponse("Confirmation was approved.");
               }
           }

            if($request->status == 'approved') {
                if ($order->updated_by == $request->header("id")) {
//                    return response()->json(["message" => "Marker Checker Failed"], 500);
                }
            }


           Transaction::where("reference",$order->slip_no)->delete();

           if($order->order_number != null) {

               if (strtolower($request->status) == 'pending') {
                   EquitiesHelper::clearFees($order);
               }

               if (strtolower($request->status) == 'cancelled') {
                   EquitiesHelper::clearFees($order);
               }

               if (strtolower($request->status) == 'approved') {
                   EquitiesHelper::setCommissions($order);
               }
           }

            $order->status = $request->status;
            $order->updated_by = $request->header("id");
            $order->save();

           if($order->order_number != null) {
               if (strtolower($request->status) == 'approved') {
                   if (strtolower($order->type == 'buy')) {
                       EquitiesHelper::_process_order_buy($order);
                       $user = DB::table("users")->find($order->client_id);
                   } else {
                       EquitiesHelper::_process_order_sell($order);
                       $user = DB::table("users")->find($order->client_id);
                   }
                   if(!empty($user->mobile)) {
                       WhatsappMessagesHelper::sendTradeConfirmationApprovedObject($user->mobile);
                   }
               }
           }

         if(!empty($orderData)) {
             EquitiesHelper::updateOrderStatus($orderData->id);
         }

         Helper::newEventStream();
            DB::commit();
         return response()->json($order);
        }catch (Throwable $throwable){
            DB::rollback();
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function update_status_fix(Request $request): JsonResponse
    {
       try {
            DB::beginTransaction();

           $order = DealingSheet::findOrFail("018f4e21-8388-736e-8c02-4deb6a32c80b");

            $order->status = "pending";
            $order->save();

//           if($order->order_number != null) {
//               if (strtolower($request->status) == 'approved') {
//                   if (strtolower($order->type == 'buy')) {
//                       EquitiesHelper::_process_order_buy($order);
//                       $user = DB::table("users")->find($order->client_id);
//                   } else {
//                       EquitiesHelper::_process_order_sell($order);
//                       $user = DB::table("users")->find($order->client_id);
//                   }
//                   if(!empty($user->mobile)) {
//                       WhatsappMessagesHelper::sendTradeConfirmationApprovedObject($user->mobile);
//                   }
//               }
//           }


            DB::commit();
         return response()->json($order);
        }catch (Throwable $throwable){
            DB::rollback();
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function downloadPdf(Request $request)
    {
        try {
            $dealingSheet = DealingSheet::find($request->id);
            $order = Order::find($dealingSheet->order_id);
            $pdf = new ContractNotePdf();
            $filename = $pdf->create($order, $dealingSheet);
            header("Access-Control-Allow-Origin: *");
            header("strict-transport-security: max-age=600");
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filename));
            readfile($filename);
            exit;
        }catch (\Throwable $throwable){
            Log::info($throwable);
            report($throwable);
            exit();
        }
    }

    public function send_contract_note(Request $request): JsonResponse
    {
        $dealingSheet = DealingSheet::find($request->id);
        $order = Order::find($dealingSheet->order_id);
        $dealingSheet->email_sent = "yes";
        $dealingSheet->save();
        $this->send_order_executed($order, $dealingSheet);
        return response()->json($dealingSheet);
    }

    public function sheet_update(Request $request): JsonResponse
    {

        try {
            return $this->onErrorResponse("Confirmation update is currently unavailable.");
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse("System could not process your request, Contact Support #ORDER-PROCESS-FAIL-0200");
        }

    }

    public function all_sheets_status(Request $request): JsonResponse
    {
        try{
            $order =DealingSheet::latest("trade_date")->where("status",$request->status)->paginate(getenv("PERPAGE"));
            return  response()->json($order,200);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function all_sheets_customer(Request $request): JsonResponse
    {
        try{
            $per_page = !empty($request->per_page) ? $request->per_page: getenv("PERPAGE");

            $order = DB::table("dealing_sheets")
                ->latest()
                ->select(["dealing_sheets.*"])
                ->selectRaw("dealing_sheets.order_id as order_number")
                ->where("client_id",$request->client)
                ->paginate($per_page);
            return  response()->json($order);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function all_trades(Request $request): JsonResponse
    {
        try{
            $order = DealingSheet::latest("trade_date")->status("pending")
                ->paginate(300);
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
            $order = DealingSheet::latest("trade_date")->paginate($per_page);
            return  response()->json($order);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function all_orders(Request $request): JsonResponse
    {
        try{
            $per_page = !empty($request->per_page) ? $request->per_page: getenv("PERPAGE");
            $order = OrderList::latest("date")
                ->whereNull("deleted_at")
                ->where("status","!=","cancelled")
                ->where("status","!=","pending")
                ->where("status","!=","rejected")
                ->whereNull("closed")
                ->where("status","!=","new")
                ->where("status","!=","complete")
                ->where("status","!=","overdraft")
                ->paginate($per_page);
            return  response()->json($order);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function export_csv(Request $request): BinaryFileResponse
    {
        if(empty($request->status)){
            return (new DealingSheetsExport)->from($request->from)->end($request->end)->download('exports.xlsx');
        }else{
            return (new DealingSheetsExport)->status($request->status)->from($request->from)->end($request->end)->download('exports.xlsx');
        }

    }

    public function sheet(Request $request): JsonResponse
    {
        try{
//            $order = DealingSheet::find($request->id);
//            EquitiesHelper::setCommissions($order);
//            $order->save();
            $order = DealingSheet::find($request->id);
//            $systemDate = Helper::systemDateTime();
//            $order->trade_date = $systemDate['timely'];
//            $order->settlement_date = Helper::settlementDateEquity($systemDate['today']);
//            $order->save();
//            $user = auth()->user();
//            if($user->email == "abdul@itrust.co.tz") {
//                $order->executed = 10193;
//                $order->price = 190;
//                $order->balance = $order->volume;
//                $order->amount = $order->price * $order->executed;
//                $order->save();
//                EquitiesHelper::setCommissions($order);
//                $order->save();
//            }
            return  response()->json($order,200);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function sheet_reference(Request $request): JsonResponse
    {
        try{
            $order = DealingSheet::where("slip_no",$request->id)->first();
            return  response()->json($order,200);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {


        try {

            DB::beginTransaction();
            $shares = str_ireplace(",","",floatval(str_ireplace(",","",$request->executed)));
            $reference = Str::squish(str_ireplace(","," ",$request->slip_no));
            $referenceCheck  = DB::table("dealing_sheets")
                ->whereNull("deleted_at")
                ->where("slip_no",$reference)
                ->first();

            if(!empty($referenceCheck)){
                return response()->json(['status' => false, 'message' => "Found A Record with same Trade Confirmation"], 500);
            }

            $orderData  = DB::table("orders")->select(['balance','id','client_id','has_custodian','custodian_id','volume','type','security_id','payout'])->find($request->order);

            $order = new DealingSheet();

            $client = new Profile($orderData->client_id);

            if(strtolower($order->type) == "sell"){
                $balance =  $orderData->balance - $shares;

                if($balance < $shares){
                    return $this->onErrorResponse("You can not process confirmation of more than order balance. Order balance is ".$balance);
                }
            }

            if($orderData->has_custodian == "yes"){
                $order->has_custodian = $orderData->has_custodian;
                $order->custodian_id = $orderData->custodian_id;
            }

            $category = DB::table("customer_categories")->where("id",$client->category_id)->first();
            $scheme = DB::table("equity_schemes")->where("id",$category->equity_scheme)->first();
            $systemDate = Helper::systemDateTime();
            $order->settlement_date = Helper::settlementDateEquity($systemDate['today']);
            $order->trade_date = $systemDate['timely'];
            $order->volume = $orderData->volume;
            $order->other_charges = floatval(str_ireplace(",","",$request->other_charges));
            $order->slip_no = str_ireplace(",","",$request->slip_no);
            $order->balance = $orderData->balance - $request->executed;
            $order->price = floatval(str_ireplace(",","",$request->price));
            $order->amount = floatval(str_ireplace(",","",$request->price)) * floatval(str_ireplace(",","",$request->executed));
            $order->status = "pending";
            $order->executed = floatval(str_ireplace(",","",$request->executed));
            $order->mode = $request->price == 0 ? "limit" :'market';
            $order->type = $orderData->type;
            $order->order_id = $orderData->id;
            $order->client_id = $orderData->client_id;
            $order->security_id = $orderData->security_id;
            $order->financial_year_id = Helper::business()->financial_year;

            if($request->use_custom_commission == "yes") {
                $order->use_custom_commission = $request->use_custom_commission;
                $order->rate_step_one = $request->step_one_commission;
                $order->rate_step_two = $request->step_two_commission;
                $order->rate_step_three = $request->step_three_commission;
                $order->use_flat = $request->use_flat;
            }else{
                $rates = EquitiesHelper::commissionRates($scheme);
                $order->rate_step_one = $rates["one"];
                $order->rate_step_two = $rates["two"];
                $order->rate_step_three = $rates["three"];
            }

            if($request->use_flat == "yes"){
                $order->brokerage_rate = $request->flat_commission;
            }else {
                $order->brokerage_rate = $scheme->flat_rate;
            }

            $order->created_by = $request->header("id");
            $order->updated_by = $request->header("id");
            EquitiesHelper::setCommissions($order);

            $totalTraded = DB::table("dealing_sheets")
                ->where("status","!=","cancelled")
                ->where("order_id",$orderData->id)->sum("payout");
            $totalUnTraded  = $orderData->payout - $totalTraded - $order->other_charges;

            if(round($totalUnTraded) < round($order->payout)){
//                return $this->onErrorResponse("Untraded order balance is below ".number_format($order->payout).'. Untraded balance is '.number_format($totalUnTraded));
            }

            $order->save();
            Helper::dealingSheetUID($order);

            EquitiesHelper::updateOrderStatus($orderData->id);

            DB::commit();

            return $this->onSuccessResponse("Order confirmation created successfully.");

        }catch (Throwable $ex){
            DB::rollBack();
            report($ex);
            return response()->json(['status' => false, 'message' => "registration failed failed ".$ex->getMessage()], 500);
        }
    }

}
