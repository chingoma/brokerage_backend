<?php

namespace Modules\CRM\Http\Controllers;

use App\Exports\Customers\CustomersExport;
use App\Exports\Customers\CustomersExportAll;
use App\Exports\Customers\CustomersExportCreditors;
use App\Exports\Customers\CustomersExportDebtors;
use App\Exports\Customers\CustomersWallet;
use App\Helpers\Clients\CustomerStatistics;
use App\Helpers\Helper;
use App\Helpers\Pdfs\StatementPdf;
use App\Http\Controllers\Controller;
use App\Imports\UsersImport;
use App\Mail\Clients\WelcomeEmailMailable;
use App\Models\Accounting\FinancialYear;
use App\Models\Accounting\Transaction;
use App\Models\Bank;
use App\Models\Corporate;
use App\Models\DealingSheet;
use App\Models\JointProfile;
use App\Models\Nationality;
use App\Models\NextOfKin;
use App\Models\Profile;
use App\Models\ProfileFile;
use App\Models\Sector;
use App\Models\User;
use App\Rules\ValidationHelper;
use Faker\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use JetBrains\PhpStorm\NoReturn;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use Modules\Bonds\Entities\Bond;
use Modules\Bonds\Entities\BondExecution;
use Modules\CRM\Entities\Customer;
use Modules\CRM\Entities\CustomerCategory;
use Modules\CRM\Entities\CustomerCustodian;
use Modules\Custodians\Entities\Custodian;
use Modules\DSE\DTOs\DSEPayloadDTO;
use Modules\DSE\DTOs\InvestorAccountDetailsDTO;
use Modules\DSE\Helpers\DSEHelper;
use Modules\Orders\Entities\Order;
use Nnjeim\World\Models\Country;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class CustomersController extends Controller
{
    public function syncDSE(Request $request)
    {
        try {
            $profile = Profile::where('user_id', $request->id)->firstOrFail();
            $user = User::findOrFail($request->id);
            $dse = new \stdClass();
            $dse->birthDistrict = $profile->district;
            $dse->birthWard = $profile->ward;
            $dse->country = 'TZ';
            $dse->dob = $profile->dob;
            $dse->email = $user->email;
            $dse->firstName = $profile->firstname;
            $dse->gender = $profile->gender;
            $dse->lastName = $profile->lastname;
            $dse->middleName = $profile->middlename;
            $dse->nationality = $profile->nationality;
            $dse->nidaNumber = $profile->identity;
            $dse->phoneNumber = $user->mobile;
            $dse->photo = '';
            $dse->residentRegion = $profile->region;
            $dse->physicalAddress = $profile->address;
            $dse->placeOfBirth = $profile->place_birth;
            $dse->region = $profile->region;
            $dse->requestId = $profile->user_id;
            $dse->residentDistrict = $profile->district;
            $dse->residentHouseNo = '';
            $dse->residentPostCode = '';
            $dse->residentVillage = '';
            $dseAccount = InvestorAccountDetailsDTO::fromJson(json_encode($dse));
            $status = DSEHelper::createAccount($dseAccount);
            if ($status === true || $status->code == 9050) {
                return $this->onSuccessResponse('Request sent successfully, wait for callback');
            } else {
                return $this->onErrorResponse('DSE Response: '.$status->message);
            }

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    #[NoReturn]
    public function downloadStatement(Request $request)
    {
        $client = User::find($request->id);
        $transactions = Transaction::where('status', 'approved')
            ->groupBy('reference')
            ->orderBy('transaction_date', 'asc')
//            ->whereDate("created_at",">=",date("Y-m-d",strtotime($request->from)))
//            ->whereDate("created_at","<=",date("Y-m-d",strtotime($request->end)))
            ->where('client_id', $client->id)
            ->latest()
            ->orderBy('transaction_date')
            ->get();
        $pdf = new StatementPdf(true);
        $filename = $pdf->create($transactions, $client);
        $this->downloadFile($filename, 'pdf');
    }

    #[NoReturn]
    public function generateStatement(Request $request)
    {
        $client = User::find($request->id);
        $transactions = Transaction::where('status', 'approved')
            ->groupBy('reference')
            ->orderBy('transaction_date', 'asc')
//            ->whereDate("created_at",">=",date("Y-m-d",strtotime($request->from)))
//            ->whereDate("created_at","<=",date("Y-m-d",strtotime($request->end)))
            ->where('client_id', $client->id)
            ->latest()
            ->orderBy('transaction_date')
            ->get();
        $pdf = new StatementPdf(true);
        $filename = $pdf->create($transactions, $client);
        //z  $this->downloadFile($filename,'pdf');
    }

    #[NoReturn]
    public function downloadIdentity(Request $request)
    {
        $ext = pathinfo($request->filename, PATHINFO_EXTENSION);
        $filename = Helper::storageArea().'identities/'.$request->filename;
        $this->downloadFile($filename, $ext);
    }

    #[NoReturn]
    public function downloadPassport(Request $request)
    {
        $ext = pathinfo($request->filename, PATHINFO_EXTENSION);
        $filename = Helper::storageArea().'passports/'.$request->filename;
        $this->downloadFile($filename, $ext);
    }

    #[NoReturn]
    public function downloadFiles(Request $request)
    {
        $ext = pathinfo($request->filename, PATHINFO_EXTENSION);
        $filename = Helper::storageArea().'files/'.$request->filename;
        $this->downloadFile($filename, $ext);
    }

    public function statement(Request $request): JsonResponse
    {

        $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
        //        $id = $request->id;
        //        $statement = DB::table("statements")
        //            ->where("status","approved")
        //            ->where("client_id",$id)
        //            ->orderBy("date")
        //            ->paginate($per_page);
        //
        //        return  response()->json($statement);

        $client = User::find($request->id);

        $transactions = [];
        if (! empty($client)) {
            $transactions = Transaction::where('status', 'approved')
                ->groupBy('reference')
                ->orderBy('transaction_date', 'asc')
                ->where('client_id', $client->id)
                ->get();
        }

        $pdf = new StatementPdf(false);
        $pdf->create($transactions, $client);
        $statements = $pdf->statement;
        if (! empty($statements)) {
            $statement = [];
            foreach ($statements as $key => $transaction) {
                $statement[$key]['date'] = $transaction['date'];
                $statement[$key]['type'] = $transaction['type'];
                $statement[$key]['category'] = $transaction['category'];
                $statement[$key]['reference'] = $transaction['reference'];
                $statement[$key]['particulars'] = $transaction['particulars'];
                $statement[$key]['quantity'] = $transaction['quantity'];
                $statement[$key]['price'] = $transaction['price'];
                $statement[$key]['debit'] = $transaction['debit'];
                $statement[$key]['credit'] = $transaction['credit'];
                $statement[$key]['balance'] = $transaction['balance'];
            }

            return response()->json($statement);
        }

        return response()->json($statements);
    }

    public function custodians(Request $request): JsonResponse
    {
        $client = User::find($request->id);

        $custodians = [];
        if (! empty($client)) {
            $custodians = DB::table('customer_custodians')
                ->select(['customer_custodians.id', 'customer_custodians.status', 'custodians.name'])
                ->where('customer_custodians.user_id', $client->id)
                ->join('custodians', 'custodians.id', '=', 'customer_custodians.custodian_id')
                ->latest('customer_custodians.created_at')
                ->get();
        }

        return response()->json($custodians);
    }

    public function customers_stats(): JsonResponse
    {
        return response()->json((new CustomerStatistics)->stats());
    }

    public function change_password(Request $request): JsonResponse
    {
        $user = User::find($request->id);
        $user->password = Hash::make($request->password);
        $user->email = $request->email;
        $profile = Profile::where('id', $request->id)->first();

        try {
            DB::beginTransaction();
            $user->save();
            DB::commit();

            return response()->json($profile);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function change_custodian(Request $request): JsonResponse
    {
        $user = User::findOrFail($request->id);
        $user->has_custodian = $request->status;
        $user->custodian_approved = 'no';
        $user->updated_by = $request->header('id');
        $profile = new \App\Helpers\Clients\Profile($request->id);

        try {
            DB::beginTransaction();
            $user->save();
            DB::commit();

            return response()->json($profile);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function approve_custodian(Request $request): JsonResponse
    {

        DB::beginTransaction();
        $user = User::findOrFail($request->id);

        if ($user->updated_by == $request->header('id')) {
            //  DB::rollBack();
            return response()->json(['message' => 'Maker Checker Failed'], 500);
        }

        $user->custodian_approved = 'yes';
        //    $user->updated_by = $request->header("id");
        $profile = new \App\Helpers\Clients\Profile($request->id);

        try {
            $user->save();
            DB::commit();

            return response()->json($profile);
        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function reset_failed_attempts(Request $request): JsonResponse
    {

        DB::beginTransaction();
        $user = User::findOrFail($request->id);

        $user->failed_attempts = 0;
        $profile = new \App\Helpers\Clients\Profile($request->id);

        try {
            $user->save();
            DB::commit();

            return response()->json($profile);
        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function change_custodian_status(Request $request): JsonResponse
    {
        $user = User::findOrFail($request->id);
        $customerCustodian = CustomerCustodian::findOrFail($request->customer_custodian_id);
        $customerCustodian->status = $request->status;
        try {
            DB::beginTransaction();
            $customerCustodian->save();
            $user->save();
            DB::commit();
            $profile = new \App\Helpers\Clients\Profile($request->id);

            return response()->json($profile);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function change_email(Request $request): JsonResponse
    {
        $user = User::find($request->id);
        $user->email = $request->email;
        $profile = Profile::where('id', $request->id)->first();
        $profile->contact_email = $request->email;

        try {
            DB::beginTransaction();
            $user->save();
            $profile->save();
            DB::commit();
            $profile = new \App\Helpers\Clients\Profile($request->id);

            return response()->json($profile);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function send_reset_password_email(Request $request): JsonResponse
    {

        try {

            $user = User::find($request->id);

            if (empty($user->email)) {
                return response()->json(['message' => 'We could not find account with provided information'], 500);
            }

            Password::sendResetLink(
                ['email' => $user->email]
            );

            return response()->json(['Account activation email was sent successfully']);

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function export_csv(Request $request): BinaryFileResponse
    {
        if (empty($request->status)) {
            return (new CustomersExport)->from($request->from)->end($request->end)->download('exports.xlsx');
        } else {
            return (new CustomersExport)->status($request->status)->from($request->from)->end($request->end)->download('exports.xlsx');
        }

    }

    public function export_creditors(Request $request): BinaryFileResponse
    {
        return (new CustomersExportCreditors)->download('exports.xlsx');

    }

    public function export_debtors(Request $request): BinaryFileResponse
    {
        ini_set('memory_limit', '4095M');
        return (new CustomersExportDebtors)->download('exports.xlsx');

    }

    public function export_csv_all(Request $request)
    {
        return (new CustomersExportAll)->download('exports.xlsx');
    }

    public function export_wallet(Request $request)
    {
        return (new CustomersWallet)->download('exports.xlsx');
    }

    public function search_customer(Request $request): JsonResponse
    {
        try {
            $users = DB::table('users')
                ->select(['name', 'email', 'id', 'type', 'dse_account', 'flex_acc_no', 'created_at'])
                ->where('name', 'LIKE', "%{$request->q}%")
                ->orWhere('id', 'LIKE', "%{$request->q}%")
                ->orWhere('flex_acc_no', 'LIKE', "%{$request->q}%")
                ->orWhere('email', 'LIKE', "%{$request->q}%")
                ->orWhere('dse_account', 'LIKE', "%{$request->q}%")
                ->orderBy('created_at', 'desc')->get();

            return response()->json([$request->q, $users]);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function migrate_shares(Request $request): JsonResponse
    {

        $validator = Validator::make($request->all(),[
            "type" => ["required"],
            "volume" => ["required"],
            "id" => ["required"],
            "security" => ["required"]
        ]);
        if($validator->fails()){
            return response()->json(['status' => false, 'message' => $validator->messages()->first()], 500);
        }

        $date = Helper::systemDateTime();
        $order = new DealingSheet();
        $order->volume = $request->volume;
        $order->slip_no = '';
        $order->trade_date = $date['timely'];
        $order->balance = 0;
        $order->price = 0;
        $order->closed = 'yes';
        $order->amount = 0;
        $order->status = 'pending';
        $order->executed = $request->volume;
        $order->type = $request->type;
        $order->client_id = $request->id;
        $order->security_id = $request->security;
        $order->financial_year_id = FinancialYear::first()->id;

        try {
            DB::beginTransaction();
            $order->save();
            DB::commit();

            return response()->json(new \App\Helpers\Clients\Profile($request->id));

        } catch (Throwable $ex) {
            DB::rollBack();
            report($ex);

            return response()->json(['status' => false, 'message' => 'registration failed failed '.$ex->getMessage()], 500);
        }
    }

    public function migrate_bond(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(),[
            "type" => ["required"],
            "face_value" => ["required"],
            "id" => ["required"],
            "bond" => ["required"]
        ]);
        if($validator->fails()){
            return response()->json(['status' => false, 'message' => $validator->messages()->first()], 500);
        }

        $date = Helper::systemDateTime();
        $order = new BondExecution();
        $order->face_value = $request->face_value;
        $order->slip_no = '';
        $order->trade_date = $date['timely'];
        $order->balance = 0;
        $order->price = 0;
        $order->closed = 'yes';
        $order->amount = 0;
        $order->status = 'pending';
        $order->executed = $request->face_value;
        $order->type = $request->type;
        $order->client_id = $request->id;
        $order->bond_id = $request->bond;
        $bondData = Bond::findOrFail($request->bond);
        $order->market = $bondData->market;
        $order->category = $bondData->category;
        $order->bond_type = $bondData->type;

        $order->financial_year_id = FinancialYear::first()->id;

        try {
            DB::beginTransaction();
            $order->save();
            DB::commit();

            return response()->json(new \App\Helpers\Clients\Profile($request->id));

        } catch (Throwable $ex) {
            DB::rollBack();
            report($ex);

            return response()->json(['status' => false, 'message' => 'registration failed failed '.$ex->getMessage()], 500);
        }
    }

    public function payees(): JsonResponse
    {
        try {
            $users = DB::table("users")
                ->whereIn("type",["minor","individual","joint","corporate"])
                ->get();

            return response()->json($users);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function add_payee(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $user = new User();

            $user->type = 'payee';
            $user->name = $request->name;
            $user->password = Hash::make('1234567890');
            $user->email = $request->email;
            $user->save();
            Helper::customerUID($user);

            $profile = new Profile();
            $profile->name = $request->name;
            $profile->address = $request->address;
            $profile->user_id = $user->id;
            $payees = User::payees()->orderBy('uid', 'DESC')->get();

            DB::commit();

            return response()->json($payees);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function add_custodian(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $user = User::findOrFail($request->id);
            $custodianStatus = CustomerCustodian::where('user_id', $user->id)->where('custodian_id', $request->custodian_id)->first();
            if (empty($custodianStatus)) {
                $custodian = new CustomerCustodian();
                $custodian->user_id = $user->id;
                $custodian->custodian_id = $request->custodian_id;
                $custodian->save();
                $user->has_custodian = 'yes';
                $user->custodian_approved = 'no';
            }

            $user->updated_by == $request->header('id');
            $user->custodian_id = $request->custodian;
            $user->save();
            DB::commit();
            $custodians = DB::table('customer_custodians')
                ->select(['customer_custodians.id', 'customer_custodians.status', 'custodians.name'])
                ->where('customer_custodians.user_id', $user->id)
                ->join('custodians', 'custodians.id', '=', 'customer_custodians.custodian_id')
                ->latest('customer_custodians.created_at')
                ->get();

            return response()->json($custodians);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function data_customers(): JsonResponse
    {
        try {
            $data['countries'] = DB::table('countries')->orderBy("name")->get();
            $data['categories'] = DB::table("customer_categories")->select(['id','name'])->get();
            $data['companies'] = DB::table("securities")->select(['id','name','fullname'])->get();
            $data['bonds'] = Bond::get();
            $data['banks'] = Bank::orderBy('name', 'asc')->get();
            $data['nationalities'] = Nationality::orderBy("name")->get();
            $data['sectors'] = Sector::orderBy('name')->get();
            $data['kins'] = ['Parent', 'Guardian', 'Sibling', 'Spouse', 'Child'];
            $data['custodians'] = Custodian::orderBy("name")->get();
            $data['managers'] = DB::table("users")->whereIn("type",['admin'])->get();
            $data['source_of_income'] = ["Dividends / Interest","Salary / Savings","Loans","Business Profits","Pension","Rental Income / Property Sale"];
            $data['income_frequency'] = ["Below TZS 100M","Above TZS 100M"];
            return response()->json($data);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function delete_document(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();

            $data = ProfileFile::find($request->id);
            if (! empty($data)) {
                $data->delete();
            }
            DB::commit();

            return response()->json(Customer::find($request->file_id), 200);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function create_document(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();

            if ($request->hasfile('files')) {
                foreach ($request->file('files') as $key => $file) {
                    $data = new ProfileFile();
                    $data->name = $request->file_names[$key];
                    $data->profile_id = $request->file_id;
                    $path = $file->store('public/business/profiles');
                    $data->file_id = str_ireplace('public/business/profiles/', '', $path);
                    $data->extension = $file->extension();
                    $data->path = $path;
                    $data->save();
                }

            }

            DB::commit();

            return response()->json(Customer::find($request->file_id), 200);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function hideCustomer(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $user = User::findOrFail($request->id);
            Transaction::where('client_id', $user->id)->delete();
            Order::where('client_id', $user->id)->delete();
            DealingSheet::where('client_id', $user->id)->delete();
            Profile::where('user_id', $user->id)->delete();
            User::where('parent_id', $user->id)->delete();
            JointProfile::where('user_id', $user->id)->delete();
            $user->delete();

            DB::commit();

            return $this->onSuccessResponse('');
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function customers_kyc(Request $request): JsonResponse
    {
        try {
            $users = User::customers()
                ->latest('created_at')
                ->where('status', "active")
                ->where('onboard_status',"!=", "finished")
                ->paginate(getenv('PERPAGE'));

            return response()->json($users);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }
    public function customers_onboarding(Request $request): JsonResponse
    {
        try {
            $users = User::customers()
                ->latest('created_at')
                ->where('status', "new")
                ->paginate(getenv('PERPAGE'));

            return response()->json($users);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function approve(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            $user = User::findOrFail($request->id);

            if ($user->updated_by == $request->header('id')) {
//                return response()->json(['message' => 'Maker Checker Failed'], 400);
            }

            $user->approved_by = $request->header('id');
            $user->once_auth = 'yes';
            $user->status = 'active';
            $user->save();

            //            AccountSyncJob::dispatch($user->id);
            DB::commit();

            return $this->onSuccessResponse('Request processed successfully');
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function approve_kyc(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            $user = User::findOrFail($request->id);
            $user->onboard_status = 'finished';
            $user->save();
            $mailable = new WelcomeEmailMailable($user);
            Mail::to($user->email)->send($mailable);
            DB::commit();

            return $this->onSuccessResponse('Request processed successfully');
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function change_status(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            $user = User::findOrFail($request->id);

            if (strtolower($request->status) == 'active') {
                if ($user->updated_by == $request->header('id')) {
//                    return response()->json(['message' => 'Maker Checker Failed'], 400);
                }

                //                $mailable = new AccountActivated($user);
                //                Mail::to($user)->queue($mailable);

                $user->approved_by = $request->header('id');
                $user->once_auth = 'yes';
                //                AccountSyncJob::dispatchAfterResponse($user->id);
            }

            if (strtolower($request->status) != 'active') {
                $user->approved_by = '';
            }

            $user->status = $request->status;
            $user->save();

            DB::commit();

            return $this->onSuccessResponse('Request processed successfully');
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function upgrade_minor(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();

            $status = User::where('email', $request->email)->first();

            if (! empty($status)) {
                return response()->json(['message' => 'Email has been used, Try different email'], 500);
            }

            $user = User::findOrFail($request->id);
            $user->password = Hash::make(12345678900);
            $user->email = $request->email;
            $user->type = 'individual';
            $user->updated_by == $request->header('id');
            $user->status = 'pending';
            $user->save();

            DB::commit();

            return response()->json(new \App\Helpers\Clients\Profile($user->id), 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function upgrade_individual(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            $user = User::findOrFail($request->id);
            $user->type = 'joint';
            $user->updated_by == $request->header('id');
            $user->status = 'pending';
            $user->save();

            $profile = new JointProfile();
            $profile->updated_by == $request->header('id');
            $profile->user_id = $user->id;
            $profile->save();

            DB::commit();

            return response()->json(new \App\Helpers\Clients\Profile($user->id), 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function downgrade_individual(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            $parent = User::findOrFail($request->parent_id);
            $user = User::findOrFail($request->id);
            $user->parent_relationship = $request->parent_relationship;
            $user->type = 'minor';
            $user->parent_id = $parent->id;
            $user->email = null;
            $user->updated_by == $request->header('id');
            $user->status = 'pending';
            $user->save();

            DB::commit();

            return response()->json(new \App\Helpers\Clients\Profile($user->id));
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function downgrade_joint(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            $user = User::findOrFail($request->id);
            $user->type = 'individual';
            $user->updated_by == $request->header('id');
            $user->status = 'pending';
            $user->save();

            $profile = JointProfile::where('user_id', $user->id)->firstOrFail();
            $profile->delete();

            DB::commit();

            return response()->json(new \App\Helpers\Clients\Profile($user->id));
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function new_customers(Request $request): JsonResponse
    {
        try {
            $users = User::customers()->latest()->where('status', 'pending')->paginate(getenv('PERPAGE'));

            return response()->json(['users' => $users], 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function pending_customers_custodians(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $users = User::customers()->whereNotNull('custodian_id')->where('custodian_approved', 'no')->latest('created_at')->paginate($per_page);

            return response()->json($users);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function customers(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $users = User::customers()->latest('created_at')
                ->where('status', "!=","new")
                ->paginate($per_page);

            return response()->json($users);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function customersList(Request $request): JsonResponse
    {
        try {
            $users = DB::table('users')->select(['name', 'id'])->whereIn('type', ['individual', 'corporate', 'joint'])->orderBy('name')->get();

            return response()->json($users);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function customers_wallet(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $users = Wallet::customers()->latest('created_at')->paginate($per_page);

            return response()->json($users);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function debtors(Request $request): JsonResponse
    {
        try {
            $users = User::customers()->get();
            $ids = [];
            if (! empty($users)) {
                foreach ($users as $key => $user) {
                    if ($user->wallet_balance < 0) {
                        $ids[$key] = $user->id;
                    }
                }
            }
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $users = User::customers()->whereIn('id', $ids)->latest()->paginate($per_page);

            return response()->json($users);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function creditors(Request $request): JsonResponse
    {
        try {
            $users = User::customers()->get();
            $ids = [];
            if (! empty($users)) {
                foreach ($users as $key => $user) {
                    if ($user->wallet_balance > 0) {
                        $ids[$key] = $user->id;
                    }
                }
            }
            $per_page = ! empty($request->per_page) ? $request->per_page : getenv('PERPAGE');
            $users = User::whereIn('id', $ids)->latest()->paginate($per_page);

            return response()->json($users);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function customers_status(Request $request): JsonResponse
    {
        try {
            $users = User::customers()->latest('created_at')->where('status', $request->status)->paginate(getenv('PERPAGE'));

            return response()->json($users);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function portfolio(Request $request): JsonResponse
    {
        try {
            $profile = new \App\Helpers\Clients\Portfolio($request->id);

            return response()->json($profile);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function profile(Request $request): JsonResponse
    {
        if ($request->id == 'undefined') {
            return response()->json();
        }
        try {
            $profile = new \App\Helpers\Clients\Profile($request->id);

            return response()->json($profile);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function create_customer_joint(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), ValidationHelper::newJointProfileValidator());

            if ($validator->fails()) {
                return response()->json([
                    'code' => 102,
                    'message' => $validator->messages()->first(),
                    'errors' => $validator->errors(),
                ], 400);
            }
            $middlename = $request->j_firstname;
            $category = CustomerCategory::findOrFail($request->category);
            $user = new User();
            $user->risk_status = $request->risk_status ?? '';
            $user->flex_acc_no = $request->flex_acc_no ?? '';
            $user->updated_by = $request->header('id');
            $user->firstname = $request->firstname;
            $user->middlename = $request->middlename ?? '';
            $user->lastname = $request->lastname;
            $user->name = $request->firstname.' '.$user->middlename.' '.$request->lastname.' & '.$request->j_firstname.' '.$middlename.' '.$request->j_lastname;
            $user->email = $request->joint_email;
            $user->mobile = $request->joint_mobile;
            $user->status = 'pending';
            $user->type = 'joint';
            $user->email_verified_at = now()->toDateTimeString();
            $user->password = Factory::create()->password();
            $user->self_registration = false;
            $user->dse_account = $request->dse_account;
            $user->bank_id = $request->bank_id;
            $user->bank_account_name = $request->bank_account_name;
            $user->bank_account_number = $request->bank_account_number;
            $user->bank_branch = $request->bank_branch;
            $user->manager_id = $category->manager_id;
            $user->category_id = $category->id;
            $user->is_admin = false;
            $user->save();
            Helper::customerUID($user);

            $user->custodian_id = $request->custodian_id ?? '';
            if (! empty($request->custodian_id) && $request->custodian_id != null && $request->custodian_id != 'null') {
                $custodian = new CustomerCustodian();
                $custodian->user_id = $user->id;
                $custodian->custodian_id = $request->custodian_id;
                $custodian->save();
                $user->has_custodian = 'yes';
            } else {
                $user->has_custodian = 'no';
            }
            $user->custodian_approved = 'no';
            $user->save();

            // first applicant
            $country = Country::where('iso2', $request->country)->firstOrFail();
            $profile = new Profile();
            $profile->region = $request->region;
            $profile->district = $request->district;
            $profile->ward = $request->ward;
            $profile->place_birth = $request->place_birth;
            $profile->title = $request->title;
            $profile->firstname = $request->firstname;
            $profile->middlename = $request->middlename ?? '';
            $profile->lastname = $request->lastname;
            $profile->name = $request->firstname.' '.$profile->middlename.' '.$request->lastname;
            $profile->gender = $request->gender;
            $profile->dob = $request->dob;
            $profile->identity = $request->identity;
            $profile->updated_by = $request->header('id');

            if (! empty($request->tin_file) && $request->hasFile('tin_file')) {
                $path = Helper::storageArea().'identities/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('tin_file');
                Storage::disk('main_storage')->put('identities/'.$file->hashName(), File::get($file));
                $profile->tin_file = $file->hashName();
            }

            if (! empty($request->signature_file) && $request->hasFile('signature_file')) {
                $path = Helper::storageArea().'identities/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('signature_file');
                Storage::disk('main_storage')->put('identities/'.$file->hashName(), File::get($file));
                $profile->signature_file = $file->hashName();
            }

            if (! empty($request->passport_file) && $request->hasFile('passport_file')) {
                $file = $request->file('passport_file');
                $path = Helper::storageArea().'passports/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                Storage::disk('main_storage')->put('passports/'.$file->hashName(), File::get($file));
                $profile->passport_file = $file->hashName();
            }

            if (! empty($request->identity_file) && $request->hasFile('identity_file')) {
                $path = Helper::storageArea().'identities/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('identity_file');
                Storage::disk('main_storage')->put('identities/'.$file->hashName(), File::get($file));
                $profile->identity_file = $file->hashName();
            }
            $profile->country_id = $country->id;
            $profile->address = $request->address;
            $profile->mobile = $request->mobile;
            $profile->email = $request->email;
            $profile->nationality = $request->nationality;
            $profile->employment_status = $request->employment_status;
            $profile->tin = $request->tin;
            $profile->current_occupation = $request->current_occupation;
            $profile->employer_name = $request->employer_name;
            $profile->business_sector = $request->business_sector;
            $profile->other_title = $request->other_title;
            $profile->other_business = $request->other_business;
            $profile->other_employment = $request->other_employment;
            $profile->user_id = $user->id;
            $profile->save();

            // second applicant
            $country = Country::where('iso2', $request->j_country)->firstOrFail();
            $profile = new JointProfile();
            $profile->title = $request->j_title;
            $profile->firstname = $request->j_firstname;
            $profile->middlename = $request->j_middlename ?? '';
            $profile->lastname = $request->j_lastname;
            $profile->name = $request->j_firstname.' '.$profile->middlename.' '.$request->j_lastname;
            $profile->gender = $request->j_gender;
            $profile->dob = $request->j_dob;
            $profile->identity = $request->j_identity;

            if (! empty($request->j_passport_file) && $request->hasFile('j_passport_file')) {
                $file = $request->file('j_passport_file');
                $path = Helper::storageArea().'passports/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                Storage::disk('main_storage')->put('passports/'.$file->hashName(), File::get($file));
                $profile->passport_file = $file->hashName();
            }

            if (! empty($request->j_identity_file) && $request->hasFile('j_identity_file')) {
                $path = Helper::storageArea().'identities/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('j_identity_file');
                Storage::disk('main_storage')->put('identities/'.$file->hashName(), File::get($file));
                $profile->identity_file = $file->hashName();
            }

            if (! empty($request->j_tin_file) && $request->hasFile('j_tin_file')) {
                $path = Helper::storageArea().'identities/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('j_tin_file');
                Storage::disk('main_storage')->put('identities/'.$file->hashName(), File::get($file));
                $profile->tin_file = $file->hashName();
            }

            if (! empty($request->j_signature_file) && $request->hasFile('j_signature_file')) {
                $path = Helper::storageArea().'identities/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('j_signature_file');
                Storage::disk('main_storage')->put('identities/'.$file->hashName(), File::get($file));
                $profile->signature_file = $file->hashName();
            }

            $profile->region = $request->j_region;
            $profile->district = $request->j_district;
            $profile->ward = $request->j_ward;
            $profile->place_birth = $request->j_place_birth;
            $profile->country_id = $country->id;
            $profile->address = $request->j_address;
            $profile->mobile = $request->j_mobile;
            $profile->email = $request->j_email;
            $profile->nationality = $request->j_nationality;
            $profile->employment_status = $request->j_employment_status;
            $profile->tin = $request->tin;
            $profile->current_occupation = $request->j_current_occupation;
            $profile->employer_name = $request->j_employer_name;
            $profile->business_sector = $request->j_business_sector;
            $profile->other_title = $request->j_other_title;
            $profile->other_business = $request->j_other_business;
            $profile->other_employment = $request->j_other_employment;
            $profile->user_id = $user->id;
            $profile->save();

            $kin = new NextOfKin();
            $kin->parent = $user->id;
            $kin->mobile = $request->k_mobile;
            $kin->email = $request->k_email;
            $kin->relationship = $request->k_relationship;
            $kin->name = $request->k_name;
            $kin->save();

            //            $welcomeMailable = new WelcomeEmailMailable($user);
            //            Mail::to($user)->queue($welcomeMailable);
            //            $user->sendEmailVerificationNotification();

            DB::commit();

            $response = User::customers()->latest()->paginate(getenv('PERPAGE'));

            return response()->json($response);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function update_customer_joint(Request $request): JsonResponse
    {

        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), ValidationHelper::jointProfileValidator());

            if ($validator->fails()) {
                return response()->json([
                    'code' => 102,
                    'message' => $validator->messages()->first(),
                    'errors' => $validator->errors(),
                ], 400);
            }

            $middlename = $request->middlename ?? '';
            $j_middlename = $request->j_middlename ?? '';
            $category = CustomerCategory::findOrFail($request->category);
            $user = User::findOrFail($request->id);
            $user->risk_status = $request->risk_status ?? '';
            $user->flex_acc_no = $request->flex_acc_no ?? '';
            $user->updated_by = $request->header('id');
            $user->approved_by = '';
            $user->firstname = $request->firstname;
            $user->middlename = $request->middlename ?? '';
            $user->lastname = $request->lastname;
            $user->name = $request->firstname.' '.$middlename.' '.$request->lastname.' & '.$request->j_firstname.' '.$j_middlename.' '.$request->j_lastname;
            $user->email = $request->joint_email;
            $user->mobile = $request->joint_mobile;
            $user->status = 'pending';
            $user->self_registration = false;
            $user->dse_account = $request->dse_account;
            $user->bank_id = $request->bank_id;
            $user->bank_account_name = $request->bank_account_name;
            $user->bank_account_number = $request->bank_account_number;
            $user->bank_branch = $request->bank_branch;
            $user->manager_id = $category->manager_id;
            $user->category_id = $category->id;
            $user->is_admin = false;
            $user->save();

            // first applicant
            $country = Country::where('iso2', $request->country)->firstOrFail();
            $profile = Profile::where('user_id', $request->id)->firstOrFail();
            $profile->region = $request->region;
            $profile->district = $request->district;
            $profile->ward = $request->ward;
            $profile->place_birth = $request->place_birth;
            $profile->user_id = $request->id;
            $profile->title = $request->title;
            $profile->firstname = $request->firstname;
            $profile->middlename = $request->middlename ?? '';
            $profile->lastname = $request->lastname;
            $profile->name = $request->firstname.' '.$middlename.' '.$request->lastname;
            $profile->gender = $request->gender;
            $profile->dob = $request->dob;
            $profile->identity = $request->identity;
            $profile->updated_by = $request->header('id');

            if (! empty($request->passport_file) && $request->hasFile('passport_file')) {
                $file = $request->file('passport_file');
                $path = Helper::storageArea().'passports/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                Storage::disk('main_storage')->put('passports/'.$file->hashName(), File::get($file));
                $profile->passport_file = $file->hashName();
            }

            if (! empty($request->identity_file) && $request->hasFile('identity_file')) {
                $path = Helper::storageArea().'identities/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('identity_file');
                Storage::disk('main_storage')->put('identities/'.$file->hashName(), File::get($file));
                $profile->identity_file = $file->hashName();
            }

            if (! empty($request->tin_file) && $request->hasFile('tin_file')) {
                $path = Helper::storageArea().'identities/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('tin_file');
                Storage::disk('main_storage')->put('identities/'.$file->hashName(), File::get($file));
                $profile->tin_file = $file->hashName();
            }

            if (! empty($request->signature_file) && $request->hasFile('signature_file')) {
                $path = Helper::storageArea().'identities/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('signature_file');
                Storage::disk('main_storage')->put('identities/'.$file->hashName(), File::get($file));
                $profile->signature_file = $file->hashName();
            }

            $profile->country_id = $country->id;
            $profile->address = $request->address;
            $profile->mobile = $request->mobile;
            $profile->email = $request->email;
            $profile->nationality = $request->nationality;
            $profile->employment_status = $request->employment_status;
            $profile->tin = $request->tin;
            $profile->current_occupation = $request->current_occupation;
            $profile->employer_name = $request->employer_name;
            $profile->business_sector = $request->business_sector;
            $profile->other_title = $request->other_title;
            $profile->other_business = $request->other_business;
            $profile->other_employment = $request->other_employment;
            $profile->user_id = $user->id;
            $profile->save();

            // second applicant
            $country = Country::where('iso2', $request->j_country)->firstOrFail();
            $profile = JointProfile::where('user_id', $request->id)->firstOrFail();
            $profile->region = $request->j_region;
            $profile->district = $request->j_district;
            $profile->ward = $request->j_ward;
            $profile->place_birth = $request->j_place_birth;
            $profile->title = $request->j_title;
            $profile->firstname = $request->j_firstname;
            $profile->middlename = $request->j_middlename ?? '';
            $profile->lastname = $request->j_lastname;
            $profile->name = $request->j_firstname.' '.$j_middlename.' '.$request->j_lastname;
            $profile->gender = $request->j_gender;
            $profile->dob = $request->j_dob;
            $profile->identity = $request->j_identity;

            if (! empty($request->j_tin_file) && $request->hasFile('j_tin_file')) {
                $path = Helper::storageArea().'identities/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('j_tin_file');
                Storage::disk('main_storage')->put('identities/'.$file->hashName(), File::get($file));
                $profile->tin_file = $file->hashName();
            }

            if (! empty($request->j_signature_file) && $request->hasFile('j_signature_file')) {
                $path = Helper::storageArea().'identities/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('j_signature_file');
                Storage::disk('main_storage')->put('identities/'.$file->hashName(), File::get($file));
                $profile->signature_file = $file->hashName();
            }

            if (! empty($request->j_passport_file) && $request->hasFile('j_passport_file')) {
                $file = $request->file('j_passport_file');
                $path = Helper::storageArea().'passports/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                Storage::disk('main_storage')->put('passports/'.$file->hashName(), File::get($file));
                $profile->passport_file = $file->hashName();
            }

            if (! empty($request->j_identity_file) && $request->hasFile('j_identity_file')) {
                $path = Helper::storageArea().'identities/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('j_identity_file');
                Storage::disk('main_storage')->put('identities/'.$file->hashName(), File::get($file));
                $profile->identity_file = $file->hashName();
            }

            $profile->country_id = $country->id;
            $profile->address = $request->j_address;
            $profile->mobile = $request->j_mobile;
            $profile->email = $request->j_email;
            $profile->nationality = $request->j_nationality;
            $profile->employment_status = $request->j_employment_status;
            $profile->tin = $request->j_tin;
            $profile->current_occupation = $request->j_current_occupation;
            $profile->employer_name = $request->j_employer_name;
            $profile->business_sector = $request->j_business_sector;
            $profile->other_title = $request->j_other_title;
            $profile->other_business = $request->j_other_business;
            $profile->other_employment = $request->j_other_employment;
            $profile->user_id = $user->id;
            $profile->save();

            $kin = NextOfKin::where('parent', $request->id)->firstOrFail();
            $kin->parent = $user->id;
            $kin->mobile = $request->k_mobile;
            $kin->email = $request->k_email;
            $kin->relationship = $request->k_relationship;
            $kin->name = $request->k_name;
            $kin->save();

            DB::commit();

            $response = new \App\Helpers\Clients\Profile($request->id);

            return response()->json($response);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function create_customer_minor(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            $category = CustomerCategory::findOrFail($request->category);
            $user = new User();
            $user->risk_status = $request->risk_status ?? '';
            $user->parent_id = $request->parent_id;
            $user->firstname = $request->firstname;
            $user->middlename = $request->middlename ?? '';
            $user->lastname = $request->lastname;
            $user->parent_relationship = $request->parent_relationship;
            $user->updated_by = $request->header('id');
            $user->name = $request->firstname.' '.$request->middlename ?? ''.' '.$request->lastname;
            $user->flex_acc_no = $request->flex_acc_no ?? '';
            $user->dse_account = $request->dse_account ?? '';

            $user->status = 'pending';
            $user->type = 'minor';
            $user->email_verified_at = now()->toDateTimeString();
            $user->self_registration = false;

            $user->manager_id = $category->manager_id;
            $user->category_id = $category->id;
            $user->is_admin = false;
            $user->save();
            Helper::customerUID($user);

            $user->custodian_id = $request->custodian_id ?? '';
            if (! empty($request->custodian_id) && $request->custodian_id != null && $request->custodian_id != 'null') {
                $custodian = new CustomerCustodian();
                $custodian->user_id = $user->id;
                $custodian->custodian_id = $request->custodian_id;
                $custodian->save();
                $user->has_custodian = 'yes';
            } else {
                $user->has_custodian = 'no';
            }
            $user->custodian_approved = 'no';
            $user->save();

            $profile = new Profile();
            $profile->firstname = $request->firstname;
            $profile->middlename = $request->middlename ?? '';
            $profile->lastname = $request->lastname;
            $profile->name = $request->firstname.' '.$request->middlename ?? ''.' '.$request->lastname;
            $profile->gender = $request->gender;
            $profile->dob = $request->dob;
            $profile->tin = $request->tin ?? '';
            $profile->identity = $request->identity;
            $profile->updated_by = $request->header('id');

            if (! empty($request->passport_file) && $request->hasFile('passport_file')) {
                $file = $request->file('passport_file');
                $path = Helper::storageArea().'passports/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                Storage::disk('main_storage')->put('passports/'.$file->hashName(), File::get($file));
                $profile->passport_file = $file->hashName();
            }

            if (! empty($request->identity_file) && $request->hasFile('identity_file')) {
                $path = Helper::storageArea().'identities/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('identity_file');
                Storage::disk('main_storage')->put('identities/'.$file->hashName(), File::get($file));
                $profile->identity_file = $file->hashName();
            }

            $profile->user_id = $user->id;
            $profile->save();

            //            $welcomeMailable = new WelcomeEmailMailable($user);
            //            Mail::to([$user->email])
            //                ->queue($welcomeMailable);
            //            $user->sendEmailVerificationNotification();

            DB::commit();

            $response = User::customers()->latest()->paginate(getenv('PERPAGE'));

            return response()->json($response);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function update_customer_minor(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            $category = CustomerCategory::findOrFail($request->category);
            $user = User::findOrFail($request->id);
            $user->risk_status = $request->risk_status ?? '';
            $user->parent_id = $request->parent_id;
            $user->firstname = $request->firstname;
            $user->middlename = $request->middlename ?? '';
            $user->lastname = $request->lastname;
            $user->parent_relationship = $request->parent_relationship;
            $user->updated_by = $request->header('id');
            $user->name = $request->firstname.' '.$user->middlename.' '.$request->lastname;
            $user->flex_acc_no = $request->flex_acc_no ?? '';
            $user->dse_account = $request->dse_account ?? '';
            $user->self_registration = false;
            $user->manager_id = $category->manager_id;
            $user->category_id = $category->id;
            $user->is_admin = false;
            $user->status = 'pending';
            $user->save();
            Helper::customerUID($user);

            $profile = Profile::where('user_id', $request->id)->first();
            $profile->firstname = $request->firstname;
            $profile->middlename = $request->middlename ?? '';
            $profile->lastname = $request->lastname;
            $profile->name = $request->firstname.' '.$profile->middlename.' '.$request->lastname;
            $profile->gender = $request->gender;
            $profile->dob = $request->dob;
            $profile->tin = $request->tin ?? '';
            $profile->identity = $request->identity;
            $profile->updated_by = $request->header('id');

            if (! empty($request->passport_file) && $request->hasFile('passport_file')) {
                $file = $request->file('passport_file');
                $path = Helper::storageArea().'passports/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                Storage::disk('main_storage')->put('passports/'.$file->hashName(), File::get($file));
                $profile->passport_file = $file->hashName();
            }

            if (! empty($request->identity_file) && $request->hasFile('identity_file')) {
                $path = Helper::storageArea().'identities/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('identity_file');
                Storage::disk('main_storage')->put('identities/'.$file->hashName(), File::get($file));
                $profile->identity_file = $file->hashName();
            }

            $profile->user_id = $user->id;
            $profile->save();

            //            $welcomeMailable = new WelcomeEmailMailable($user);
            //            Mail::to([$user->email])
            //                ->queue($welcomeMailable);
            //            $user->sendEmailVerificationNotification();

            DB::commit();

            $response = new \App\Helpers\Clients\Profile($request->id);

            return response()->json($response);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function inspect_account(Request $request): JsonResponse
    {
        try {
            $data = DSEPayloadDTO::fromRequest($request);
            $status = DB::table('profiles')->where('identity', $data->nidaNumber)->first();
            if (! empty($status)) {
                return $this->onErrorResponse('Client with ID'.$data->nidaNumber.' Exists in Lockminds as '.$status->firstname.' '.$status->lastname);
            } else {
                $status = DB::table('joint_profiles')->where('identity', $data->nidaNumber)->first();
                if (! empty($status)) {
                    return $this->onErrorResponse('Client with ID '.$data->nidaNumber.' Exists in Lockminds as '.$status->firstname.' '.$status->lastname);
                } else {
                    return $this->onSuccessResponse('');
                }
            }
        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function verify_dse_account(Request $request): JsonResponse
    {
        try {
            $data = DSEPayloadDTO::fromRequest($request);
            $dseAccount = DSEHelper::verifyAccount($data);
            if (empty($dseAccount->dseAccount)) {
                //  return response()->json(["message" => "We could not find DSE Account"],400);
            }

            return response()->json($dseAccount);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function fetch_dse_account(Request $request): JsonResponse
    {
        try {
            $profile = Profile::where('email', $request->nidaNumber)->first();
            $data = DSEPayloadDTO::fromRequest($request);
            $dseAccount = DSEHelper::accountDetails($data);
            if (empty($dseAccount->nidaAccount)) {
                return response()->json(['message' => 'We could not find DSE Account'], 400);
            }
            //            $dseAccount = InvestorAccountDetailsDTO::fromJson(json_encode($dseAccount));
            $dseAccount = new InvestorAccountDetailsDTO();
            $account = new \stdClass();
            $account->dse_account = $dseAccount->nidaAccount;
            $account->joint_email = $dseAccount->email;
            $account->joint_mobile = $dseAccount->phoneNumber;
            $account->firstname = $dseAccount->firstName;
            $account->middlename = $dseAccount->middleName;
            $account->lastname = $dseAccount->lastName;
            $account->dob = $dseAccount->dob;
            $account->gender = $dseAccount->gender;
            $account->address = $dseAccount->physicalAddress;
            $account->email = $dseAccount->email;
            $account->mobile = $dseAccount->phoneNumber;
            $account->nationality = $dseAccount->nationality;
            $account->country = $dseAccount->country;
            $account->region = $dseAccount->region;
            $account->identity = $data->nidaNumber;

            return response()->json($profile);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function create_customer_existing(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $category = CustomerCategory::findOrFail($request->category);
            $user = new User();
            $user->risk_status = $request->risk_status ?? '';
            $user->flex_acc_no = $request->flex_acc_no ?? '';
            $user->updated_by = $request->header('id');
            $user->firstname = $request->firstname;
            $user->middlename = $request->middlename ?? '';
            $user->lastname = $request->lastname;
            $user->name = $request->firstname.' '.$request->middlename ?? ''.' '.$request->lastname;
            $user->email = $request->joint_email;
            $user->mobile = $request->joint_mobile;
            $user->status = 'pending';
            $user->type = 'individual';
            $user->email_verified_at = now()->toDateTimeString();
            $user->password = Hash::make($request->password);
            $user->self_registration = false;

            $user->dse_account = $request->dse_account ?? '';
            $user->bank_id = $request->bank_id;
            $user->bank_account_name = $request->bank_account_name;
            $user->bank_account_number = $request->bank_account_number;
            $user->bank_branch = $request->bank_branch;
            $user->manager_id = $category->manager_id;
            $user->category_id = $category->id;
            $user->is_admin = false;
            $user->has_custodian = 'no';
            $user->custodian_approved = 'no';
            $user->save();
            Helper::customerUID($user);

            $country = Country::where('iso2', $request->country)->firstOrFail();
            $profile = new Profile();
            $profile->title = $request->title;
            $profile->firstname = $request->firstname;
            $profile->middlename = $request->middlename ?? '';
            $profile->lastname = $request->lastname;
            $profile->name = $request->firstname.' '.$request->middlename ?? ''.' '.$request->lastname;
            $profile->gender = $request->gender;
            $profile->dob = $request->dob;
            $profile->identity = $request->identity;
            $profile->updated_by = $request->header('id');

            $profile->country_id = $country->id;
            $profile->nationality = $request->nationality;
            $profile->address = $request->address;
            $profile->mobile = $request->mobile;
            $profile->email = $request->email;
            $profile->employment_status = $request->employment_status;
            $profile->tin = $request->tin;
            $profile->current_occupation = $request->current_occupation;
            $profile->employer_name = $request->employer_name;
            $profile->business_sector = $request->business_sector;
            $profile->other_title = $request->other_title;
            $profile->other_business = $request->other_business;
            $profile->other_employment = $request->other_employment;
            $profile->user_id = $user->id;
            $profile->save();

            $kin = new NextOfKin();
            $kin->parent = $user->id;
            $kin->mobile = $request->k_mobile;
            $kin->email = $request->k_email;
            $kin->relationship = $request->k_relationship;
            $kin->name = $request->k_name;
            $kin->save();

            //            $welcomeMailable = new WelcomeEmailMailable($user);
            //            Mail::to([$user->email])
            //                ->queue($welcomeMailable);
            //            $user->sendEmailVerificationNotification();

            DB::commit();

            $response = User::customers()->latest()->paginate(getenv('PERPAGE'));

            return response()->json($response);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function create_customer_individual(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), ValidationHelper::newIndividualProfileValidator());

            if ($validator->fails()) {
                return response()->json([
                    'code' => 102,
                    'message' => $validator->messages()->first(),
                    'errors' => $validator->errors(),
                ], 400);
            }

            $category = CustomerCategory::findOrFail($request->category);
            $user = new User();
            $user->risk_status = $request->risk_status ?? '';
            $user->flex_acc_no = $request->flex_acc_no ?? '';
            $user->updated_by = $request->header('id');
            $user->firstname = $request->firstname;
            $user->middlename = $request->middlename ?? '';
            $user->lastname = $request->lastname;
            $user->name = $request->firstname.' '.$user->middlename.' '.$request->lastname;
            $user->email = $request->joint_email;
            $user->mobile = str_ireplace("+","",$request->joint_mobile);
            $user->status = 'pending';
            $user->type = 'individual';
            $user->email_verified_at = now()->toDateTimeString();
            $user->password = Hash::make($request->password);
            $user->self_registration = false;

            $user->dse_account = $request->dse_account ?? '';
            $user->bank_id = $request->bank_id;
            $user->bank_account_name = $request->bank_account_name;
            $user->bank_account_number = $request->bank_account_number;
            $user->bank_branch = $request->bank_branch;
            $user->manager_id = $category->manager_id;
            $user->category_id = $category->id;
            $user->is_admin = false;
            $user->save();
            Helper::customerUID($user);

            $user->custodian_id = $request->custodian_id ?? '';
            if (! empty($request->custodian_id) && $request->custodian_id != null && $request->custodian_id != 'null') {
                $custodian = new CustomerCustodian();
                $custodian->user_id = $user->id;
                $custodian->custodian_id = $request->custodian_id;
                $custodian->save();
                $user->has_custodian = 'yes';
            } else {
                $user->has_custodian = 'no';
            }
            $user->custodian_approved = 'no';
            $user->save();
            $country = Country::where('iso2', $request->country)->firstOrFail();
            $profile = new Profile();
            $profile->region = $request->region;
            $profile->district = $request->district;
            $profile->ward = $request->ward;
            $profile->place_birth = $request->place_birth;
            $profile->title = $request->title;
            $profile->firstname = $request->firstname;
            $profile->middlename = $request->middlename ?? '';
            $profile->lastname = $request->lastname;
            $profile->name = $request->firstname.' '.$request->middlename ?? ''.' '.$request->lastname;
            $profile->gender = $request->gender;
            $profile->dob = $request->dob;
            $profile->identity = $request->identity;
            $profile->updated_by = $request->header('id');

            if (! empty($request->passport_file) && $request->hasFile('passport_file')) {
                $file = $request->file('passport_file');
                $path = Helper::storageArea().'passports/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                Storage::disk('main_storage')->put('passports/'.$file->hashName(), File::get($file));
                $profile->passport_file = $file->hashName();
            }

            if (! empty($request->identity_file) && $request->hasFile('identity_file')) {
                $path = Helper::storageArea().'identities/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('identity_file');
                Storage::disk('main_storage')->put('identities/'.$file->hashName(), File::get($file));
                $profile->identity_file = $file->hashName();
            }

            if (! empty($request->tin_file) && $request->hasFile('tin_file')) {
                $path = Helper::storageArea().'identities/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('tin_file');
                Storage::disk('main_storage')->put('identities/'.$file->hashName(), File::get($file));
                $profile->tin_file = $file->hashName();
            }

            if (! empty($request->signature_file) && $request->hasFile('signature_file')) {
                $path = Helper::storageArea().'identities/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('signature_file');
                Storage::disk('main_storage')->put('identities/'.$file->hashName(), File::get($file));
                $profile->signature_file = $file->hashName();
            }

            $profile->country_id = $country->id;
            $profile->nationality = $request->nationality;
            $profile->address = $request->address;
            $profile->mobile = $request->mobile;
            $profile->email = $request->email;
            $profile->employment_status = $request->employment_status;
            $profile->tin = $request->tin;
            $profile->current_occupation = $request->current_occupation;
            $profile->employer_name = $request->employer_name;
            $profile->business_sector = $request->business_sector;
            $profile->other_title = $request->other_title;
            $profile->other_business = $request->other_business;
            $profile->other_employment = $request->other_employment;
            $profile->user_id = $user->id;
            $profile->save();

            $kin = new NextOfKin();
            $kin->parent = $user->id;
            $kin->mobile = $request->k_mobile;
            $kin->email = $request->k_email;
            $kin->relationship = $request->k_relationship;
            $kin->name = $request->k_name;
            $kin->save();

            //            $welcomeMailable = new WelcomeEmailMailable($user);
            //            Mail::to([$user->email])
            //                ->queue($welcomeMailable);
            //            $user->sendEmailVerificationNotification();

            DB::commit();

            $response = User::customers()->latest()->paginate(getenv('PERPAGE'));

            return response()->json($response);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function update_customer_individual(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), ValidationHelper::individualProfileValidator(),[
                'validation.phone' => 'Invalid mobile number'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 102,
                    'message' => $validator->messages()->first(),
                    'errors' => $validator->errors(),
                ], 400);
            }

            $middlename = $request->middlename ?? '';
            $category = CustomerCategory::findOrFail($request->category);
            $user = User::findOrFail($request->id);
            $user->source_of_income = $request->source_of_income;
            $user->income_frequency = $request->income_frequency;
            $user->risk_status = $request->risk_status ?? '';
            $user->flex_acc_no = $request->flex_acc_no ?? '';
            $user->name = $request->firstname.' '.$middlename.' '.$request->lastname;
            $user->firstname = $request->firstname;
            $user->middlename = $request->middlename ?? '';
            $user->lastname = $request->lastname;
            $user->mobile = $request->joint_mobile;
            $user->dse_account = $request->dse_account;
            $user->bank_id = $request->bank_id;
            $user->bank_account_name = $request->bank_account_name;
            $user->bank_account_number = $request->bank_account_number;
            $user->bank_branch = $request->bank_branch;
            $user->manager_id = $category->manager_id;
            $user->category_id = $category->id;
            $user->updated_by = $request->header('id');
            $user->approved_by = '';
            $user->status = 'pending';
            $user->save();

            $country = Country::where('iso2', $request->country)->firstOrFail();
            $profile = Profile::whereUserId($request->id)->firstOrFail();
            $profile->region = $request->region;
            $profile->district = $request->district;
            $profile->ward = $request->ward;
            $profile->place_birth = $request->place_birth;
            $profile->title = $request->title;
            $profile->firstname = $request->firstname;
            $profile->middlename = $request->middlename;
            $profile->lastname = $request->lastname;
            $profile->name = $request->firstname.' '.$request->middlename ?? ''.' '.$request->lastname;
            $profile->gender = $request->gender;
            $profile->dob = $request->dob;
            $profile->identity = $request->identity;
            $profile->updated_by = $request->header('id');

            if (! empty($request->passport_file) && $request->hasFile('passport_file')) {
                $file = $request->file('passport_file');
                $path = Helper::storageArea().'passports/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                Storage::disk('main_storage')->put('passports/'.$file->hashName(), File::get($file));
                $profile->passport_file = $file->hashName();
            }

            if (! empty($request->identity_file) && $request->hasFile('identity_file')) {
                $path = Helper::storageArea().'identities/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('identity_file');
                Storage::disk('main_storage')->put('identities/'.$file->hashName(), File::get($file));
                $profile->identity_file = $file->hashName();
            }

            if (! empty($request->tin_file) && $request->hasFile('tin_file')) {
                $path = Helper::storageArea().'identities/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('tin_file');
                Storage::disk('main_storage')->put('identities/'.$file->hashName(), File::get($file));
                $profile->tin_file = $file->hashName();
            }

            if (! empty($request->signature_file) && $request->hasFile('signature_file')) {
                $path = Helper::storageArea().'identities/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('signature_file');
                Storage::disk('main_storage')->put('identities/'.$file->hashName(), File::get($file));
                $profile->signature_file = $file->hashName();
            }

            $profile->country_id = $country->id;
            $profile->address = $request->address;
            $profile->mobile = $request->mobile;
            $profile->email = $request->email;
            $profile->nationality = $request->nationality;
            $profile->employment_status = $request->employment_status;
            $profile->tin = $request->tin;
            $profile->employer_name = $request->employer_name;
            $profile->current_occupation = $request->current_occupation;
            $profile->business_sector = $request->business_sector;
            $profile->other_title = $request->other_title;
            $profile->other_business = $request->other_business;
            $profile->other_employment = $request->other_employment;
            $profile->save();

            $kin = NextOfKin::where('parent', $request->id)->firstOrFail();
            $kin->parent = $user->id;
            $kin->mobile = $request->k_mobile;
            $kin->email = $request->k_email;
            $kin->relationship = $request->k_relationship;
            $kin->name = $request->k_name;
            $kin->save();

            DB::commit();

            $response = new \App\Helpers\Clients\Profile($request->id);

            return response()->json($response);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function create_customer_corporate(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            $category = CustomerCategory::findOrFail($request->category);
            $user = new User();
            $user->risk_status = $request->risk_status ?? '';
            $user->flex_acc_no = $request->flex_acc_no ?? '';
            $user->updated_by = $request->header('id');
            $user->firstname = $request->firstname;
            $user->middlename = $request->middlename ?? '';
            $user->lastname = $request->lastname;
            $user->name = $request->corporate_name;
            $user->email = $request->joint_email;
            $user->mobile = $request->joint_mobile;
            $user->status = 'pending';
            $user->type = 'corporate';
            $user->email_verified_at = now()->toDateTimeString();
            $user->password = Factory::create()->password();
            $user->self_registration = false;
            $user->dse_account = $request->dse_account;
            $user->bank_id = $request->bank_id;
            $user->bank_account_name = $request->bank_account_name;
            $user->bank_account_number = $request->bank_account_number;
            $user->bank_branch = $request->bank_branch;
            $user->manager_id = $category->manager_id;
            $user->category_id = $category->id;
            $user->is_admin = false;
            $user->save();
            Helper::customerUID($user);

            $user->custodian_id = $request->custodian_id ?? '';
            if (! empty($request->custodian_id) && $request->custodian_id != null && $request->custodian_id != 'null') {
                $custodian = new CustomerCustodian();
                $custodian->user_id = $user->id;
                $custodian->custodian_id = $request->custodian_id;
                $custodian->save();
                $user->has_custodian = 'yes';
            } else {
                $user->has_custodian = 'no';
            }
            $user->custodian_approved = 'no';
            $user->save();

            // first applicant
            $profile = new Profile();

            $country = Country::where('iso2', $request->country)->firstOrFail();
            $profile->country_id = $country->id;
            $profile->address = $request->address;
            $profile->nationality = $request->nationality;
            $profile->firstname = $request->firstname;
            $profile->middlename = $request->middlename ?? '';
            $profile->lastname = $request->lastname;
            $profile->position = $request->position;
            $profile->region = $request->region;
            $profile->district = $request->district;
            $profile->ward = $request->ward;
            $profile->place_birth = $request->place_birth;
            $profile->name = $request->firstname.' '.$request->middlename ?? ''.' '.$request->lastname;
            $profile->mobile = $request->mobile;
            $profile->email = $request->email;
            $profile->user_id = $user->id;
            $profile->business_sector = $request->business_sector;
            $profile->updated_by = $request->header('id');
            $profile->save();

            // second applicant
            $profile = new Corporate();
            $profile->user_id = $user->id;
            $profile->corporate_type = $request->corporate_type;
            $profile->other_corporate_type = $request->other_corporate_type;
            $profile->corporate_name = $request->corporate_name;
            $profile->business_sector = $request->business_sector;
            $profile->corporate_telephone = $request->corporate_telephone;
            $profile->corporate_email = $request->corporate_email;
            $profile->corporate_trade_name = $request->corporate_trade_name;
            $profile->corporate_address = $request->corporate_address;
            $profile->corporate_building = $request->corporate_building;
            $profile->corporate_reg_number = $request->corporate_reg_number;
            $profile->corporate_tin = $request->corporate_tin;

            if (! empty($request->certificate_incorporation) && $request->hasFile('certificate_incorporation')) {
                $path = Helper::storageArea().'files/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('certificate_incorporation');
                Storage::disk('main_storage')->put('files/'.$file->hashName(), File::get($file));
                $profile->certificate_incorporation = $file->hashName();
            }

            if (! empty($request->board_resolution) && $request->hasFile('board_resolution')) {
                $path = Helper::storageArea().'files/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('board_resolution');
                Storage::disk('main_storage')->put('files/'.$file->hashName(), File::get($file));
                $profile->board_resolution = $file->hashName();
            }

            if (! empty($request->tin_file) && $request->hasFile('tin_certificate')) {
                $path = Helper::storageArea().'identities/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('tin_certificate');
                Storage::disk('main_storage')->put('identities/'.$file->hashName(), File::get($file));
                $profile->tin_certificate = $file->hashName();
            }

            if (! empty($request->signature_file) && $request->hasFile('signature_file')) {
                $path = Helper::storageArea().'identities/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('signature_file');
                Storage::disk('main_storage')->put('identities/'.$file->hashName(), File::get($file));
                $profile->signature_file = $file->hashName();
            }

            $profile->save();

            //            $welcomeMailable = new WelcomeEmailMailable($user);
            //            Mail::to($user)->queue($welcomeMailable);
            //            $user->sendEmailVerificationNotification();

            DB::commit();

            $response = User::customers()->latest()->paginate(getenv('PERPAGE'));

            return response()->json($response);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function update_customer_corporate(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            $category = CustomerCategory::findOrFail($request->category);
            $user = User::find($request->id);
            $user->risk_status = $request->risk_status ?? '';
            $user->flex_acc_no = $request->flex_acc_no ?? '';
            $user->firstname = $request->firstname;
            $user->middlename = $request->middlename ?? '';
            $user->lastname = $request->lastname;
            $user->name = $request->corporate_name;
            $user->email = $request->joint_email;
            $user->mobile = $request->joint_mobile;
            $user->dse_account = $request->dse_account;
            $user->bank_id = $request->bank_id;
            $user->bank_account_name = $request->bank_account_name;
            $user->bank_account_number = $request->bank_account_number;
            $user->bank_branch = $request->bank_branch;
            $user->manager_id = $category->manager_id;
            $user->category_id = $category->id;
            $user->is_admin = false;
            $user->updated_by = $request->header('id');
            $user->approved_by = '';
            $user->status = 'pending';
            $user->save();

            // first applicant

            $profile = Profile::whereUserId($request->id)->first();
            $country = Country::where('iso2', $request->country)->firstOrFail();
            $profile->country_id = $country->id;
            $profile->address = $request->address;
            $profile->nationality = $request->nationality;
            $profile->region = $request->region;
            $profile->district = $request->district;
            $profile->ward = $request->ward;
            $profile->place_birth = $request->place_birth;
            $profile->position = $request->position;
            $profile->firstname = $request->firstname;
            $profile->middlename = $request->middlename ?? '';
            $profile->lastname = $request->lastname;
            $profile->name = $request->firstname.' '.$request->middlename ?? ''.' '.$request->lastname;
            $profile->mobile = $request->mobile;
            $profile->email = $request->email;
            $profile->user_id = $user->id;
            $profile->save();
            $profile->updated_by = $request->header('id');

            // second applicant
            $profile = Corporate::where('user_id', $request->id)->first();
            $profile->corporate_type = $request->corporate_type;
            $profile->other_corporate_type = $request->other_corporate_type;
            $profile->corporate_name = $request->corporate_name;
            $profile->business_sector = $request->business_sector;
            $profile->corporate_telephone = $request->corporate_telephone;
            $profile->corporate_email = $request->corporate_email;
            $profile->corporate_trade_name = $request->corporate_trade_name;
            $profile->corporate_address = $request->corporate_address;
            $profile->corporate_building = $request->corporate_building;
            $profile->corporate_reg_number = $request->corporate_reg_number;
            $profile->corporate_tin = $request->corporate_tin;

            if (! empty($request->certificate_incorporation) && $request->hasFile('certificate_incorporation')) {
                $path = Helper::storageArea().'files/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('certificate_incorporation');
                Storage::disk('main_storage')->put('files/'.$file->hashName(), File::get($file));
                $profile->certificate_incorporation = $file->hashName();
            }

            if (! empty($request->board_resolution) && $request->hasFile('board_resolution')) {
                $path = Helper::storageArea().'files/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('board_resolution');
                Storage::disk('main_storage')->put('files/'.$file->hashName(), File::get($file));
                $profile->board_resolution = $file->hashName();
            }

            if (! empty($request->tin_certificate) && $request->hasFile('tin_certificate')) {
                $path = Helper::storageArea().'files/';
                File::isDirectory($path) or File::makeDirectory($path, 0766, true, true);
                $file = $request->file('tin_certificate');
                Storage::disk('main_storage')->put('files/'.$file->hashName(), File::get($file));
                $profile->tin_certificate = $file->hashName();
            }

            $profile->save();

            DB::commit();
            $response = new \App\Helpers\Clients\Profile($request->id);

            return response()->json($response);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function importCustomers(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            $headings = (new HeadingRowImport)->toArray(request()->file('file'));
            Excel::import(new UsersImport, request()->file('file'));
            if (strtolower(request()->mode) == 'create') {
                DB::commit();
            }

            if (strtolower(request()->mode) == 'update') {
                DB::commit();
            }

            $response = User::customers()->latest()->paginate(getenv('PERPAGE'));

            return response()->json($response);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }
}
