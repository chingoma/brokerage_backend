<?php

namespace App\Mail\MarketReports;

use App\Models\MarketReports\Weekly\WeeklyMarketReport;
use App\Models\MarketReports\Weekly\WeeklyMarketReportCooperateBond;
use App\Models\MarketReports\Weekly\WeeklyMarketReportEquityOverview;
use App\Models\MarketReports\Weekly\WeeklyMarketReportEquitySummary;
use App\Models\MarketReports\Weekly\WeeklyMarketReportGovernmentBond;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WeeklyReportsMailable extends Mailable
{
    use Queueable, SerializesModels;

    public string $attachment = '';

    public mixed $options = [];

    /**
     * The report instance.
     */
    public WeeklyMarketReport $report;

    /**
     * Create a new message instance.
     */
    public function __construct(WeeklyMarketReport $report, array $options = [])
    {
        $this->report = $report;
        $this->options = $options;
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
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this->markdown('emails.market-reports.weekly-report')->replyTo('support@alphacapital.co.tz');
    }

    public function getAttachment(): string
    {
        return $this->attachment;
    }

    public function setAttachment(string $attachment)
    {
        $this->attachment = $attachment;
    }
}
