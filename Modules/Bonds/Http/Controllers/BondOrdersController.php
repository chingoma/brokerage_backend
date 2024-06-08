<?php

namespace Modules\Bonds\Http\Controllers;

use App\Data\StatusCheck;
use App\Helpers\BondsHelper;
use App\Helpers\Clients\Profile;
use App\Helpers\EmailsHelper;
use App\Helpers\Helper;
use App\Helpers\MoneyHelper;
use App\Http\Controllers\Controller;
use App\Models\Accounting\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Pulse\Users;
use Modules\Bonds\Entities\Bond;
use Modules\Bonds\Entities\BondExecution;
use Modules\Bonds\Entities\BondOrder;
use Modules\Bonds\Entities\BondOrderList;
use Modules\CRM\Entities\CustomerCategory;
use Modules\Orders\Entities\OrderDocument;
use Modules\Schemes\Entities\BondScheme;
use Modules\Wallet\Entities\BondsOnHold;
use Modules\Wallet\Entities\Wallet;
use Modules\Whatsapp\Helpers\WhatsappMessagesHelper;
use Throwable;

class BondOrdersController extends Controller
{

    public function delete_document(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();

            $data = OrderDocument::find($request->id);

            if(!empty($data)){
                $data->delete();
            }

            DB::commit();

            return response()->json(BondOrder::find($request->file_id));

        }catch (Throwable $ex){
            DB::rollBack();
            report($ex);
            return response()->json(['status' => false, 'message' => "creation failed ".$ex->getMessage()], 400);
        }
    }

    public function approve(Request $request): JsonResponse
    {
        $order = BondOrder::findOrFail($request->id);
        $order->status = "approved";
        try {
            DB::beginTransaction();

            if($order->updated_by == $request->header("id")){
//                return response()->json(['status' =>  false,'message' => 'Make-Check process Failed'],400);
            }
            $order->updated_by = $request->header("id");
            $order->save();
            DB::commit();
            return response()->json($order);
        }catch (Throwable $throwable){
            report($throwable);
            return response()->json(['status' =>  false,'message' => 'Failed to update profile'],400);
        }

    }

    public function cancel(Request $request): JsonResponse
    {
        $order = BondOrder::findOrFail($request->id);
        $order->status = "cancelled";
        try {
            DB::beginTransaction();

            if($order->updated_by == $request->header("id")){
//                return response()->json(['status' =>  false,'message' => 'Make-Check process Failed'],400);
            }
            $order->updated_by = $request->header("id");
            $order->save();
            BondsHelper::updateWalletAfterCancel($order);
            DB::commit();
            return response()->json($order);
        }catch (Throwable $throwable){
            report($throwable);
            return response()->json(['status' =>  false,'message' => 'Failed to update profile'],400);
        }

    }

    public function review(Request $request): JsonResponse
    {
        $order = (new BondOrder)->findOrfail($request->id);

        try {
            DB::beginTransaction();
            $order->status = "pending";
            $order->updated_by = $request->header("id");
            $order->save();
            DB::commit();
            return response()->json($order);
        }catch (ModelNotFoundException|Throwable $throwable){
            report($throwable);
            return response()->json(['status' =>  false,'message' => 'Failed to review order'],400);
        }

    }

    public function update_status(Request $request): JsonResponse
    {
        $order = BondOrder::find($request->id);
        $order->status = $request->status;
        if(strtolower($request->status) ==  "cancelled"){
            $order->status = $request->status;
            $order->request_cancel = null;
            $order->closed = "no";
        }
        try {
            DB::beginTransaction();
            if(strtolower($request->status) !=  "approved"){

                BondExecution::where("bond_id",$order->id)->delete();
                Transaction::where("order_id",$order->id)->delete();
            }


            if(strtolower($request->status) ==  "approved") {
                if ($order->updated_by  == $request->header("id")) {
                    return response()->json(["message" => "Marker-Checker process Failed"], 400);
                }
            }

            $order->updated_by = $request->header("id");
            $order->save();
            DB::commit();
            return response()->json($order);
        }catch (Throwable $throwable){
            report($throwable);
            return response()->json(['status' =>  false,'message' => 'Failed to update profile'],400);
        }

    }

    public function update_order(Request $request): JsonResponse
    {
        $order = BondOrder::findOrFail($request->id);
        $order->officer_notice = $request->officer_notice??"";

        try {
            DB::beginTransaction();
            $order->save();
            DB::commit();

            $order = BondOrder::find($request->id);
            return response()->json($order);

        }catch (Throwable $ex){
            DB::rollBack();
            report($ex);
            return $this->onErrorResponse($ex->getMessage());
        }

    }

    public function update_overdraft_order(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            $order = BondOrder::findOrFail($request->id);
            $order->overdraft_message =  $request->overdraft_message;
            $order->save();
            $per_page = !empty($request->per_page) ? $request->per_page: getenv("PERPAGE");
            $query = BondOrder::where("status","overdraft")->latest("created_at");
            $orders = $query->paginate($per_page);
            DB::commit();

            return response()->json($orders);

        }catch (Throwable $ex){
            DB::rollBack();
            report($ex);
            return $this->onErrorResponse($ex->getMessage());
        }

    }

    public function approve_overdraft(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $order = BondOrder::findOrFail($request->id);
            $order->overdraft_message =  $request->overdraft_message;
            $order->status = "pending";
            $order->save();
            $per_page = !empty($request->per_page) ? $request->per_page: getenv("PERPAGE");
            $query = BondOrder::where("status","overdraft")->latest("created_at");
            $orders = $query->paginate($per_page);
            DB::commit();
            BondsHelper::updateWallet($order);

            return response()->json($orders);
        }catch (Throwable $throwable){
            report($throwable);
            return response()->json(['status' =>  false,'message' => 'Failed to approve overdraft contact system system administrator with code #AP'],400);
        }

    }

    public function approveSelectedOverdraft(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            if(!empty($request->items)){
                foreach ($request->items as $item){
                    $order = BondOrder::find($item);
                    $order->overdraft_message =  $request->overdraft_message;
                    $order->status = "pending";
                    $order->save();
                    BondsHelper::updateWallet($order);
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
                    $order = BondOrder::find($item);
                    $order->status = "rejected";
                    $order->save();
                    BondsOnHold::where("bond_id",$order->id)->delete();
                    BondsHelper::updateWalletAfterReject($order);
                }
            }
            DB::commit();
            return $this->onSuccessResponse("Successfully.");
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function reject_overdraft(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $order = BondOrder::findOrFail($request->id);
            $order->overdraft_message =  $request->overdraft_message;
            $order->status = "rejected";
            $order->save();
            DB::commit();
            BondsOnHold::where("bond_id",$order->id)->delete();
            BondsHelper::updateWalletAfterReject($order);

            return $this->onSuccessResponse("Order rejected successfully.");
        }catch (Throwable $throwable){
            report($throwable);
            return response()->json(['status' =>  false,'message' => 'Failed to approve overdraft contact system system administrator with code #AP'],400);
        }

    }

    public function filter(Request $request): JsonResponse
    {
        try{
            $per_page = !empty($request->per_page) ? $request->per_page: getenv("PERPAGE");
            $query = BondOrder::latest("date");

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

    public function orders(Request $request): JsonResponse
    {
        try{
            $per_page = !empty($request->per_page) ? $request->per_page: getenv("PERPAGE");
            $query = BondOrderList::latest("created_at");
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

    public function orders_overdraft(Request $request): JsonResponse
    {
        try{
            $per_page = !empty($request->per_page) ? $request->per_page: getenv("PERPAGE");
            $query = BondOrder::where("status","overdraft")->latest("created_at");
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

    public function settings(Request $request): JsonResponse
    {
        try{
//            $response['clients'] = DB::table("users")->select(['id','name','email'])->whereIn('type',Helper::customerTypes())->latest()->get();
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
            $response['bonds'] = Bond::get();
            return  response()->json($response,200);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function customerBuyData(Request $request): JsonResponse
    {
        try{
            $wallet = Wallet::where("user_id",$request->id)->first();
            $data = new \stdClass();
            $data->balance = $wallet->available_balance;
            return  response()->json($data);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function order(Request $request): JsonResponse
    {
        try{
            $order = BondOrder::findOrFail($request->id);
            return  response()->json($order);
        }catch (Throwable $throwable){
            report($throwable);
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        $profile = new Profile($request->customer);

        if(floatval(str_ireplace(",","",$request->face_value)) <= 0){
            return response()->json(['status' => false, 'message' => "You can not trade 0 Face Value"], 400);
        }

        if(strtolower($request->type) == "sell"){

            $buy =  DB::table("bond_executions")
                ->whereNull("deleted_at")
                ->where("bond_id",$request->bond)
                ->where("type","buy")
                ->where("status","approved")
                ->where("client_id",$request->customer)
                ->sum('face_value');

            $sell =  DB::table("bond_orders")
                ->whereNull("deleted_at")
                ->where("status","!=","cancelled")
                ->where("bond_id",$request->bond)
                ->where("type","sell")
                ->where("client_id",$request->customer)
                ->sum('face_value');

            $balance =  $buy - $sell;

            if($balance < str_ireplace(",","",$request->face_value)){
                return $this->onErrorResponse("Customer do not have enough Face Value to sell. Available Face Value is ".$balance);
            }

        }

        $client = DB::table("users")->where("id",$request->customer)->first();
        $category = DB::table("customer_categories")->where("id",$client->category_id)->first();
        $scheme = DB::table("bond_schemes")->find($category->bond_scheme);
        $bond = (new Bond)->findOrFail($request->bond);
        $systemDate = Helper::systemDateTime();
        $order = new BondOrder();

        if($request->has_custodian == "yes" && $client->custodian_approved == "yes" && !empty($request->custodian_id)){
            $order->has_custodian = "yes";
            $order->custodian_id = $request->custodian_id;
            $order->status = "pending";
        }else{
            if(strtolower($request->type) == "buy" && round($profile->wallet_available) < round(floatval(str_ireplace(",","",$order->payout)))){
                if(empty($request->overdraft_message)){
                    return response()->json(['status' => false, 'message' => "You must provide overdraft message"], 400);
                }
                $order->status = "overdraft";
                $order->overdraft_message = $request->overdraft_message;
            }else{
                $order->status = "pending";
            }
        }

        if($request->has_custodian == "yes" && $client->custodian_approved == "yes" && empty($request->custodian_id)){
            return response()->json(['status' => false, 'message' => "Please specify custodian"], 400);
        }
        $order->status = "pending";
        $order->date = $systemDate['timely'];
        $order->market = $request->market;
        $order->category = $bond->category;
        $order->client_id = $request->customer;
        $order->coupons = $request->coupons;
        $order->type = $request->type;
        $order->price = floatval(str_ireplace(",","",$request->price));
        $order->face_value = floatval(str_ireplace(",","",$request->face_value));
        $order->amount = ($order->price * $order->face_value)/100;
        BondsHelper::setCommissions($order);
        $order->bond_id = $bond->id;
        $order->financial_year_id = Helper::business()->financial_year;
        $order->updated_by = $request->header("id");
        $order->brokerage_rate = $scheme->flat_rate;
        try {
            DB::beginTransaction();
            $order->save();
            if($order->market == "primary") {
                BondsHelper::BondPrimaryOrderUID($order);
            }else{
                BondsHelper::BondOrderUID($order);
            }
            $per_page = !empty($request->per_page) ? $request->per_page: getenv("PERPAGE");
            $orders = BondOrder::latest("date")->paginate($per_page);

            if($request->has_custodian == "yes" && $client->custodian_approved == "yes" && !empty($request->custodian_id)){

            }else{
                if(strtolower($request->type) == "buy" && round($profile->wallet_balance) < round(floatval(str_ireplace(",","",$order->payout)))){
                    if(empty($request->overdraft_message)){
                        return response()->json(['status' => false, 'message' => "You must provide overdraft message"], 400);
                    }
                    EmailsHelper::sendOverdraftEmail("bond");
                }else{
                    BondsHelper::updateWallet($order);
                }
            }

            DB::commit();

            return response()->json($orders);

        }catch (Throwable $ex){
            DB::rollBack();
            report($ex);
            return response()->json(['status' => false, 'message' => "registration failed failed ".$ex->getMessage()], 400);
        }
    }

    public function sell_order(Request $request): JsonResponse
    {

        $validation  =  Validator::make($request->all(),[
            'bond' => 'required',
            'price' => 'required',
            'face_value' => 'required',
            'customer' => 'required',
            'coupons' => 'required',
            'market' => 'required',
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

        $orderCheckStatus = self::_buyOrderChecks($request);
        if(!$orderCheckStatus->status){
            return response()->json($orderCheckStatus, 400);
        }

        $buy =  DB::table("bond_executions")
            ->whereNull("deleted_at")
            ->where("bond_id",$request->bond)
            ->where("type","buy")
            ->where("status","approved")
            ->where("client_id",$request->customer)
            ->sum('face_value');

        $sell =  DB::table("bond_orders")
            ->whereNull("deleted_at")
            ->where("status","!=","cancelled")
            ->where("bond_id",$request->bond)
            ->where("type","sell")
            ->where("client_id",$request->customer)
            ->sum('face_value');

        $balance =  $buy - $sell;

        if($balance < str_ireplace(",","",$request->face_value)){
            return $this->onErrorResponse("Customer do not have enough Face Value to sell. Available Face Value is ".$balance);
        }

        $client = User::find($request->customer);

        $category = DB::table("customer_categories")->where("id",$client->category_id)->first();
        $scheme = DB::table("bond_schemes")->find($category->bond_scheme);
        $bond = Bond::findOrFail($request->bond);
        $order = new BondOrder();
        if(strtolower($request->has_custodian) == "yes"){
            if(!self::_buyOrderCheckCustodian(client:$client,request: $request )){
                return response()->json(['status' => false, 'message' => "Make sure you have provided Custodian and Investor is Activated to use Custodian"], 400);
            }
            $order->has_custodian = "yes";
            $order->custodian_id = $request->custodian_id;
        }
        $order->type = "sell";
        $order->status = "pending";

        $systemDate = Helper::systemDateTime($request->date);
        $order->date = $systemDate['timely'];
        $order->market = $request->market;
        $order->category = $bond->category;
        $order->client_id = $request->customer;
        $order->coupons = $request->coupons;
        $order->price = floatval(str_ireplace(",","",$request->price));
        $order->face_value = floatval(str_ireplace(",","",$request->face_value));
        $order->amount = ($order->price * $order->face_value)/100;
        BondsHelper::setCommissions($order);
        $order->bond_id = $bond->id;
        $order->financial_year_id = Helper::business()->financial_year;
        $order->updated_by = $request->header("id");
        $order->brokerage_rate = $scheme->flat_rate;
        try {

            DB::beginTransaction();
            $order->save();
            BondsHelper::BondOrderUID($order);
            DB::commit();

            return $this->onSuccessResponse("Order created successfully.");

        }catch (Throwable $ex){
            DB::rollBack();
            report($ex);
            return response()->json(['status' => false, 'message' => "registration failed failed ".$ex->getMessage()], 400);
        }
    }

    public function buy_order(Request $request): JsonResponse
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

            $orderCheckStatus = self::_buyOrderChecks($request);
            if(!$orderCheckStatus->status){
                return response()->json($orderCheckStatus, 400);
            }
            $wallet = Wallet::select(['available_balance'])->where("user_id",$request->customer)->first();
            $client = User::select(['id','category_id','custodian_approved'])->findOrFail($request->customer);
            $category = CustomerCategory::select(['bond_scheme'])->findOrFail($client->category_id);
            $scheme = BondScheme::select(['id', 'name', 'mode', 'broker_fee', 'flat_rate', 'step_one', 'step_two', 'dse_fee', 'csdr_fee', 'cmsa_fee'])->findOrFail($category->bond_scheme);
            $bond = Bond::findOrFail($request->bond);
            $systemDate = Helper::systemDateTime();

            $order = new BondOrder();
            $order->status = "pending";

            $order->date = $systemDate['timely'];
            $order->market = $request->market;
            $order->category = $bond->category;
            $order->client_id = $request->customer;
            $order->coupons = $request->coupons;
            $order->type = "buy";
            $order->price = MoneyHelper::sanitize($request->price);
            $order->face_value = MoneyHelper::sanitize($request->face_value);
            $order->amount = ($order->price * $order->face_value)/100;
            BondsHelper::setCommissions($order);
            $order->bond_id = $bond->id;
            $order->financial_year_id = Helper::business()->financial_year;
            $order->updated_by = $request->header("id");
            $order->brokerage_rate = $scheme->flat_rate;

            if(strtolower($request->has_custodian) == "yes"){
                if(!self::_buyOrderCheckCustodian(client:$client,request: $request )){
                    return response()->json(['status' => false, 'message' => "Make sure you have provided Custodian and Investor is Activated to use Custodian"], 400);
                }
                $order->has_custodian = "yes";
                $order->custodian_id = $request->custodian_id;
            }else{
                if(round($wallet->available_balance) < round(MoneyHelper::sanitize($order->payout))){
                    if(!self::_buyOrderCheckOverdraft(order: $order,client:$client,request: $request )){
                        return response()->json(['status' => false, 'message' => "Make sure you have provided overdraft message"], 400);
                    }
                    $order->status = "overdraft";
                    $order->overdraft_message = $request->overdraft_message;
                }
            }

            $order->save();
            BondsHelper::BondOrderUID($order);

            if(!round($wallet->available_balance) < round(MoneyHelper::sanitize($order->payout))){
               BondsHelper::updateWallet($order);
            }

            DB::commit();

            if(round($wallet->available_balance) < round(MoneyHelper::sanitize($order->payout))){
                EmailsHelper::sendOverdraftEmail("bond");
            }

            return $this->onSuccessResponse("Order created successfully");

        }catch (Throwable $ex){
            DB::rollBack();
            report($ex);
            return response()->json(['status' => false, 'message' => "Order failed ".$ex->getMessage()], 400);
        }
    }

    private static function _buyOrderChecks(Request $request): StatusCheck
    {
        $check = new StatusCheck();
        $check->status = true;
        if(floatval(MoneyHelper::sanitize($request->face_value)) <= 0){
            $check->message = "You can not trade 0 Face Value";
            $check->status = false;
        }

        return $check;
    }

    private static function _buyOrderCheckCustodian( User $client, Request $request): bool
    {
        if($request->has_custodian == "yes" && $client->custodian_approved == "yes" && !empty($request->custodian_id)){
            return true;
        }else{
            return false;
        }
    }

    private static function _buyOrderCheckOverdraft( BondOrder $order, User $client, Request $request): bool
    {
        if(empty($request->overdraft_message)){
            return false;
        }
        return true;
    }

}
