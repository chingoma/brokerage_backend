<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Clients\EmailsHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Accounting\FinancialYear;
use App\Models\Auths\PasswordReset;
use App\Models\Business;
use App\Models\Profile;
use App\Models\Role;
use App\Models\Token;
use App\Models\User;
use App\Rules\ValidationHelper;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Throwable;

class AuthController extends Controller
{
    public function change_user_email(Request $request)
    {
        try {
            $user = User::findOrFail($request->id);
            $user->newEmail($request->email);

            return response()->json(['message' => 'Email change request submitted']);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function send_password_reset(Request $request): JsonResponse
    {

        try {

            $user = User::where('email', $request->email)->first();

            if (empty($user->email)) {
                return response()->json(['message' => 'We could not find account with provided information'], 400);
            }

            if (! $user->is_admin) {
                return response()->json(['message' => 'No A We could not find account with provided information'], 400);
            }

            $token = Helper::generateRandomString(191);

            PasswordReset::where('email', $request->email)->delete();

            $reset = new PasswordReset();
            $reset->email = $request->email;
            $reset->token = $token;
            $reset->created_at = now()->toDateTimeString();
            $reset->save();

            EmailsHelper::send_password_reset_link_admin(user: $user, token: $token, request: $request);
            event(new \Illuminate\Auth\Events\PasswordReset($user));

            return response()->json(['Password reset link has been sent']);

        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function logout(Request $request)
    {
//        $request->user()->currentAccessToken()->delete();
        $request->user()->tokens()->delete();
        try {
            Token::where("tokenable_id",$request->user()->id())->delete();
        }catch (Throwable $throwable){

        }
    }

    public function login(Request $request): JsonResponse
    {

        $validator = Validator::make($request->all(), ValidationHelper::passwordValidator());

        if ($validator->fails()) {
            return response()->json([
                'code' => 102,
                'message' => "The provided credentials are incorrect",
                'errors' => $validator->errors(),
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (empty($user)) {
            return response()->json(['message' => 'The provided credentials are incorrect'], 400);
        }

        if (! Hash::check($request->password, $user->password)) {
                        event(new Failed($user->name,$user,['']));
            return response()->json(['message' => 'The provided credentials are incorrect'], 400);
        }

        if (! $user->is_admin) {
            event(new Failed($user->name,$user,['email' => $request->email,'password' => $request->password]));
            return response()->json(['message' => 'The provided credentials are incorrect'], 400);
        }

        $profile = Helper::profile($user->id);

        try {

            //    $user->syncRoles(["Administrator"]);

            return $this->_loginResponse($user, $profile, $request);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function register(Request $request): JsonResponse
    {
        try {

            DB::beginTransaction();

            $user = new User();

            $user->name = $request->name;
            $user->password = Hash::make($request->password);
            $user->email = $request->email;
            $user->save();
            Helper::customerUID($user);

            $profile = new Profile();
            $profile->name = $request->name;
            $profile->user_id = $user->id;
            $profile->save();

            $business = new Business();
            $business->name = $request->name;
            $business->save();

            $financialYear = new FinancialYear();
            $now = now()->addYear();
            $financialYear->name = now()->year.' - '.$now->year;
            $financialYear->year_start = now()->toDateTimeString();
            $financialYear->year_end = $now->toDateTimeString();
            $financialYear->status = 'active';

            DB::commit();

            return $this->_loginResponse($user, $profile, $request);

        } catch (Throwable $ex) {
            DB::rollBack();
            report($ex);

            return $this->onErrorResponse($ex->getMessage());
        }
    }

    private function _loginResponse($user, $profile, Request $request): JsonResponse
    {
        $role = Role::where('name', $user->getRoleNames()[0] ?? '')->first();
        $permissionList = [];
        if (! empty($role)) {
            $permissions = $role->getAllPermissions();
            foreach ($permissions as $permission) {
                $permissionList[] = str_replace(' ', '_', strtolower($permission));
            }
        }

        try {
            Token::where("tokenable_id",$user->id())->delete();
        }catch (Throwable $throwable){

        }

        $token = $user->createToken($request->header('user-agent'),[]);
        $tokenable = Token::orderBy('id', 'DESC')->where('tokenable_id', $user->id)->first();
        if(!empty($tokenable)) {
            $tokenable->ip_address = $request->ip();
        }
        $tokenable->save();

        event(new Login($profile->name, $user, true));

        Cache::remember('customers', 86400, function() {

            return  DB::table("users")
                ->select([
                    "users.name",
                    "users.email",
                    "users.id",
                ])
                ->selectRaw("customer_categories.name as category_name")
                ->whereIn("users.type",['minor','individual','corporate','joint'])
                ->whereNotNull("dse_account")
                ->whereNotNull("category_id")
                ->orderBy("users.name")
                ->leftJoin("customer_categories","users.category_id","=","customer_categories.id")
                ->get();
        });

        return response()->json([
            'authToken' => $token->plainTextToken,
            'token' => $token->plainTextToken,
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->getRoleNames()[0] ?? '',
            'permissions' => $permissionList,
            'name' => $user->name,
        ]);
    }
}
