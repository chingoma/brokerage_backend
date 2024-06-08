<?php

namespace App\Http\Controllers\NewsLetter;

use App\Models\MarketReports\BondDailyPrice;
use App\Models\MarketReports\DseMarketReport;
use App\Models\MarketReports\EquityDailyPrice;
use App\Models\MarketReports\EquityMarketIndicator;
use App\Models\MarketReports\Weekly\WeeklyMarketReport;
use App\Models\MarketReports\Weekly\WeeklyMarketReportCooperateBond;
use App\Models\MarketReports\Weekly\WeeklyMarketReportEquityOverview;
use App\Models\MarketReports\Weekly\WeeklyMarketReportEquitySummary;
use App\Models\MarketReports\Weekly\WeeklyMarketReportGovernmentBond;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Exception;
use stdClass;
use Throwable;

class WeeklyReportsController extends BaseController
{
    public mixed $options = [];

    public function send(Request $request): JsonResponse
    {
        $report = WeeklyMarketReport::find($request->id);

        try {
            $this->_generateReport($report->id);

            return $this->_reports();
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function regenerate(Request $request): JsonResponse
    {
        $report = WeeklyMarketReport::find($request->id);

        try {
            DB::beginTransaction();

            WeeklyMarketReportCooperateBond::where('report_id', $report->id)->delete();
            WeeklyMarketReportGovernmentBond::where('report_id', $report->id)->delete();
            WeeklyMarketReportEquityOverview::where('report_id', $report->id)->delete();
            WeeklyMarketReportEquitySummary::where('report_id', $report->id)->delete();
            $this->_dailyPricesLatest(reportId: $report->id, insert: true);
            $this->_marketIndicators(reportId: $report->id, insert: true);
            $this->_cooperateBonds(reportId: $report->id, insert: true);
            $this->_governmentBonds(reportId: $report->id, insert: true);

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
        $report = WeeklyMarketReport::find($request->id);
        $report->description = $request->description;
        $report->title = $request->title;

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

    public function create(Request $request): JsonResponse
    {
        $report = new WeeklyMarketReport();
        $report->description = $request->description;
        $report->title = $request->title;
        $report->status = 'sent';
        $report->recipients = User::count();

        try {
            DB::beginTransaction();
            $report->save();
            $this->_dailyPricesLatest(reportId: $report->id, insert: true);
            $this->_marketIndicators(reportId: $report->id, insert: true);
            $this->_cooperateBonds(reportId: $report->id, insert: true);
            $this->_governmentBonds(reportId: $report->id, insert: true);

            DB::commit();

            return $this->_reports();
        } catch (Throwable $throwable) {
            DB::rollback();
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function reports(): JsonResponse
    {
        return $this->_reports();
    }

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    private function _generateReport($reportId)
    {
        $report = WeeklyMarketReport::find($reportId);
        $this->send_weekly_report($report);
    }

    private function _reports(): JsonResponse
    {
        return response()->json(WeeklyMarketReport::orderBy('id', 'DESC')->get());
    }

    private function _dailyPricesLatest($reportId, $insert = false): void
    {

        $prices = [];
        $currentReports = $this->_marketReportsCurrentLatest();
        $current = $this->_dailyPricesCurrent($currentReports);
        $currentReportsAll = $this->_marketReportsCurrent();
        $currentTurnover = $this->_turnoverCurrent($currentReportsAll);

        $previousReports = $this->_marketReportsPreviousLatest();
        $previous = $this->_dailyPricesPrevious($previousReports);
        if (! empty($previous)) {
            if (! empty($current)) {

                foreach ($current as $item) {

                    $change = 0;
                    foreach ($previous as $prev) {

                        if (strtolower($prev['company_name']) == strtolower($item['company_name'])) {
                            $change = round(((($item['closing_price'] / $prev['closing_price']) - 1) * 100), 2);
                        }
                    }

                    $turnover = 0;

                    if (! empty($currentTurnover)) {
                        foreach ($currentTurnover as $turn) {
                            if (strtolower($turn['company_name']) == strtolower($item['company_name'])) {
                                $turnover = $turn['total'];
                            }
                        }
                    }

                    $prices[] = [
                        'company' => $item['company_name'],
                        'week_close' => $item['closing_price'],
                        'report_id' => $reportId,
                        'turnover' => $turnover,
                        'change' => $change,
                        'business_id' => auth()->user()->business_id,
                    ];
                }
            }
        } else {
            if (! empty($current)) {
                foreach ($current as $item) {

                    $turnover = 0;

                    if (! empty($currentTurnover)) {
                        foreach ($currentTurnover as $turnover) {
                            if (strtolower($turnover['company_name']) == strtolower($item['company_name'])) {
                                $turnover = $turnover['total'];
                            }
                        }
                    }

                    $prices[] = [
                        'company' => $item['company_name'],
                        'week_close' => $item['closing_price'],
                        'report_id' => $reportId,
                        'turnover' => $turnover,
                        'change' => 0,
                        'business_id' => auth()->user()->business_id,
                    ];
                }
            }
        }

        if ($insert) {
            if (! empty($prices)) {
                foreach ($prices as $price) {
                    $create = new WeeklyMarketReportEquitySummary();
                    $create->company = $price['company'];
                    $create->week_close = $price['week_close'];
                    $create->report_id = $price['report_id'];
                    $create->turnover = $price['turnover'];
                    $create->change = $price['change'];
                    $create->business_id = $price['business_id'];
                    $create->save();
                }
            }
        }

    }

    private function _marketIndicators($reportId, $insert = false): void
    {

        $list = [];
        $currentReports = $this->_marketReportsCurrentLatest();
        $current = $this->_marketIndicatorsCurrent($currentReports);

        $previousReports = $this->_marketReportsPreviousLatest();
        $previous = $this->_marketIndicatorsPrevious($previousReports);

        if (! empty($previous)) {
            if (! empty($current)) {
                foreach ($current as $item) {
                    $change = 0;
                    $before = '';
                    foreach ($previous as $prev) {
                        if (strtolower($prev['name']) == strtolower($item['name'])) {
                            $change = round(((($item['total'] / $prev['total']) - 1) * 100), 2);
                            $before = $prev['total'];
                        }
                    }

                    $list[] = [
                        'equity_overview' => $item['name'],
                        'previous' => $before,
                        'current' => $item['total'],
                        'report_id' => $reportId,
                        'change' => $change,
                        'business_id' => auth()->user()->business_id,
                    ];
                }
            }
        } else {
            if (! empty($current)) {
                foreach ($current as $item) {
                    $list[] = [
                        'equity_overview' => $item['name'],
                        'previous' => 0,
                        'current' => $item['total'],
                        'report_id' => $reportId,
                        'change' => 0,
                        'business_id' => auth()->user()->business_id,
                    ];
                }
            }
        }

        if ($insert) {
            if (! empty($list)) {
                foreach ($list as $item) {
                    $create = new WeeklyMarketReportEquityOverview();
                    $create->equity_overview = $item['equity_overview'];
                    $create->previous = $item['previous'];
                    $create->current = $item['current'];
                    $create->report_id = $item['report_id'];
                    $create->change = $item['change'];
                    $create->business_id = $item['business_id'];
                    $create->save();
                }
            }
        }

    }

    private function _governmentBonds($reportId, $insert = false): void
    {

        $list = [];
        $currentReports = $this->_marketReportsCurrent();
        $bondsCurrent = $this->_governmentBondsCurrent($currentReports);

        if (! empty($bondsCurrent)) {
            foreach ($bondsCurrent as $item) {
                $list[] = [
                    'bond_no' => $item['bond_no'],
                    'duration' => $item['term'],
                    'amount' => $item['amount'],
                    'amount_yield' => $item['amount'] * $item['yield'],
                    'week_close' => $item['price'],
                    'coupon' => $item['coupon'],
                    'yield' => $item['yield'],
                    'report_id' => $reportId,
                    'business_id' => auth()->user()->business_id,
                ];
            }
        }

        if ($insert) {
            if (! empty($list)) {
                foreach ($list as $item) {
                    $create = new WeeklyMarketReportGovernmentBond();
                    $create->duration = $item['duration'];
                    $create->bond_no = $item['bond_no'];
                    $create->week_close = $item['week_close'];
                    $create->coupon = $item['coupon'];
                    $create->amount = $item['amount'];
                    $create->amount_yield = $item['amount'] * $item['yield'];
                    $create->yield = $item['yield'];
                    $create->report_id = $item['report_id'];
                    $create->business_id = $item['business_id'];
                    $create->save();
                }
            }
        }

    }

    private function _governmentBondsCurrent($reports)
    {
        return BondDailyPrice::whereIn('report_id', $reports)
            ->where('bond_no', 'regexp', '^[0-9]')
            ->get();
    }

    private function _cooperateBonds($reportId, $insert = false): array
    {

        $list = [];
        $currentReports = $this->_marketReportsCurrent();
        $bondsCurrent = $this->_cooperateBondsCurrent($currentReports);

        if (! empty($bondsCurrent)) {
            foreach ($bondsCurrent as $item) {
                $list[] = [
                    'bond_no' => $item['bond_no'],
                    'duration' => $item['term'],
                    'week_close' => $item['price'],
                    'coupon' => $item['coupon'],
                    'yield' => $item['yield'],
                    'report_id' => $reportId,
                    'business_id' => auth()->user()->business_id,
                ];
            }
        }

        if ($insert) {
            if (! empty($list)) {
                foreach ($list as $item) {
                    $create = new WeeklyMarketReportCooperateBond();
                    $create->bond_no = $item['bond_no'];
                    $create->duration = $item['duration'];
                    $create->week_close = $item['week_close'];
                    $create->coupon = $item['coupon'];
                    $create->yield = $item['yield'];
                    $create->report_id = $item['report_id'];
                    $create->business_id = $item['business_id'];
                    $create->save();
                }
            }
        }

        return $list;
    }

    private function _cooperateBondsCurrent($reports)
    {
        return BondDailyPrice::whereIn('report_id', $reports)
            ->where('bond_no', 'regexp', '[A-Z]')
            ->get();
    }

    private function _marketReportsCurrent()
    {
        $dates = $this->_weekDaysCurrent();

        return DseMarketReport::select('report_id')
            ->whereDate('report_date', '>=', $dates->start)
            ->whereDate('report_date', '<=', $dates->end)
            ->get();
    }

    private function _marketReportsCurrentLatest()
    {
        $dates = $this->_weekDaysCurrent();

        return DseMarketReport::select('report_id')->orderBy('report_date', 'DESC')
            ->whereDate('report_date', '>=', $dates->start)
            ->whereDate('report_date', '<=', $dates->end)
            ->limit(1)->get();
    }

    private function _marketReportsPrevious()
    {
        $dates = $this->_weekDaysPrevious();

        return DseMarketReport::select('report_id')
            ->whereDate('report_date', '>=', $dates->start)
            ->whereDate('report_date', '<=', $dates->end)
            ->get();
    }

    private function _marketReportsPreviousLatest()
    {
        $dates = $this->_weekDaysPrevious();

        return DseMarketReport::select('report_id')->orderBy('report_date', 'DESC')
            ->whereDate('report_date', '>=', $dates->start)
            ->whereDate('report_date', '<=', $dates->end)
            ->limit(1)->get('report_id');
    }

    private function _turnoverCurrent($reports)
    {
        return EquityDailyPrice::whereIn('report_id', $reports)
            ->select(DB::raw('(SUM(turnover)) as total'), 'company_name')
            ->groupBy('company_name')
            ->get();
    }

    private function _turnoverPrevious($reports)
    {
        return EquityDailyPrice::whereIn('report_id', $reports)
            ->selectRaw('SUM(turnover) as total')
            ->selectRaw('company_name')
            ->groupBy('company_name')
            ->get();
    }

    private function _dailyPricesCurrent($reports)
    {
        return EquityDailyPrice::whereIn('report_id', $reports)->get();
    }

    private function _dailyPricesPrevious($reports)
    {
        return EquityDailyPrice::whereIn('report_id', $reports)->get();
    }

    private function _marketIndicatorsCurrent($reports)
    {
        return EquityMarketIndicator::whereIn('report_id', $reports)
            ->selectRaw('value as total')
            ->selectRaw('name')
            ->get();
    }

    private function _marketIndicatorsPrevious($reports)
    {
        return EquityMarketIndicator::whereIn('report_id', $reports)
            ->selectRaw('name')
            ->selectRaw('value as total')
            ->get();
    }

    private function _weekDaysCurrent(): stdClass
    {
        $now = Carbon::now();
        $weekStartDate = $now->startOfWeek()->format('Y-m-d H:i:s');
        $weekEndDate = $now->endOfWeek()->format('Y-m-d H:i:s');
        $days = new stdClass();
        $days->start = $weekStartDate;
        $days->end = $weekEndDate;

        return $days;
    }

    private function _weekDaysPrevious(): stdClass
    {
        $startOfCurrentWeek = Carbon::now()->startOfWeek();
        $startOfLastWeek = $startOfCurrentWeek->copy()->subDays(7);
        $endOfLastWeek = Carbon::now()->subDays(7)->endOfWeek();
        $days = new stdClass();
        $days->start = $startOfLastWeek;
        $days->end = $endOfLastWeek;

        return $days;
    }

    public function preview(Request $request)
    {
        $report = WeeklyMarketReport::find($request->id);
        $this->options['report'] = $report;
        $this->options['overview'] = WeeklyMarketReportEquityOverview::where('report_id', $report->id)->get();
        $this->options['summary'] = WeeklyMarketReportEquitySummary::where('report_id', $report->id)->get();
        $this->options['gainers'] = WeeklyMarketReportEquitySummary::orderBy('change', 'DESC')->where('change', '>', 0)->where('report_id', $report->id)->limit(5)->get();
        $this->options['losers'] = WeeklyMarketReportEquitySummary::orderBy('change', 'ASC')->where('change', '<', 0)->where('report_id', $report->id)->limit(5)->get();
        $this->options['movers'] = WeeklyMarketReportEquitySummary::orderBy('turnover', 'DESC')->where('report_id', $report->id)->limit(5)->get();
        $this->options['totalTurnover'] = WeeklyMarketReportEquitySummary::where('report_id', $report->id)->sum('turnover');

        $bonds = WeeklyMarketReportGovernmentBond::where('duration', 25)->where('report_id', $report->id)->count();
        if ($bonds > 0) {
            $bonds25['low'] = WeeklyMarketReportGovernmentBond::where('duration', 25)->where('report_id', $report->id)->min('week_close');
            $bonds25['high'] = WeeklyMarketReportGovernmentBond::where('duration', 25)->where('report_id', $report->id)->max('week_close');
            $bonds25['amount'] = WeeklyMarketReportGovernmentBond::where('duration', 25)->where('report_id', $report->id)->sum('amount');
            $bonds25['yield'] = WeeklyMarketReportGovernmentBond::where('duration', 25)->where('report_id', $report->id)->sum('amount_yield') / $bonds25['amount'];
            $this->options['bond25'] = $bonds25;
            $this->options['governmentBonds'] = true;
        }

        $bonds = WeeklyMarketReportGovernmentBond::where('duration', 20)->where('report_id', $report->id)->count();
        if ($bonds > 0) {
            $bonds20['low'] = WeeklyMarketReportGovernmentBond::where('duration', 20)->where('report_id', $report->id)->min('week_close');
            $bonds20['high'] = WeeklyMarketReportGovernmentBond::where('duration', 20)->where('report_id', $report->id)->max('week_close');
            $bonds20['amount'] = WeeklyMarketReportGovernmentBond::where('duration', 20)->where('report_id', $report->id)->sum('amount');
            $bonds20['yield'] = WeeklyMarketReportGovernmentBond::where('duration', 20)->where('report_id', $report->id)->sum('amount_yield') / $bonds20['amount'];
            $this->options['bond20'] = $bonds20;
            $this->options['governmentBonds'] = true;
        }

        $bonds = WeeklyMarketReportGovernmentBond::where('duration', 15)->where('report_id', $report->id)->count();
        if ($bonds > 0) {
            $bonds15['low'] = WeeklyMarketReportGovernmentBond::where('duration', 15)->where('report_id', $report->id)->min('week_close');
            $bonds15['high'] = WeeklyMarketReportGovernmentBond::where('duration', 15)->where('report_id', $report->id)->max('week_close');
            $bonds15['amount'] = WeeklyMarketReportGovernmentBond::where('duration', 15)->where('report_id', $report->id)->sum('amount');
            $bonds15['yield'] = WeeklyMarketReportGovernmentBond::where('duration', 15)->where('report_id', $report->id)->sum('amount_yield') / $bonds15['amount'];
            $this->options['bond15'] = $bonds15;
            $this->options['governmentBonds'] = true;
        }

        $bonds = WeeklyMarketReportGovernmentBond::where('duration', 10)->where('report_id', $report->id)->count();
        if ($bonds > 0) {
            $bonds10['low'] = WeeklyMarketReportGovernmentBond::where('duration', 10)->where('report_id', $report->id)->min('week_close');
            $bonds10['high'] = WeeklyMarketReportGovernmentBond::where('duration', 10)->where('report_id', $report->id)->max('week_close');
            $bonds10['amount'] = WeeklyMarketReportGovernmentBond::where('duration', 10)->where('report_id', $report->id)->sum('amount');
            $bonds10['yield'] = WeeklyMarketReportGovernmentBond::where('duration', 10)->where('report_id', $report->id)->sum('amount_yield') / $bonds10['amount'];
            $this->options['bond10'] = $bonds10;
            $this->options['governmentBonds'] = true;
        }

        $bonds = WeeklyMarketReportGovernmentBond::where('duration', 7)->where('report_id', $report->id)->count();
        if ($bonds > 0) {
            $bonds7['low'] = WeeklyMarketReportGovernmentBond::where('duration', 7)->where('report_id', $report->id)->min('week_close');
            $bonds7['high'] = WeeklyMarketReportGovernmentBond::where('duration', 7)->where('report_id', $report->id)->max('week_close');
            $bonds7['amount'] = WeeklyMarketReportGovernmentBond::where('duration', 7)->where('report_id', $report->id)->sum('amount');
            $bonds7['yield'] = WeeklyMarketReportGovernmentBond::where('duration', 7)->where('report_id', $report->id)->sum('amount_yield') / $bonds7['amount'];
            $this->options['bond7'] = $bonds7;
            $this->options['governmentBonds'] = true;
        }

        $bonds = WeeklyMarketReportGovernmentBond::where('duration', 5)->where('report_id', $report->id)->count();
        if ($bonds > 0) {
            $bonds5['low'] = WeeklyMarketReportGovernmentBond::where('duration', 5)->where('report_id', $report->id)->min('week_close');
            $bonds5['high'] = WeeklyMarketReportGovernmentBond::where('duration', 5)->where('report_id', $report->id)->max('week_close');
            $bonds5['amount'] = WeeklyMarketReportGovernmentBond::where('duration', 5)->where('report_id', $report->id)->sum('amount');
            $bonds5['yield'] = WeeklyMarketReportGovernmentBond::where('duration', 5)->where('report_id', $report->id)->sum('amount_yield') / $bonds5['amount'];
            $this->options['bond5'] = $bonds5;
            $this->options['governmentBonds'] = true;
        }

        $bonds = WeeklyMarketReportGovernmentBond::where('duration', 2)->where('report_id', $report->id)->count();
        if ($bonds > 0) {
            $bonds2['low'] = WeeklyMarketReportGovernmentBond::where('duration', 2)->where('report_id', $report->id)->min('week_close');
            $bonds2['high'] = WeeklyMarketReportGovernmentBond::where('duration', 2)->where('report_id', $report->id)->max('week_close');
            $bonds2['amount'] = WeeklyMarketReportGovernmentBond::where('duration', 2)->where('report_id', $report->id)->sum('amount');
            $bonds2['yield'] = WeeklyMarketReportGovernmentBond::where('duration', 2)->where('report_id', $report->id)->sum('amount_yield') / $bonds2['amount'];
            $this->options['bond2'] = $bonds2;
            $this->options['governmentBonds'] = true;
        }

        $this->options['cooperateBonds'] = WeeklyMarketReportCooperateBond::where('report_id', $report->id)->get();

        return view('emails.market-reports.weekly-report', ['options' => $this->options]);
    }

    public function generate_pdf(Request $request)
    {
        $report = WeeklyMarketReport::find($request->id);
        $this->options['report'] = $report;
        $this->options['overview'] = WeeklyMarketReportEquityOverview::where('report_id', $report->id)->get();
        $this->options['summary'] = WeeklyMarketReportEquitySummary::where('report_id', $report->id)->get();
        $this->options['gainers'] = WeeklyMarketReportEquitySummary::orderBy('change', 'DESC')->where('change', '>', 0)->where('report_id', $report->id)->limit(5)->get();
        $this->options['losers'] = WeeklyMarketReportEquitySummary::orderBy('change', 'ASC')->where('change', '<', 0)->where('report_id', $report->id)->limit(5)->get();
        $this->options['movers'] = WeeklyMarketReportEquitySummary::orderBy('turnover', 'DESC')->where('report_id', $report->id)->limit(5)->get();
        $this->options['totalTurnover'] = WeeklyMarketReportEquitySummary::where('report_id', $report->id)->sum('turnover');

        $bonds = WeeklyMarketReportGovernmentBond::where('duration', 25)->where('report_id', $report->id)->count();
        if ($bonds > 0) {
            $bonds25['low'] = WeeklyMarketReportGovernmentBond::where('duration', 25)->where('report_id', $report->id)->min('week_close');
            $bonds25['high'] = WeeklyMarketReportGovernmentBond::where('duration', 25)->where('report_id', $report->id)->max('week_close');
            $bonds25['amount'] = WeeklyMarketReportGovernmentBond::where('duration', 25)->where('report_id', $report->id)->sum('amount');
            $bonds25['yield'] = WeeklyMarketReportGovernmentBond::where('duration', 25)->where('report_id', $report->id)->sum('amount_yield') / $bonds25['amount'];
            $this->options['bond25'] = $bonds25;
            $this->options['governmentBonds'] = true;
        }

        $bonds = WeeklyMarketReportGovernmentBond::where('duration', 20)->where('report_id', $report->id)->count();
        if ($bonds > 0) {
            $bonds20['low'] = WeeklyMarketReportGovernmentBond::where('duration', 20)->where('report_id', $report->id)->min('week_close');
            $bonds20['high'] = WeeklyMarketReportGovernmentBond::where('duration', 20)->where('report_id', $report->id)->max('week_close');
            $bonds20['amount'] = WeeklyMarketReportGovernmentBond::where('duration', 20)->where('report_id', $report->id)->sum('amount');
            $bonds20['yield'] = WeeklyMarketReportGovernmentBond::where('duration', 20)->where('report_id', $report->id)->sum('amount_yield') / $bonds20['amount'];
            $this->options['bond20'] = $bonds20;
            $this->options['governmentBonds'] = true;
        }

        $bonds = WeeklyMarketReportGovernmentBond::where('duration', 15)->where('report_id', $report->id)->count();
        if ($bonds > 0) {
            $bonds15['low'] = WeeklyMarketReportGovernmentBond::where('duration', 15)->where('report_id', $report->id)->min('week_close');
            $bonds15['high'] = WeeklyMarketReportGovernmentBond::where('duration', 15)->where('report_id', $report->id)->max('week_close');
            $bonds15['amount'] = WeeklyMarketReportGovernmentBond::where('duration', 15)->where('report_id', $report->id)->sum('amount');
            $bonds15['yield'] = WeeklyMarketReportGovernmentBond::where('duration', 15)->where('report_id', $report->id)->sum('amount_yield') / $bonds15['amount'];
            $this->options['bond15'] = $bonds15;
            $this->options['governmentBonds'] = true;
        }

        $bonds = WeeklyMarketReportGovernmentBond::where('duration', 10)->where('report_id', $report->id)->count();
        if ($bonds > 0) {
            $bonds10['low'] = WeeklyMarketReportGovernmentBond::where('duration', 10)->where('report_id', $report->id)->min('week_close');
            $bonds10['high'] = WeeklyMarketReportGovernmentBond::where('duration', 10)->where('report_id', $report->id)->max('week_close');
            $bonds10['amount'] = WeeklyMarketReportGovernmentBond::where('duration', 10)->where('report_id', $report->id)->sum('amount');
            $bonds10['yield'] = WeeklyMarketReportGovernmentBond::where('duration', 10)->where('report_id', $report->id)->sum('amount_yield') / $bonds10['amount'];
            $this->options['bond10'] = $bonds10;
            $this->options['governmentBonds'] = true;
        }

        $bonds = WeeklyMarketReportGovernmentBond::where('duration', 7)->where('report_id', $report->id)->count();
        if ($bonds > 0) {
            $bonds7['low'] = WeeklyMarketReportGovernmentBond::where('duration', 7)->where('report_id', $report->id)->min('week_close');
            $bonds7['high'] = WeeklyMarketReportGovernmentBond::where('duration', 7)->where('report_id', $report->id)->max('week_close');
            $bonds7['amount'] = WeeklyMarketReportGovernmentBond::where('duration', 7)->where('report_id', $report->id)->sum('amount');
            $bonds7['yield'] = WeeklyMarketReportGovernmentBond::where('duration', 7)->where('report_id', $report->id)->sum('amount_yield') / $bonds7['amount'];
            $this->options['bond7'] = $bonds7;
            $this->options['governmentBonds'] = true;
        }

        $bonds = WeeklyMarketReportGovernmentBond::where('duration', 5)->where('report_id', $report->id)->count();
        if ($bonds > 0) {
            $bonds5['low'] = WeeklyMarketReportGovernmentBond::where('duration', 5)->where('report_id', $report->id)->min('week_close');
            $bonds5['high'] = WeeklyMarketReportGovernmentBond::where('duration', 5)->where('report_id', $report->id)->max('week_close');
            $bonds5['amount'] = WeeklyMarketReportGovernmentBond::where('duration', 5)->where('report_id', $report->id)->sum('amount');
            $bonds5['yield'] = WeeklyMarketReportGovernmentBond::where('duration', 5)->where('report_id', $report->id)->sum('amount_yield') / $bonds5['amount'];
            $this->options['bond5'] = $bonds5;
            $this->options['governmentBonds'] = true;
        }

        $bonds = WeeklyMarketReportGovernmentBond::where('duration', 2)->where('report_id', $report->id)->count();
        if ($bonds > 0) {
            $bonds2['low'] = WeeklyMarketReportGovernmentBond::where('duration', 2)->where('report_id', $report->id)->min('week_close');
            $bonds2['high'] = WeeklyMarketReportGovernmentBond::where('duration', 2)->where('report_id', $report->id)->max('week_close');
            $bonds2['amount'] = WeeklyMarketReportGovernmentBond::where('duration', 2)->where('report_id', $report->id)->sum('amount');
            $bonds2['yield'] = WeeklyMarketReportGovernmentBond::where('duration', 2)->where('report_id', $report->id)->sum('amount_yield') / $bonds2['amount'];
            $this->options['bond2'] = $bonds2;
            $this->options['governmentBonds'] = true;
        }

        $this->options['cooperateBonds'] = WeeklyMarketReportCooperateBond::where('report_id', $report->id)->get();

        $pdf = PDF::loadView('emails.market-reports.weekly-report-pdf', ['options' => $this->options]);

        return $pdf->download('weekly-report.pdf');
    }
}
