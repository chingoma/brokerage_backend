<?php

namespace App\Http\Controllers\NewsLetter;

use App\Http\Controllers\Controller;
use App\Jobs\SendNewLetter;
use App\Models\MarketReports\MarketCustomReport;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\CRM\Entities\CustomerCategory;
use Throwable;

class CustomReportsController extends Controller
{
    public $options = [];

    public function index(): JsonResponse
    {
        return $this->_reports();
    }

    public function store(Request $request): JsonResponse
    {
        $report = new MarketCustomReport();
        $report->description = $request->description;
        $report->title = $request->title;
        $report->category_id = strtolower($request->category);
        $report->status = 'Draft';
        $report->recipients = User::customers()->count();
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('public/documents');
            $report->file_name = $file->hashName();
            $report->file_ext = $file->extension();
            $report->file_path = $path;
        }

        try {
            DB::beginTransaction();
            $report->save();
            DB::commit();

            return $this->_reports();
        } catch (Throwable $throwable) {
            DB::rollback();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function update(Request $request): JsonResponse
    {
        $report = MarketCustomReport::find($request->id);
        $report->description = $request->description;
        $report->title = $request->title;
        $report->category_id = $request->category;
        $report->recipients = User::customers()->where('category_id', $report->category_id)->count();

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('public/documents');
            $report->file_name = $file->hashName();
            $report->file_ext = $file->extension();
            $report->file_path = $path;
        }

        try {
            DB::beginTransaction();
            $report->save();
            DB::commit();

            return $this->_reports();
        } catch (Throwable $throwable) {
            DB::rollback();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function send(Request $request): JsonResponse
    {

        try {

            $report = MarketCustomReport::findOrFail($request->id);
            SendNewLetter::dispatchAfterResponse($report);

            return $this->_reports();
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function data(Request $request): JsonResponse
    {
        $data['categories'] = CustomerCategory::get();

        return response()->json($data);
    }

    public function reports(): JsonResponse
    {
        return $this->_reports();
    }

    private function _reports(): JsonResponse
    {
        return response()->json(MarketCustomReport::latest()->get());
    }
}
