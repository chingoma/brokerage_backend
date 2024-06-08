<?php

namespace Modules\Calendar\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Calendar\Entities\Calendar;
use Throwable;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        return $this->list($request);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        try {
            DB::beginTransaction();
            $calendar = new Calendar();
            $calendar->calendar = $request->calendar;
            $calendar->today = date('Y-m-d', strtotime($request->start));
            $calendar->start = date('Y-m-d H:i:s', strtotime($request->start));
            $calendar->end = date('Y-m-d H:i:s', strtotime($request->start));
            $calendar->title = $request->title;
            $calendar->save();
            DB::commit();

            return $this->list($request);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        try {
            DB::beginTransaction();
            $calendar = Calendar::findOrFail($request->id);
            $calendar->calendar = $request->calendar;
            $calendar->today = date('Y-m-d', strtotime($request->start));
            $calendar->title = $request->title;
            $calendar->save();
            DB::commit();

            return $this->list($request);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return JsonResponse
     */
    public function delete(Request $request)
    {
        try {
            DB::beginTransaction();
            $calendar = Calendar::findOrFail($request->id);
            $calendar->delete();
            DB::commit();

            return $this->list($request);

        } catch (Throwable $throwable) {
            DB::rollBack();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    private function list(Request $request): JsonResponse
    {
        try {
            $events = Calendar::latest('created_at')->get();

            return response()->json($events);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }
}
