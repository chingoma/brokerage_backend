<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Business;
use App\Models\Role;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;
use Throwable;

class BusinessController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function roles_all(Request $request)
    {

        try {
            $perpage = env('PERPAGE');

            if (! empty($request->length)) {
                $perpage = $request->length;
            }

            if (! empty($request->start)) {
                $request->merge(['page' => $request->start / $perpage]);
            }

            $query = (object) $request->input('search');

            if (! empty($request->input('search'))) {
                $roles = Role::where('roles.name', 'LIKE', "%{$query->value}%")
                    ->paginate($perpage);
            } else {
                $roles = Role::paginate($perpage);
            }

            $response = [
                'recordsTotal' => Role::count(),
                'recordsFiltered' => $roles->total(),
                'draw' => $request->draw,
                'data' => $roles->items(),
            ];

            return response()->json($response, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json(['status' => false, 'message' => $throwable->getMessage()], 500);
        }

    }

    public function roles_modify(Request $request)
    {
        $role = Role::find($request->role);

        return view('/content/settings/business/roleDetailsModify', ['role' => $role]);
    }

    /**
     * @return JsonResponse
     */
    public function roles_create(Request $request)
    {
        if (empty($request->permissions) && ! is_array($request->permissions)) {
            return response()->json(['status' => false, 'message' => 'You must specify at least one permission'], 200);
        }
        try {
            DB::beginTransaction();
            $role = new Role();
            $role->name = $request->name;
            $role->description = $request->description;
            $role->save();

            $permissions = $request->permissions;
            $role->syncPermissions($permissions);

            $obj = new stdClass();
            $obj->modal = $role;
            $obj->details = 'new role has been created';
            $this->log_activity_create($obj);
            DB::commit();

            return response()->json(['status' => true, 'message' => 'Role created successfully'], 200);
        } catch (Throwable $throwable) {
            report($throwable);
            DB::rollBack();

            return response()->json(['status' => false, 'message' => $throwable->getMessage()], 500);
        }
    }

    /**
     * @return JsonResponse
     */
    public function branches(Request $request)
    {

        try {
            $perpage = env('PERPAGE');

            if (! empty($request->length)) {
                $perpage = $request->length;
            }

            if (! empty($request->start)) {
                $request->merge(['page' => $request->start / $perpage]);
            }

            $query = (object) $request->input('search');

            if (! empty($request->input('search'))) {
                $branches = Branch::where('branches.name', 'LIKE', "%{$query->value}%")
                    ->paginate($perpage);
            } else {
                $branches = Branch::paginate($perpage);
            }

            $response = [
                'recordsTotal' => Branch::count(),
                'recordsFiltered' => $branches->total(),
                'draw' => $request->draw,
                'data' => $branches->items(),
            ];

            return response()->json($response, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json(['status' => false, 'message' => $throwable->getMessage()], 500);
        }

    }

    public function branches_details(Request $request)
    {
        $branch = Branch::find($request->branch);

        return view('/content/settings/business/branchDetails', ['branch' => $branch]);
    }

    public function branches_modify(Request $request)
    {
        $branch = Branch::find($request->branch);

        return view('/content/settings/business/branchDetailsModify', ['branch' => $branch]);
    }

    /**
     * @return JsonResponse
     */
    public function branches_update(Request $request)
    {
        $branch = Branch::find($request->branch);
        $original = Branch::find($request->branch);

        try {
            $branch->administrator_id = ! empty($request->administrator_id) ? $request->administrator_id : Auth::user()->id;
            $branch->name = $request->name;
            $branch->address = $request->address;
            $branch->fax = $request->fax;
            $branch->telephone = $request->telephone;
            $branch->country_id = $request->country_id;
            $branch->email = $request->email;
            $branch->website = $request->website;
            $branch->facebook = $request->facebook;
            $branch->twitter = $request->twitter;
            $branch->google = $request->google;
            $branch->quora = $request->quora;
            $branch->instagram = $request->instagram;
            $branch->linkedin = $request->linkedin;
            $branch->save();

            $changes = $branch->getChanges();
            $columns = array_keys($changes);

            $old = new stdClass();
            if (! empty($columns)) {
                foreach ($columns as $column) {
                    $old->$column = $original->getOriginal($column);
                }

            }

            return response()->json(['status' => true, 'message' => 'Business branch updated successfully'], 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json(['status' => false, 'message' => $throwable->getMessage()], 500);
        }
    }

    /**
     * @return JsonResponse
     */
    public function branches_create(Request $request)
    {
        $branch = new Branch();
        try {
            DB::beginTransaction();
            $branch->administrator_id = ! empty($request->administrator_id) ? $request->administrator_id : Auth::user()->id;
            $branch->name = $request->name;
            $branch->address = $request->address;
            $branch->fax = $request->fax;
            $branch->telephone = $request->telephone;
            $branch->country_id = $request->country_id;
            $branch->email = $request->email;
            $branch->website = $request->website;
            $branch->facebook = $request->facebook;
            $branch->twitter = $request->twitter;
            $branch->google = $request->google;
            $branch->quora = $request->quora;
            $branch->instagram = $request->instagram;
            $branch->linkedin = $request->linkedin;

            if ($branch->save()) {
                $obj = new stdClass();
                $obj->modal = $branch;
                $obj->details = 'new branch has been created';
                $obj->link = 'this is the link';
                $this->log_activity_create($obj);
            }

            DB::commit();

            return response()->json(['status' => true, 'message' => 'Business branch created successfully'], 200);
        } catch (Throwable $throwable) {
            report($throwable);
            DB::rollBack();

            return response()->json(['status' => false, 'message' => $throwable->getMessage()], 500);
        }
    }

    /**
     * @return Application|Factory|View
     */
    public function business(Request $request)
    {
        $business = $request->user()->business;
        $breadcrumbs = [['link' => '/', 'name' => 'Home'], ['link' => route('profile'), 'name' => 'Settings'], ['name' => 'Settings Business']];

        return view('/content/settings/business/settings', ['breadcrumbs' => $breadcrumbs, 'business' => $business]);
    }

    public function update(Request $request)
    {
        $business = Business::first();
        try {
            //$business->administrator = auth()->user()->id;
            $business->name = $request->name;
            $business->address = $request->address;
            $business->fax = $request->fax;
            $business->telephone = $request->telephone;
            $business->country_id = $request->country_id;
            $business->email = $request->email;
            $business->website = $request->website;
            $business->save();

            return response()->json(['status' => true, 'message' => 'Business general updated successfully'], 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json(['status' => false, 'message' => $throwable->getMessage()], 500);
        }
    }

    public function update_social(Request $request)
    {
        $business = Business::first();
        try {
            $business->facebook = $request->facebook;
            $business->twitter = $request->twitter;
            $business->google = $request->google;
            $business->quora = $request->quora;
            $business->instagram = $request->instagram;
            $business->linkedin = $request->linkedin;
            $business->save();

            return response()->json(['status' => true, 'message' => 'Business social links updated successfully'], 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json(['status' => false, 'message' => $throwable->getMessage()], 500);
        }
    }

    public function update_logo(Request $request)
    {
        $business = Business::first();
        $oldLogo = $business->logo;
        try {
            $path = $request->file('file')->store('public/business/logo');
            $business->logo = str_ireplace('public/', '', $path);
            $business->save();

            if (! empty($oldLogo) && file_exists(public_path('storage/'.$oldLogo))) {
                unlink(public_path('storage/'.$oldLogo));
            }

            return response()->json(['status' => true, 'message' => 'Business logo changed successfully'], 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json(['status' => false, 'message' => $throwable->getMessage()], 500);
        }

    }

    public function branches_details_print(Request $request)
    {
        $branch['branch'] = Branch::find($request->branch);
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML(view('/content/settings/business/branchDetails', $branch));

        return $pdf->download('invoice.pdf');
    }
}
