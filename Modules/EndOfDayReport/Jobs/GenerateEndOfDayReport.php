<?php

namespace Modules\EndOfDayReport\Jobs;

use App\Models\Accounting\Transaction;
use App\Models\DealingSheet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Modules\EndOfDayReport\Entities\EndDayReport;
use Modules\EndOfDayReport\Mail\EndOfDayReportFail;
use Modules\Orders\Entities\Order;
use Throwable;

class GenerateEndOfDayReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected EndDayReport $endDayReport;

    protected bool $tradeStatus = true;

    protected bool $financeStatus = true;

    protected bool $systemStatus = false;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(EndDayReport $report)
    {
        //  $this->onQueue("end-of-day-report");
        $this->endDayReport = $report;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $report = EndDayReport::find($this->endDayReport->id);
        $date = EndDayReport::find($report->id)->date;
        $slips = DealingSheet::where('trade_date', $date)->get();
        if (! empty($slips)) {
            foreach ($slips as $slip) {

                $order = Order::find($slip->order_id);

                if (empty($order)) {
                    $this->tradeStatus = false;
                    $this->financeStatus = false;
                    $report->trade_status_description .= ' Order Confirmation No '.$slip->uid.' has no valid Order';
                    $report->finance_status_description .= ' Transaction No '.$slip->uid.' has no valid Order';
                } else {
                    if (strtolower($order->status) == 'pending') {
                        $this->tradeStatus = false;
                        $this->financeStatus = false;
                        $report->trade_status_description .= ' Order No '.$slip->uid.' has Pending status';
                        $report->finance_status_description .= ' Order No '.$slip->uid.' has Pending status';
                    }
                }

                $sumDebit = Transaction::groupBy('reference')->where('reference', $slip->slip_no)->sum('debit');
                $sumCredit = Transaction::groupBy('reference')->where('reference', $slip->slip_no)->sum('credit');
                if (round(floatval($sumCredit)) != round($sumDebit)) {
                    $this->financeStatus = false;
                    $report->finance_status_description .= ' Sum Debit and Sum Credit do not match. sum Credit is '.$sumCredit.' and Sum Debit is '.$sumDebit;
                }

                $report->process_status = $this->systemStatus ? 'pass' : 'fail';
                $report->finance_status = $this->financeStatus ? 'pass' : 'fail';
                $report->trade_status = $this->tradeStatus ? 'pass' : 'fail';
                $report->save();
            }
        } else {
            $report->process_status = 'pass';
            $report->finance_status = 'pass';
            $report->trade_status = 'pass';
            $report->save();
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        $mailable = new EndOfDayReportFail();
        Mail::to('canwork.job@gmail.com')->send($mailable);
    }
}
