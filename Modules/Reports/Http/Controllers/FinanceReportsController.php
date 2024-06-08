<?php

namespace Modules\Reports\Http\Controllers;

use App\Helpers\Clients\Profile;
use App\Helpers\Helper;
use App\Helpers\Pdfs\CustomerHoldingReportPdf;
use App\Http\Controllers\Controller;
use App\Models\DealingSheet;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\NoReturn;
use Modules\CRM\Entities\Wallet;
use Modules\Custodians\Entities\Custodian;
use Modules\Reports\Exports\CustodianReport\CustodianReportReportExport;
use Modules\Reports\Exports\FlexcubeEntries\FlexcubeEntriesReportExport;
use Modules\Reports\Exports\SettlementReport\SettlementReportReportExport;
use Modules\Reports\Exports\TradeRegisters\TradeRegisterReportExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class FinanceReportsController extends Controller
{
    public function settings(Request $request)
    {
        //        $settings['customers'] = Wallet::customers()->get();
        $settings['custodians'] = Custodian::get();

        return response()->json($settings);
    }

    public function trade_register_report_export(Request $request): BinaryFileResponse
    {
        return (new TradeRegisterReportExport)->from($request->from)->end($request->end)->download('trade-register-report.xlsx');
    }

    public function settlement_report_export(Request $request): BinaryFileResponse
    {
        return (new SettlementReportReportExport)->from($request->from)->end($request->end)->download('trade-register-report.xlsx');
    }

    public function flexcube_entries_report_export(Request $request): BinaryFileResponse
    {
        return (new FlexcubeEntriesReportExport)->from($request->from)->end($request->end)->download('flexcube-entries-report.xlsx');
    }

    public function custodian_report_export(Request $request): BinaryFileResponse
    {
        return (new CustodianReportReportExport($request->custodian))->from($request->from)->end($request->end)->download('trade-register-report.xlsx');
    }

    public function trade_register_report_filter(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : env('PERPAGE');

            $query = DealingSheet::latest('trade_date')->groupBy('reference')->orderBy('uid', 'desc');

            if (! empty($request->status)) {
                $query->whereStatus($request->status);
            }

            if (! empty($request->from) && ! empty($request->end)) {
                $query = $query->whereDate('trade_date', '>=', date('Y-m-d', strtotime($request->from)))
                    ->whereDate('trade_date', '<=', date('Y-m-d', strtotime($request->end)));
            }

            $list = $query->paginate($per_page);

            return response()->json($list);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function settlement_report_filter(Request $request): JsonResponse
    {
        try {
            $per_page = ! empty($request->per_page) ? $request->per_page : env('PERPAGE');

            $query = DealingSheet::latest('settlement_date')->groupBy('reference')->orderBy('uid', 'desc');

            if (! empty($request->status)) {
                $query->whereStatus($request->status);
            }

            if (! empty($request->from) && ! empty($request->end)) {
                $query = $query->whereDate('settlement_date', '>=', date('Y-m-d', strtotime($request->from)))
                    ->whereDate('settlement_date', '<=', date('Y-m-d', strtotime($request->end)));
            }

            $list = $query->paginate($per_page);

            return response()->json($list);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    #[NoReturn]
    public function downloadCustomerHoldingReport(Request $request)
    {
        $client = User::findOrFail($request->id);
        $pdf = new CustomerHoldingReportPdf($client);
        $filename = $pdf->create();
        $this->downloadFile($filename, 'pdf');
    }

    public function customer_holdings(Request $request)
    {

        try {
            $equities = [];
            $bonds = [];
            $summary['total_bond'] = 0;
            $summary['total_equity'] = 0;

            $companies = \DB::table('dealing_sheets')->where('client_id', $request->id)->groupBy('security_id')->pluck('security_id');
            if (! empty($companies)) {
                foreach ($companies as $i => $company) {
                    $equities[$i]['name'] = \DB::table('securities')->find($company)->name ?? '';
                    $equities[$i]['volume'] = Helper::customerCompanyShares($company, $request->id);
                }
            }
            if (! empty($equities)) {
                foreach ($equities as $equity) {
                    $summary['total_equity'] = +$equity['volume'];
                }
            }

            $bondies = \DB::table('bond_orders')->where('client_id', $request->id)->groupBy('bond_id')->pluck('bond_id');
            if (! empty($bondies)) {
                foreach ($bondies as $i => $bondy) {
                    $bData = \DB::table('bonds')->find($bondy);
                    $bonds[$i]['security_name'] = $bData->security_name ?? '';
                    $bonds[$i]['market'] = $bData->market ?? '';
                    $bonds[$i]['category'] = $bData->category ?? '';
                    $bonds[$i]['face_value'] = Helper::customerBondFaceValue($bondy, $request->id);
                }
            }
            if (! empty($bonds)) {
                foreach ($bonds as $bond) {
                    $summary['total_bond'] = +$bond['face_value'];
                }
            }

            $data['customer'] = new Profile($request->id);
            $data['equities'] = $equities;
            $data['bonds'] = $bonds;
            $data['summary'] = $summary;
            $data['available'] = ($data['summary']['total_bond'] + $data['summary']['total_equity']) > 0;

            return response()->json($data);
        } catch (\Throwable $throwable) {
            report($throwable);
            $data['customer'] = new Profile($request->id);
            $data['equities'] = [];
            $data['bonds'] = [];
            $summary['total_bond'] = 0;
            $summary['total_equity'] = 0;
            $data['summary'] = $summary;

            return response()->json($data);
        }
    }

    public function sendCustomerHoldingReport(Request $request)
    {
        try {
            $this->send_customer_holding_report($request->id);

            return response()->json(['status' => true, 'message' => 'Report sent successfully']);
        } catch (\Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }
}
