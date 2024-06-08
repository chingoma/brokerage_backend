<?php

namespace App\Helpers\Clients;

use App\Events\Clients\ClientNotificationEvents;
use App\Helpers\Helper;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\CRM\Entities\CustomerCategory;
use Throwable;

class Auths
{
    public function login(Request $request): JsonResponse
    {

        $user = User::where('email', $request->email)->first();

        if (empty($user)) {
            return response()->json(['message' => 'The provided credentials are incorrect'], 400);
        }

        if (! Hash::check($request->password, $user->password)) {
            event(new Failed($user->profile->name, $user, ['']));

            return response()->json(['message' => 'The provided credentials are incorrect'], 400);
        }

        if ($user->profile->account_type == 'staff') {
            event(new Failed($user->profile->name, $user, ['email' => $request->email, 'password' => $request->password]));

            return response()->json(['message' => 'The provided credentials are incorrect'], 400);
        }

        $profile = Profile::find($user->profile->id);

        try {

            DB::beginTransaction();
            $profile->last_login = Carbon::now()->toDateTimeString();
            $profile->ip_address = $request->ip();
            $profile->save();
            DB::commit();

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
            $user = new User();
            $category = CustomerCategory::where('business_id', 1)->where('default', 'yes')->first();
            $user->name = $request->name;
            $user->password = Hash::make($request->password);
            $user->email = $request->email;

            $profile = new Profile();
            $profile->name = $request->name;
            $profile->country = $request->country_name;
            $profile->country_code = $request->country_code;
            $profile->id_type = $request->identity_type;
            $profile->identity = $request->identity;
            $profile->status = 'pending';
            $profile->account_type = 'customer';
            $profile->category_id = $category->id;
            $profile->manager_id = $category->manager_id;
            $profile->contact_email = $request->email;
            $profile->contact_telephone = Helper::makeSmsReceiver($request->phone);

            DB::beginTransaction();
            $user->save();

            $profile->user_id = $user->id;
            $profile->save();

            $user->business_id = 1;
            $user->save();

            DB::commit();

            $event = new ClientNotificationEvents();
            $event->message = 'New customer '.$profile->name.' created';
            $event->title = 'A new Customer created';
            event(new ClientNotifications($event));

            $mails = new EmailsHelper();
            $mails->send_verification_email($user);

            return $this->_loginResponse($user, $profile, $request);

        } catch (Throwable $ex) {
            DB::rollBack();
            report($ex);

            return $this->onErrorResponse($ex->getMessage());
        }
    }
}
