<?php

namespace Modules\Orders\Http\Controllers;

use App\Exports\Orders\OrdersExportAll;
use App\Exports\Orders\OrdersExportAllMonths;
use App\Exports\Orders\OrdersExportMonthly;
use App\Helpers\Clients\Profile;
use App\Helpers\EquitiesHelper;
use App\Jobs\DSE\BuyShareJob;
use App\Jobs\Statements\UpdateCustomerStatements;
use App\Mail\Orders\OverdraftOrderPlaced;
use App\Helpers\Helper;
use App\Helpers\Pdfs\OrderPdf;
use App\Http\Controllers\Controller;
use App\Imports\ReconcileImport;
use App\Models\Accounting\Transaction;
use App\Models\DealingSheet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use JetBrains\PhpStorm\NoReturn;
use Maatwebsite\Excel\Facades\Excel;
use Modules\DSE\DTOs\BuyShareDTO;
use Modules\Orders\Entities\Order;
use Modules\Orders\Entities\OrderDocument;
use Modules\Orders\Entities\OrderList;
use Modules\Orders\Entities\OrderDetails;
use Modules\Orders\Entities\OrderReconcile;
use Modules\Wallet\Entities\EquitiesOnHold;
use Modules\Wallet\Entities\Wallet;
use Modules\Whatsapp\Helpers\WhatsappMessagesHelper;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;
use function Livewire\on;

class OrdersController extends Controller
{

    #[NoReturn] public function downloadPdf(Request $request)
    {
        $order = Order::find($request->id);
        $pdf = new OrderPdf($order);
        $fullPath = $pdf->create($order);
        $this->viewFile($fullPath,$pdf->file);
    }

    public function export_monthly(Request $request): BinaryFileResponse
    {

        $start = new Carbon('first day of '.ucwords($request->month).' '.date("Y"));
        $end = new Carbon('last day of '.ucwords($request->month).' '.date("Y"));
        return (new OrdersExportMonthly($request->month))->from($start->toDateString())->end($end->toDateString())->download($request->month.'-orders-report.xlsx');
    }

    public function export_all_orders(): BinaryFileResponse
    {
        return (new OrdersExportAll)->download('exports.xlsx');
    }

    public function export_all_months(): BinaryFileResponse
    {
        return (new OrdersExportAllMonths)->download('exports.xlsx');
    }

    public function export_csv(Request $request): BinaryFileResponse
    {
        if(empty($request->status)){
            return (new OrdersExport)->from($request->from)->end($request->end)->download('freight_requests.xlsx');
        }else{
            return (new OrdersExport)->status($request->status)->from($request->from)->end($request->end)->download('freight_requests.xlsx');
        }

    }

    public function search_order(Request $request): JsonResponse
    {
        try{
            $order = Order::select("orders.*",DB::raw("users.name"))
                ->where('orders.id', 'LIKE', "%{$request->q}%")
                ->orWhere('orders.uid', 'LIKE', "%{$request->q}%")
                ->orWhere('orders.status', 'LIKE', "%{$request->q}%")
                ->orWhere("users.name", "LIKE", "%{$request->q}%")
                ->leftJoin('users', 'users.id', '=', 'orders.client_id')
                ->get();
            return  response()->json($order);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function order_stats(): JsonResponse
    {
        return response()->json([
           'all_orders' => Order::count(),
           'new_orders' => Order::where("status","new")->count(),
           'pending' => Order::where("status","pending")->count(),
           'dealing_sheets' => Order::where("status","!=","cancelled")->where("status","!=","pending")->where("status","!=","complete")->count(),
           'on_progress' => Order::where("status","on progress")->count(),
           'cancel_request' => Order::whereNotNull("request_cancel")->count(),
           'unmatched' => OrderReconcile::where("dse_price",">",0)->where("bbo_present","NO")->count()
        ]);
    }


    public function delete_document(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();

            $data = OrderDocument::find($request->id);

            if(!empty($data)){
                $data->delete();
            }

            DB::commit();

            return response()->json(Order::find($request->file_id));

        }catch (Throwable $ex){
            DB::rollBack();
            report($ex);
            return response()->json(['status' => false, 'message' => "creation failed ".$ex->getMessage()], 500);
        }
    }

    public function approve(Request $request): JsonResponse
    {
        $order = Order::findOrFail($request->id);

        if($order->status == "cancelled"){
            return $this->onErrorResponse("You can not Approve cancelled order.");
        }

        if(strtolower($order->status) == "approved"){
            return $this->onErrorResponse("Order was already approved.");
        }

        $order->status = "approved";
        try {
            DB::beginTransaction();

            if($order->updated_by == $request->header("id")){
//                return response()->json(['status' =>  false,'message' => 'Maker checker process failed'],400);
            }
            $order->updated_by = $request->header("id");
            $order->save();

            DB::commit();
//            $profile = DB::table("profiles")->where("user_id",$order->client_id)->first();
//            if(!empty($profile)){
//                $security = DB::table("securities")->find($order->security_id);
//                $dseOrder = new BuyShareDTO();
//                $dseOrder->shares = $order->volume;
//                $dseOrder->nidaNumber = $profile->identity;
//                $dseOrder->price = $order->price;
//                $dseOrder->securityReference = $security->dse_reference;
//                $dseOrder->orderId = $order->id;
//                BuyShareJob::dispatch($dseOrder);
//            }

            return $this->onSuccessResponse("Order approved successfully.");
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse("System could not process your request, Contact Support #ORDER-PROCESS-FAIL-0001");
        }

    }

    public function close_open(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $order = Order::find($request->id);
            if(strtolower($order->status) == "new"){
                return $this->onErrorResponse("You can not close Order placed by customer");
            }

            $executed = DB::table("dealing_sheets")->where("order_id",$order->id)->sum("executed");
            $order->volume = $executed;
            $order->status = "complete";
            $order->closed = "yes";
            $order->save();
            EquitiesOnHold::where("equity_id",$order->id)->delete();
            EquitiesHelper::updateWalletAfterClose($order);
            EquitiesHelper::brokerageCommissions($order);

            DB::commit();
            return response()->json($order);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse("System could not process your request, Contact Support #ORDER-PROCESS-FAIL-0009");
        }
    }

    public function update_status(Request $request): JsonResponse
    {
        $order = Order::find($request->id);

        try {
            DB::beginTransaction();

            if(strtolower($order->status) == "approved"){
                return $this->onErrorResponse("You can not change status of Approved order");
            }

            if(strtolower($request->status) ==  "pending"){
                $user = DB::table("users")->find($order->client_id);
                if(!empty($user->mobile)) {
                    WhatsappMessagesHelper::sendOrderReviewed($user->mobile);
                }
            }

            $order->status = $request->status;
            $order->updated_by = $request->header("id");
            $order->save();
            DB::commit();
            return response()->json($order);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse("System could not process your request, Contact Support #ORDER-PROCESS-FAIL-0003");
        }

    }

    public function cancel_order(Request $request): JsonResponse
    {
        $order = Order::find($request->id);

        try {
            DB::beginTransaction();

            if(strtolower($order->status) == "approved"){
                return $this->onErrorResponse("You can not cancel Approved order");
            }

            if(strtolower($order->status) == "new"){
                return $this->onErrorResponse("You can not cancel Order placed by customer");
            }

            if(empty($request->message)){
                return $this->onErrorResponse("You must provide cancellation message.");
            }

            $order->closed = "yes";
            $order->status = "cancelled";
            $order->cancellation_reason = $request->message;
            $order->updated_by = $request->header("id");
            $order->save();

            EquitiesOnHold::where("equity_id",$order->id)->delete();
            EquitiesHelper::updateWalletAfterCancel($order);
            DB::commit();
            return $this->onSuccessResponse("Order cancelled successfully");
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse("System could not process your request, Contact Support #ORDER-PROCESS-FAIL-0003");
        }

    }

    public function update_order(Request $request): JsonResponse
    {
        $order = Order::findOrFail($request->id);
        $new_notice = $order->officer_notice." ".$request->officer_notice??"";
        $order->officer_notice = $new_notice;
        try {
            DB::beginTransaction();
            $order->save();
            DB::commit();

            $order = Order::find($request->id);
            return response()->json($order);

        }catch (Throwable $ex){
            DB::rollBack();
            report($ex);
            return $this->onErrorResponse("System could not process your request, Contact Support #ORDER-PROCESS-FAIL-0002");
        }

    }

    public function new_orders(Request $request): JsonResponse
    {
        try{
            $order = OrderList::whereNull("request_cancel")->where("status","new")->paginate(getenv("PERPAGE"));
            return  response()->json($order);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function all_orders_status(Request $request): JsonResponse
    {
        try{
            $status = $request->status;
            $query = DB::table("orders")
                ->whereNull("orders.deleted_at")
                ->select("orders.*")
                ->where("orders.status", $status)
                ->selectRaw("users.name as client_name")
                ->selectRaw("securities.name as security_name")
                ->latest()
                ->leftJoin("users","orders.client_id","=","users.id")
                ->leftJoin("securities","orders.security_id","=","securities.id");
            $orders = $query->paginate(getenv("PERPAGE"));
            return  response()->json($orders);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function orders_request_cancel(Request $request): JsonResponse
    {
        try{
            $order = OrderList::whereNotNull("request_cancel")->paginate(getenv("PERPAGE"));
            return  response()->json($order,200);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function filter(Request $request): JsonResponse
    {
        try{

            $per_page = !empty($request->per_page) ? $request->per_page: getenv("PERPAGE");
            $query = OrderDetails::whereNull("orders.deleted_at")
                ->select("orders.*")
                ->selectRaw("users.name as client_name")
                ->selectRaw("securities.name as security_name")
                ->latest()
                ->leftJoin("users","orders.client_id","=","users.id")
                ->leftJoin("securities","orders.security_id","=","securities.id");

            if(!empty($request->client)){
                $query = $query->where("client_id",$request->client);
            }

            if(!empty($request->value)){
                $query = $query->where("id",$request->value);
            }

            if(!empty($request->from) && !empty($request->end)){
                $query = $query->whereDate("date",">=",date("Y-m-d", strtotime($request->from)))
                    ->whereDate("date","<=",date("Y-m-d", strtotime($request->end)));
            }

            $orders = $query->paginate($per_page);
            return  response()->json($orders);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function orders_reconcile_import(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();

            Excel::import(new ReconcileImport, request()->file('file'));

            DB::commit();
            $per_page = !empty($request->per_page) ? $request->per_page: getenv("PERPAGE");
            $order = OrderReconcile::latest("trade_date")->paginate($per_page);
            return response()->json($order);

        }catch (Throwable $throwable){
            DB::rollBack();
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function reconcile_filter_month(Request $request): JsonResponse
    {
        try{
            $per_page = !empty($request->per_page) ? $request->per_page: getenv("PERPAGE");
            if(strtolower($request->month) == "all"){
                $order = OrderReconcile::latest("trade_date")->paginate($per_page);
            }else{
                $start = new Carbon('first day of  '.ucfirst($request->month).' '.date("Y"));
                $end = new Carbon('last day of '.ucfirst($request->month).' '.date("Y"));
                $order = OrderReconcile::whereDate('trade_date',">=" ,$start->toDateString())
                    ->whereDate('trade_date',"<=" ,$end->toDateString())
                    ->latest("trade_date")->paginate($per_page);
            }

            return  response()->json($order);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function unmatched_filter_month(Request $request): JsonResponse
    {
        try{
            $per_page = !empty($request->per_page) ? $request->per_page: getenv("PERPAGE");
            if(strtolower($request->month) == "all"){
                $order = OrderReconcile::where("dse_price",">",0)->where("bbo_present","NO")->latest("trade_date")->paginate($per_page);
            }else{
                $start = new Carbon('first day of  '.ucfirst($request->month).' '.date("Y"));
                $end = new Carbon('last day of '.ucfirst($request->month).' '.date("Y"));
                $order = OrderReconcile::where("dse_price",">",0)->where("bbo_present","NO")->whereDate('trade_date',">=" ,$start->toDateString())
                    ->whereDate('trade_date',"<=" ,$end->toDateString())
                    ->latest("trade_date")->paginate($per_page);
            }

            return  response()->json($order);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function orders_reconcile(Request $request): JsonResponse
    {
        try{
            $per_page = !empty($request->per_page) ? $request->per_page: getenv("PERPAGE");
            $order = OrderReconcile::latest("trade_date")->paginate($per_page);
            return  response()->json($order);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function orders_unmatched(Request $request): JsonResponse
    {
        try{
            $per_page = !empty($request->per_page) ? $request->per_page: getenv("PERPAGE");
            $order = OrderReconcile::where("dse_price",">",0)->where("bbo_present","NO")->latest("trade_date")->paginate($per_page);
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
            $query = DB::table("orders")
                ->whereNull("orders.deleted_at")
                ->select("orders.*")
                ->selectRaw("users.name as client_name")
                ->selectRaw("securities.name as security_name")
                ->latest()
            ->leftJoin("users","orders.client_id","=","users.id")
            ->leftJoin("securities","orders.security_id","=","securities.id");
            if(!empty($request->client)){
                $query = $query->where("client_id",$request->client);
            }
            if(!empty($request->value)){
                $query = $query->where("id",$request->value);
            }

            $order = $query->paginate($per_page);
            return  response()->json($order);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }
    public function order_overdraft(Request $request): JsonResponse
    {
        try{
            $per_page = !empty($request->per_page) ? $request->per_page: getenv("PERPAGE");
            $query = Order::where("status","overdraft")->latest("created_at");
            if(!empty($request->client)){
                $query = $query->where("client_id",$request->client);
            }
            if(!empty($request->value)){
                $query = $query->where("id",$request->value);
            }

            $order = $query->paginate($per_page);
            return  response()->json($order);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function approveSelectedOverdraft(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            if(!empty($request->items)){
                foreach ($request->items as $item){
                    $order = Order::find($item);
                    $order->overdraft_message =  $request->overdraft_message;
                    $order->status = "pending";
                    $order->save();
                    EquitiesHelper::updateWallet($order);
                }
            }

            DB::commit();
            return $this->onSuccessResponse("Successfully.");
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function rejectSelectedOverdraft(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            if(!empty($request->items)){
                foreach ($request->items as $item){
                    $order = Order::find($item);
                    $order->status = "rejected";
                    $order->save();
                    EquitiesOnHold::where("equity_id",$order->id)->delete();
                    EquitiesHelper::updateWalletAfterReject($order);
                }
            }
            DB::commit();
            return $this->onSuccessResponse("Successfully.");
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function approve_overdraft(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $order = Order::find($request->id);
            $order->overdraft_message =  $request->overdraft_message;
            $order->status = "pending";
            $order->save();
            EquitiesHelper::updateWallet($order);
            DB::commit();

            return $this->onSuccessResponse("Successfully.");
        }catch (Throwable $throwable){
            report($throwable);
            return response()->json(['status' =>  false,'message' => 'Failed to update profile'],500);
        }

    }

    public function reject_overdraft(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $order = Order::find($request->id);
            $order->overdraft_message =  $request->overdraft_message;
            $order->status = "rejected";
            $order->save();
            EquitiesHelper::updateWalletAfterReject($order);
            DB::commit();

            return $this->onSuccessResponse("Successfully.");
        }catch (Throwable $throwable){
            report($throwable);
            return response()->json(['status' =>  false,'message' => 'Failed to update profile'],500);
        }

    }

    public function update_overdraft_order(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            $order =Order::find($request->id);
            $order->overdraft_message =  $request->overdraft_message;
            $order->save();
            $per_page = !empty($request->per_page) ? $request->per_page: getenv("PERPAGE");
            $query = Order::where("status","overdraft")->latest("created_at");
            $orders = $query->paginate($per_page);
            EquitiesHelper::updateWallet($order);
            DB::commit();

            return response()->json($orders);

        }catch (Throwable $ex){
            DB::rollBack();
            report($ex);
            return $this->onErrorResponse($ex->getMessage());
        }

    }

    public function orders_data(Request $request): JsonResponse
    {
        try{
            $response['clients'] = DB::table("users")
//                ->limit(10)
                ->select([
                    "users.name",
                    "users.email",
                    "users.id",
                ])
                ->selectRaw("customer_categories.name as category_name")
                ->where("users.status","active")
                ->where("users.onboard_status","finished")
                ->whereIn("users.type",['minor','individual','corporate','joint'])
                ->whereNotNull("dse_account")
                ->whereNotNull("category_id")
                ->orderBy("users.name")
                ->leftJoin("customer_categories","users.category_id","=","customer_categories.id")
                ->get();
            $response['companies'] = DB::table("securities")->where("type","security")->get();
            return  response()->json($response);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function order(Request $request): JsonResponse
    {
        try{

            $order = OrderDetails::findOrFail($request->id);
            $user = auth()->user();
//            if($user->email == "abdul@itrust.co.tz") {
//                $order = OrderDetails::findOrFail($request->id);
//                $order->volume = 5064;
//                $order->price = 4260;
//                $order->balance = $order->volume;
//                $order->amount = $order->price * $order->volume;
//                $order->save();
//                EquitiesHelper::setCommissions($order);
//                $order->save();
//            }
            return  response()->json($order);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse("System could not process your request, Contact Support #ORDER-QUERY-FAIL-0000");
        }
    }

    public function store(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            $client = new Profile($request->customer);

            if(strtolower($client->type == "corporate")){
                if (strlen(trim(str_ireplace("-","",$client->corporate_tin))) < 9) {
                    return $this->onErrorResponse("Customer has no TIN or TIN is invalid ");
                }
            }else {
                if (strlen(trim(str_ireplace("-","",$client->tin))) < 9) {
                    return $this->onErrorResponse("Customer has no TIN or TIN is invalid ");
                }
            }

            $wallet = $client->wallet_available;

            if(floatval(str_ireplace(",","",$request->shares)) <= 0){
                return $this->onErrorResponse("You can not trade 0 shares");
            }

            if(strtolower($request->type) == "sell"){

                $security = $request->company;

                $buy =  DB::table("dealing_sheets")
                    ->whereNull("deleted_at")
                    ->where("security_id",$security)
                    ->where("type","buy")
                    ->where("status","approved")
                    ->where("client_id",$request->customer)
                    ->sum('executed');

                $equity_cancelled = DB::table('dealing_sheets')
                    ->where("security_id",$security)
                    ->where('status','cancelled')
                    ->where('type', 'sell')
                    ->where('client_id', $request->customer)
                    ->sum('executed');

                $sell =  DB::table("orders")
                    ->whereNull("deleted_at")
                    ->where("status","!=","cancelled")
                    ->where("security_id",$security)
                    ->where("type","sell")
                    ->where("client_id",$request->customer)
                    ->sum('volume');

                $shares =  $buy - $sell + $equity_cancelled;

                if($shares < str_ireplace(",","",$request->shares)){
                    return $this->onErrorResponse("Customer do not have enough shares to sell. Available Shares are ".$shares);
                }

            }


            $order = new Order();
            $systemDate = Helper::systemDateTime();

            if($request->has_custodian == "yes" && $client->custodian_approved == "yes" && empty($request->custodian_id)){
                return $this->onErrorResponse("Please specify custodian");
            }

            $category = DB::table("customer_categories")->where("id",$client->category_id)->first();
            $scheme = DB::table("equity_schemes")->where("id",$category->equity_scheme)->first();

            $order->client_id = $request->customer;
            $order->type = $request->type;
            $order->date = $systemDate['timely'];
            $order->price = floatval(str_ireplace(",","",$request->price));
            $order->volume = floatval(str_ireplace(",","",$request->shares));
            $order->balance = $order->volume;
            $order->amount = $order->price * $order->volume;
            $order->security_id = $request->company;
            $order->mode = $request->price == 0 ? "limit" :'market';
            $order->financial_year_id = Helper::business()->financial_year;
            $order->created_by = $request->header("id");
            $order->updated_by = $request->header("id");

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

            EquitiesHelper::setCommissions($order);

            $order->status = "pending";
            if($request->has_custodian == "yes" && $client->custodian_approved == "yes" && !empty($request->custodian_id)){
                $order->has_custodian = "yes";
                $order->custodian_id = $request->custodian_id;
            }else{
                if(strtolower($request->type) == "buy" && round($wallet) < round(floatval(str_ireplace(",","",$order->payout)))){
                    if(empty($request->overdraft_message)){
                        return $this->onErrorResponse("You must provide overdraft message.");
                    }
                    $order->status = "overdraft";
                    $order->overdraft_message = $request->overdraft_message;
                }
            }


            $order->save();
            Helper::orderUID($order);

            if(!$order->status == "overdraft"){
                EquitiesHelper::updateWallet($order);
            }

            DB::commit();

            if($order->status == "overdraft"){
                $mailable = new OverdraftOrderPlaced($request->overdraft_message);
                \Mail::to(Helper::mailingListOverdraft())->queue($mailable);
            }

            return $this->onSuccessResponse("Order created successfully.");

        }catch (Throwable $ex){
            DB::rollBack();
            report($ex);
            return $this->onErrorResponse("System could not process your request, Contact Support #ORDER-PROCESS-FAIL-0000");
        }
    }

    public function createSell(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            $client = new Profile($request->customer);

            if(strtolower($client->type == "corporate")){
                if (strlen(trim(str_ireplace("-","",$client->corporate_tin))) < 9) {
                    return $this->onErrorResponse("Customer has no TIN or TIN is invalid ");
                }
            }else {
                if (strlen(trim(str_ireplace("-","",$client->tin))) < 9) {
                    return $this->onErrorResponse("Customer has no TIN or TIN is invalid ");
                }
            }

            if(floatval(str_ireplace(",","",$request->shares)) <= 0){
                return $this->onErrorResponse("You can not trade 0 shares");
            }

            $security = $request->company;

            $buy =  DB::table("dealing_sheets")
                ->whereNull("deleted_at")
                ->where("security_id",$security)
                ->where("type","buy")
                ->where("status","approved")
                ->where("client_id",$request->customer)
                ->sum('executed');

            $equity_cancelled = DB::table('dealing_sheets')
                ->where("security_id",$security)
                ->where('status','cancelled')
                ->where('type', 'sell')
                ->where('client_id', $request->customer)
                ->sum('executed');

            $sell =  DB::table("orders")
                ->whereNull("deleted_at")
                ->where("status","!=","cancelled")
                ->where("security_id",$security)
                ->where("type","sell")
                ->where("client_id",$request->customer)
                ->sum('volume');

            $shares =  $buy - $sell + $equity_cancelled;

            if($shares < str_ireplace(",","",$request->shares)){
                return $this->onErrorResponse("Customer do not have enough shares to sell. Available Shares are ".$shares);
            }

            $order = new Order();
            $systemDate = Helper::systemDateTime();

            if($request->has_custodian == "yes" && $client->custodian_approved == "yes" && empty($request->custodian_id)){
                return $this->onErrorResponse("Please specify custodian");
            }

            $category = DB::table("customer_categories")->where("id",$client->category_id)->first();
            $scheme = DB::table("equity_schemes")->where("id",$category->equity_scheme)->first();

            $order->client_id = $request->customer;
            $order->type = "sell";
            $order->date = $systemDate['timely'];
            $order->price = floatval(str_ireplace(",","",$request->price));
            $order->volume = floatval(str_ireplace(",","",$request->shares));
            $order->balance = $order->volume;
            $order->amount = $order->price * $order->volume;
            $order->security_id = $request->company;
            $order->mode = $request->price == 0 ? "limit" :'market';
            $order->financial_year_id = Helper::business()->financial_year;
            $order->created_by = $request->header("id");
            $order->updated_by = $request->header("id");

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

            EquitiesHelper::setCommissions($order);

            $order->status = "pending";
            if($request->has_custodian == "yes" && $client->custodian_approved == "yes" && !empty($request->custodian_id)){
                $order->has_custodian = "yes";
                $order->custodian_id = $request->custodian_id;
            }


            $order->save();
            Helper::orderUID($order);


            DB::commit();


            return $this->onSuccessResponse("Order created successfully.");

        }catch (Throwable $ex){
            DB::rollBack();
            report($ex);
            return $this->onErrorResponse("System could not process your request, Contact Support #ORDER-PROCESS-FAIL-0000");
        }
    }

    public function createBuy(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            $validation  =  Validator::make($request->all(),[
                'company' => 'required',
                'price' => 'required',
                'shares' => 'required',
                'customer' => 'required',
                'has_custodian' => 'required'
            ]);

            if($validation->invalid()){
                return response()->json(['status' => false, 'message' => $validation->messages()->first()], 400);
            }
            $client = new Profile($request->customer);

            if(strtolower($client->type == "corporate")){
                if (strlen(trim(str_ireplace("-","",$client->corporate_tin))) < 9) {
                    return $this->onErrorResponse("Customer has no TIN or TIN is invalid ");
                }
            }else {
                if (strlen(trim(str_ireplace("-","",$client->tin))) < 9) {
                    return $this->onErrorResponse("Customer has no TIN or TIN is invalid ");
                }
            }

            $wallet = $client->wallet_available;

            if(floatval(str_ireplace(",","",$request->shares)) <= 0){
                return $this->onErrorResponse("You can not trade 0 shares");
            }


            $order = new Order();
            $systemDate = Helper::systemDateTime();

            if($request->has_custodian == "yes" && $client->custodian_approved == "yes" && empty($request->custodian_id)){
                return $this->onErrorResponse("Please specify custodian");
            }

            $category = DB::table("customer_categories")->where("id",$client->category_id)->first();
            $scheme = DB::table("equity_schemes")->where("id",$category->equity_scheme)->first();

            $order->client_id = $request->customer;
            $order->type = "buy";
            $order->date = $systemDate['timely'];
            $order->price = floatval(str_ireplace(",","",$request->price));
            $order->volume = floatval(str_ireplace(",","",$request->shares));
            $order->balance = $order->volume;
            $order->amount = $order->price * $order->volume;
            $order->security_id = $request->company;
            $order->mode = $request->price == 0 ? "limit" :'market';
            $order->financial_year_id = Helper::business()->financial_year;
            $order->created_by = $request->header("id");
            $order->updated_by = $request->header("id");

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

            EquitiesHelper::setCommissions($order);

            $order->status = "pending";
            if($request->has_custodian == "yes" && $client->custodian_approved == "yes" && !empty($request->custodian_id)){
                $order->has_custodian = "yes";
                $order->custodian_id = $request->custodian_id;
            }else{
                if(strtolower($request->type) == "buy" && round($wallet) < round(floatval(str_ireplace(",","",$order->payout)))){
                    if(empty($request->overdraft_message)){
                        return $this->onErrorResponse("You must provide overdraft message.");
                    }
                    $order->status = "overdraft";
                    $order->overdraft_message = $request->overdraft_message;
                }
            }


            $order->save();
            Helper::orderUID($order);

            if(!$order->status == "overdraft"){
                EquitiesHelper::updateWallet($order);
            }

            DB::commit();

            if($order->status == "overdraft"){
                $mailable = new OverdraftOrderPlaced($request->overdraft_message);
                \Mail::to(Helper::mailingListOverdraft())->queue($mailable);
            }

            return $this->onSuccessResponse("Order created successfully.");

        }catch (Throwable $ex){
            DB::rollBack();
            report($ex);
            return $this->onErrorResponse("System could not process your request, Contact Support #ORDER-PROCESS-FAIL-0000");
        }
    }

}
