<?php

namespace App\Http\Controllers\Settings;

use App\Helpers\Helper;
use App\Helpers\NotificationsHelper;
use App\Http\Controllers\Controller;
use App\Models\Accounting\FinancialYear;
use App\Models\Business;
use App\Models\Module;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Rules\ValidationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use stdClass;
use Throwable;

//use PhpImap;

class AccountController extends Controller
{
    public function roles_delete_role(Request $request)
    {
        DB::beginTransaction();
        try {
            $role = Role::find($request->role);
            $role->syncPermissions([]);
            $role->forceDelete();

            DB::commit();

            return response()->json(['status' => true, 'message' => 'Successfully deleted a role'], 200);
        } catch (Throwable $throwable) {
            report($throwable);
            DB::rollBack();

            return response()->json(['status' => false, 'message' => $throwable->getMessage()], 500);
        }
    }

    public function roles_add_role(Request $request)
    {
        DB::beginTransaction();
        try {
            $role = new Role();
            $role->name = $request->role;
            $role->guard_name = 'web';
//            $role->description = $request->description;
            $role->save();
            $permissions = explode(',', $request->permissions);
            $role->syncPermissions($permissions);
            DB::commit();

            return response()->json(['status' => true, 'message' => 'Successfully added new role'], 200);
        } catch (Throwable $throwable) {
            report($throwable);
            DB::rollBack();

            return response()->json(['status' => false, 'message' => $throwable->getMessage()], 500);
        }
    }

    public function roles_edit_role(Request $request)
    {
        DB::beginTransaction();
        try {
            $role = Role::find($request->id);
            $role->name = $request->role;
            //            $role->description = $request->description;
            $role->save();

            $permissions = explode(',', $request->permissions);
            $role->syncPermissions($permissions);

            NotificationsHelper::permissionsChanged(1);
            DB::commit();

            return response()->json(['status' => true, 'message' => 'Successfully updated a role'], 200);
        } catch (Throwable $throwable) {
            report($throwable);
            DB::rollBack();

            return response()->json(['status' => false, 'message' => $throwable->getMessage()], 500);
        }
    }

    public function roles_data_permission()
    {
        $response = Permission::orderBy('name')->get();

        return response()->json($response);
    }

    public function roles_data()
    {
        $response['roles'] = Role::get();
        $response['modules'] = Module::orderBy('name')->get();
        $response['users'] = User::admins()->get();

        return response()->json($response, 200);
    }

    public function roles_data_filter(Request $request)
    {
        $response = Role::where('name', 'LIKE', '%'.$request->q.'%')->paginate($request->per_page);

        return response()->json($response, 200);
    }

    public function business_data(Request $request)
    {
        $response = Business::first();
        $response->users = DB::table('users')->get();

        return response()->json($response, 200);
    }

    public function get_business_years(Request $request)
    {
        $response['years'] = FinancialYear::paginate();

        return response()->json($response, 200);
    }

    public function delete_business_year(Request $request)
    {
        $year = FinancialYear::find($request->id);
        if (! empty($year)) {
            $original = clone $year;
        }

        try {
            if ($year->status == 'active') {
                return response()->json(['status' => true, 'message' => 'You can not delete active year'], 500);
            }
            $year->delete();

            return response()->json(['status' => true, 'message' => 'you have successfully deleted business year'], 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json(['status' => true, 'message' => $throwable->getMessage()], 500);
        }
    }

    public function modify_business_year(Request $request)
    {
        $year = FinancialYear::find($request->id);
        try {
            DB::beginTransaction();

            if ($request->status = 'active') {
                $years = FinancialYear::get();
                if (! empty($year)) {
                    foreach ($years as $yea) {
                        $data = FinancialYear::find($yea->id);
                        $data->status = 'inactive';
                        $data->save();
                    }
                }
            }

            $year->name = $request->name;
            $year->year_start = $request->year_start;
            $year->year_end = $request->year_end;
            $year->status = $request->status;

            $year->save();

            DB::commit();

            return response()->json(['status' => true, 'message' => 'you have successfully deleted business year'], 200);
        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return response()->json(['status' => true, 'message' => $throwable->getMessage()], 500);
        }

    }

    public function add_business_year(Request $request)
    {

        try {
            DB::beginTransaction();

            $business = Helper::business();
            if ($request->status == 'active') {
                $years = FinancialYear::get();
                if (! empty($years)) {
                    foreach ($years as $yea) {
                        $data = FinancialYear::find($yea->id);
                        $data->status = 'inactive';
                        $data->save();
                    }
                }
            }

            $year = new FinancialYear();
            $year->name = $request->name;
            $year->year_start = $request->year_start;
            $year->year_end = $request->year_end;
            $year->status = $request->status;

            $year->save();

            if ($year->status == 'active') {

                $business->year_start = $request->year_start;
                $business->year_end = $request->year_end;
                $business->save();
            }

            DB::commit();

            return response()->json(['status' => true, 'message' => 'you have successfully deleted business year'], 200);
        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return response()->json(['status' => true, 'message' => $throwable->getMessage()], 500);
        }

    }

    public function change_business_general_data(Request $request)
    {
        try {

            DB::beginTransaction();
            $profile = Business::first();
            $original = clone $profile;
            $profile->name = $request->name;
            $profile->website = $request->website;
            $profile->fax = $request->fax;
            $profile->email = $request->email;
            $profile->about = (! empty($request->about)) ? $request->about : '';
            $profile->address = $request->address;
            $profile->telephone = $request->telephone;
            $profile->quora = $request->quora;
            $profile->instagram = $request->instagram;
            $profile->linkedin = $request->linkedin;
            $profile->twitter = $request->twitter;
            $profile->google = $request->google;
            $profile->facebook = $request->facebook;
            $profile->save();

            $changes = $profile->getChanges();
            $columns = array_keys($changes);

            $old = new stdClass();
            if (! empty($columns)) {
                foreach ($columns as $column) {
                    $old->$column = $original->getOriginal($column);
                }

            }
            DB::commit();

            return response()->json(['status' => true, 'message' => 'Success', 'profile' => $profile], 200);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return response()->json(['status' => false, 'message' => $throwable->getMessage()], 500);
        }
    }

    public function update_sms_settings(Request $request)
    {
        try {

            DB::beginTransaction();
            $profile = Business::first();
            $profile->sms_api_key = $request->sms_api_key;
            $profile->sms_secret_key = $request->sms_secret_key;
            $profile->sms_sender_id = $request->sms_sender_id;
            $profile->save();

            DB::commit();
            $settings = \DB::table('businesses')->first();
            $recipients = [
                'source_addr' => $settings->sms_sender_id,
                'schedule_time' => '',
                'encoding' => '0',
                'message' => 'This is test sms content number '.random_int(111111111, 999999999),
                'recipients' => [
                    [
                        'recipient_id' => random_int(111111111, 999999999),
                        'dest_addr' => $request->test_number,
                    ],
                ],
            ];
            $endpoint = 'https://apisms.beem.africa/v1/send';
            if (! empty($request->test_number)) {
                Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->withBasicAuth(username: $settings->sms_api_key, password: $settings->sms_secret_key)
                    ->asJson()
                    ->post($endpoint, $recipients)
                    ->onError(function ($error) {
                        return response()->json(['status' => false, 'message' => $error]);
                    });
            }

            return response()->json(['status' => true, 'message' => 'Settings updated successfully']);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return response()->json(['status' => false, 'message' => $throwable->getMessage()], 500);
        }
    }

    public function change_business_broker_data(Request $request)
    {
        try {

            DB::beginTransaction();
            $profile = Business::first();
            $original = clone $profile;
            $profile->csdr = $request->csdr;
            $profile->cmsa = $request->cmsa;
            $profile->ffund = $request->ffund;
            $profile->broker = $request->broker;
            $profile->dse = $request->dse;
            $profile->save();

            $changes = $profile->getChanges();
            $columns = array_keys($changes);

            $old = new stdClass();
            if (! empty($columns)) {
                foreach ($columns as $column) {
                    $old->$column = $original->getOriginal($column);
                }

            }
            DB::commit();

            return response()->json(['status' => true, 'message' => 'Success', 'profile' => $profile], 200);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return response()->json(['status' => false, 'message' => $throwable->getMessage()], 500);
        }
    }

    public function change_business_logo(Request $request)
    {

        try {
            DB::beginTransaction();
            $profile = Business::first();
            $original = clone $profile;
            $oldLogo = $profile->logo;
            $path = $request->file('picture')->store('public/business/profiles');
            $profile->logo = str_ireplace('public/', '', $path);
            $profile->save();

            //            $image = new \Imagick($profile->picture);
            //
            //            $image->thumbnailImage(500, 0);
            //
            //            $image->setInterlaceScheme(\Imagick::INTERLACE_PLANE);
            //
            //            $path =  str_ireplace("public/","",parse_url($profile->picture, PHP_URL_PATH));
            //            $image->writeImage( public_path($path));

            DB::commit();

            return response()->json(['status' => true, 'message' => 'Business logo changed successfully', 'profile' => $profile], 200);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return response()->json(['status' => false, 'message' => $throwable->getMessage()], 500);
        }

    }

    public function change_password(Request $request)
    {

        $validator = Validator::make($request->all(), ValidationHelper::passwordValidator());

        if ($validator->fails()) {
            return response()->json([
                'code' => 102,
                'message' => $validator->messages()->first(),
                'errors' => $validator->errors(),
            ], 400);
        }

        $user = $request->user();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['status' => false, 'message' => 'Wrong old password'], 500);
        }



        $user->password = Hash::make($request->newPassword);
        $user->save();

        return response()->json(['status' => true], 200);
    }

    public function account_data(Request $request)
    {
        return auth()->user();
    }
}
