<?php

namespace Modules\EndOfDayReport\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Calendar\Entities\Calendar;
use Modules\EndOfDayReport\Entities\EndDayReport;
use Modules\EndOfDayReport\Jobs\GenerateEndOfDayReport;
use Modules\EndOfDayReport\Jobs\ReGenerateEndOfDayReport;

class EndOfDayReportController extends Controller
{
    public function generateReport(Request $request)
    {
        try {

            $systemDate = Helper::systemDateTime();
            $status = EndDayReport::where('date', $systemDate['today'])->first();
            if (! empty($status)) {
                return $this->onErrorResponse('You can not run same report twice, use regenerate report option');
            }
            $report = new EndDayReport();
            $report->date = $systemDate['today'];
            $report->process_status = 'running';
            $report->system_status = 'pending';
            $report->finance_status = 'pending';
            $report->trade_status = 'pending';
            $report->save();

            GenerateEndOfDayReport::dispatchAfterResponse($report)->delay(now()->addMinutes());

            return $this->_list($request);
        } catch (\Throwable $throwable) {
            DB::rollBack();
            report($throwable);
            $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function regenerateReport(Request $request)
    {
        try {
            $report = EndDayReport::findOrFail($request->id);
            $report->process_status = 'running';
            $report->system_status = 'pending';
            $report->finance_status = 'pending';
            $report->trade_status = 'pending';
            $report->save();

            ReGenerateEndOfDayReport::dispatchAfterResponse($report)->delay(now()->addMinutes());

            //            dispatch(function () {
            //                ReGenerateEndOfDayReport::dispatchSync($this->endDayReport);
            //            })->delay(now()->addMinutes())->afterResponse();

            return $this->_list($request);
        } catch (\Throwable $throwable) {
            DB::rollBack();
            report($throwable);
            $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function endDay(Request $request)
    {

        try {
            $systemDate = Helper::systemDateTime();
            $status = Calendar::where('today', $systemDate['today'])->firstOrFail();
            $status->closed = true;
            $status->save();

            return response()->json(['status' => true, 'message' => 'success']);
        } catch (\Throwable $throwable) {
            DB::rollBack();
            report($throwable);
            $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function rollbackDay(Request $request)
    {
        try {
            $systemDate = Helper::systemDateTime();
            $status = Calendar::orderBy('today', 'desc')->limit(1)->where('calendar', 'Business')->where('today', '<', $systemDate['today'])->firstOrFail();
            $status->closed = false;
            $status->save();

            return response()->json(['status' => true, 'message' => 'success']);
        } catch (\Throwable $throwable) {
            DB::rollBack();
            report($throwable);
            $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function statuses(Request $request)
    {
        try {
            return response()->json(
                [
                    'end_day' => true,
                    'rollback' => true,
                    'generate_report' => true,
                ]
            );
        } catch (\Throwable $throwable) {
            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    protected function _list(Request $request)
    {
        $per_page = ! empty($request->per_page) ? $request->per_page : env('PERPAGE');
        $query = EndDayReport::latest('date');
        $report = $query->paginate($per_page);

        return response()->json($report);
    }
}
