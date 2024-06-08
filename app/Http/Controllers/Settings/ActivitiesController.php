<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Throwable;

class ActivitiesController extends Controller
{
    /**
     * @return Application|Factory|View
     */
    public function index(Request $request)
    {
        $activities = Activity::orderBy('id', 'desc')->where('causer_id', auth()->user()->id)->get();
        $breadcrumbs = [['link' => '/', 'name' => 'Home'], ['name' => 'Activities']];

        return view('/content/settings/activities/index', ['breadcrumbs' => $breadcrumbs, 'activities' => $activities]);
    }

    public function general(Request $request)
    {

        try {
            $perpage = 5;

            $activities = Activity::orderBy('id', 'desc')->where('causer_id', auth()->user()->id)->paginate($perpage);

            $response = [
                'status' => true,
                'recordsTotal' => $activities->lastPage(),
                'data' => $activities->items(),
            ];

            return response()->json($response, 200);
        } catch (Throwable $throwable) {
            report($throwable);

            return response()->json(['status' => true, 'message' => $throwable->getMessage()], 500);
        }

    }
}
