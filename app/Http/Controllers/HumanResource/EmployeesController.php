<?php

namespace App\Http\Controllers\HumanResource;

use App\Helpers\NotificationsHelper;
use App\Models\PermissionUser;
use App\Models\Profile;
use App\Models\ProfileFile;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use stdClass;
use Throwable;

use function auth;

class EmployeesController extends BaseController
{
    public function settings_data(): JsonResponse
    {
        $response['roles'] = Role::get();

        return response()->json($response);
    }

    public function change_account_social_data(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $profile = User::find($request->id);
            $original = clone $profile;
            $profile->quora = $request->quora;
            $profile->instagram = $request->instagram;
            $profile->linkedin = $request->linkedin;
            $profile->twitter = $request->twitter;
            $profile->google = $request->google;
            $profile->facebook = $request->facebook;
            $profile->save();

            DB::commit();

            return response()->json($profile);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function change_account_mail_server(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $client = Client::make([
                'host' => $request->mail_host,
                'port' => $request->mail_port,
                'encryption' => 'ssl',
                'validate_cert' => true,
                'username' => $request->mail_username,
                'password' => $request->mail_password,
                'protocol' => 'imap',
            ]);

            $client->connect();

            $profile = User::find($request->id);
            $original = clone $profile;

            $profile->mail_host = $request->mail_host;
            $profile->mail_port = $request->mail_port;
            if (! empty($profile->mail_password = $request->mail_password)) {
                $profile->mail_password = $request->mail_password;
            }
            $profile->mail_username = $request->mail_username;
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

            return response()->json($profile);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function change_account_notification_data(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $profile = User::find($request->id);
            $original = clone $profile;
            $profile->notification_daily_activity_report = ($request->notification_daily_activity_report == 'true') ? 'true' : '';
            $profile->notification_daily_performance_report = ($request->notification_daily_performance_report == 'true') ? 'true' : '';
            $profile->notification_emails = ($request->notification_emails == 'true') ? 'true' : '';
            $profile->notification_new_task = ($request->notification_new_task == 'true') ? 'true' : '';
            $profile->notification_chat_messages = ($request->notification_chat_messages == 'true') ? 'true' : '';
            $profile->notification_news = ($request->notification_news == 'true') ? 'true' : '';
            $profile->notification_sound = ($request->notification_sound == 'true') ? 'true' : '';
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

            return response()->json($profile);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function delete_document(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = ProfileFile::find($request->id);
            $copy = clone $data;
            if (! empty($data)) {
                $data->delete();
            }
            DB::commit();

            return response()->json(User::find($request->file_id), 200);

        } catch (Throwable $ex) {
            DB::rollBack();
            report($ex);

            return response()->json(['status' => false, 'message' => 'creation failed '.$ex->getMessage()], 500);
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

            return response()->json(User::find($request->file_id), 200);

        } catch (Throwable $ex) {
            DB::rollBack();
            report($ex);

            return response()->json(['status' => false, 'message' => 'creation failed '.$ex->getMessage()], 500);
        }
    }

    public function change_password(Request $request): JsonResponse
    {
        $user = User::find($request->id);
        $user->password = Hash::make($request->password);

        try {
            DB::beginTransaction();
            $user->save();
            DB::commit();
            $profile = User::find($request->id);

            return response()->json($profile);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function activate(Request $request): JsonResponse
    {
        $profile = User::find($request->id);
        $profile->status = 'active';

        try {
            DB::beginTransaction();
            $profile->save();
            DB::commit();
            $profile = User::find($request->id);

            return response()->json($profile);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function unsuspend(Request $request): JsonResponse
    {
        $profile = User::find($request->id);
        $profile->status = 'active';

        try {
            DB::beginTransaction();
            $profile->save();
            DB::commit();
            $profile = User::find($request->id);

            return response()->json($profile);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function suspend(Request $request): JsonResponse
    {
        $profile = User::find($request->id);
        $profile->status = 'suspended';

        try {
            DB::beginTransaction();
            $profile->save();
            DB::commit();
            $profile = User::find($request->id);

            return response()->json($profile);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function update(Request $request): JsonResponse
    {
        $profile = User::find($request->id);
        $profile->uid = htmlentities($request->uid);
        $profile->name = $request->name;
        $profile->email = $request->email;
        $profile->mobile = $request->mobile;
        try {
            DB::beginTransaction();
            $profile->save();
            $role = Role::findOrFail($request->role);
            if (! empty($role)) {
                $profile->syncRoles([$role->name]);
            }
            DB::commit();

            return response()->json($profile);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }

    }

    public function profile(Request $request): JsonResponse
    {
        try {
            $profile = User::find($request->id);

            return response()->json($profile);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function update_passport(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            $profile = User::find(auth()->user()->profile->id);
            $oldLogo = $profile->picture;
            $path = $request->file('picture')->store('public/business/profiles');
            $profile->profile = str_ireplace('public/', '', $path);
            $profile->save();

            if (! empty($oldLogo) && file_exists(public_path('storage/'.$oldLogo))) {
                unlink(public_path('storage/'.$oldLogo));
            }

            DB::commit();

            return response()->json(['user' => $profile, 'status' => true, 'message' => 'User picture changed successfully', 'profile' => $profile]);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return response()->json(['status' => false, 'message' => $throwable->getMessage()], 500);
        }

    }

    public function Users(Request $request): JsonResponse
    {
        try {
            $users = User::admins()->get();

            return response()->json($users);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function employees(Request $request): JsonResponse
    {
        try {
            $users = User::admins()->get();

            return response()->json($users);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function create(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                "email" => ['required', 'string', 'email', 'max:255', 'unique:users'],
            ]);

            if($validator->fails()) {
                return response()->json(["message" => $validator->messages()->first()], 400);
            }

            DB::beginTransaction();
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            $user->status = 'approved';
            $user->email_verified_at = now()->toDateTimeString();
            $user->password = Hash::make('1234567890');
            $user->is_admin = true;
            $user->save();

            $profile = new Profile();
            $profile->user_id = $user->id;
            $profile->name = $request->name;
            $profile->email = $request->email;
            $profile->mobile = $request->mobile;
            $profile->save();

            $user->syncRoles([$request->role]);
            DB::commit();

            return response()->json(User::admins()->get(), 200);

        } catch (Throwable $ex) {
            DB::rollBack();
            report($ex);

            return $this->onErrorResponse($ex->getMessage());
        }
    }

    public function user_activities(Request $request): JsonResponse
    {
        $response = ActivityLog::where('causer_id', $request->user)->where('profile_id', auth()->user()->current_team_id)->orderBy('id', 'desc')->get();

        return response()->json($response);
    }

    public function update_roles_permissions(Request $request): JsonResponse
    {

        try {
            DB::beginTransaction();
            $profile = User::find($request->id);

            $permissions = PermissionUser::where('profile_id', $profile->id)->get();

            if (! empty($permissions)) {
                foreach ($permissions as $permission) {
                    $perm = PermissionUser::find($permission->id);
                    $perm->forceDelete();
                }
            }

            $permissions = explode(',', $request->permissions);

            if (! empty($permissions) && is_array($permissions)) {
                foreach ($permissions as $permission) {
                    if (! empty($permission)) {
                        $perm = new PermissionUser();
                        $perm->permission_id = $permission;
                        $perm->user_id = $profile->user_id;
                        $perm->profile_id = $profile->id;
                        $perm->user_type = 'users';
                        $perm->save();
                    }
                }
            }

            $roles = RoleUser::where('profile_id', $profile->id)->get();

            if (! empty($roles)) {
                foreach ($roles as $role) {
                    $perm = RoleUser::find($role->id);
                    $perm->forceDelete();
                }
            }

            $roles = explode(',', $request->roles);
            if (! empty($roles) && is_array($roles)) {
                foreach ($roles as $role) {
                    if (! empty($role)) {
                        $perm = new RoleUser();
                        $perm->role_id = $role;
                        $perm->user_id = $profile->user_id;
                        $perm->profile_id = $profile->id;
                        $perm->user_type = 'users';
                        $perm->save();
                    }
                }
            }

            DB::commit();
            NotificationsHelper::permissionsChanged();

            return response()->json(['status' => true, 'message' => 'Successfully updated roles and permissions'], 200);
        } catch (Throwable $throwable) {
            report($throwable);
            DB::rollBack();

            return $this->onErrorResponse($throwable->getMessage());
        }
    }
}
