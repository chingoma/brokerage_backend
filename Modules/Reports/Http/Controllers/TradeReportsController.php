<?php

namespace Modules\Reports\Http\Controllers;

use App\Helpers\Clients\Profile;
use App\Helpers\Helper;
use App\Helpers\Pdfs\CustomerHoldingReportPdf;
use App\Http\Controllers\Controller;
use App\Models\User;
use Goat1000\SVGGraph\SVGGraph;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\NoReturn;
use Modules\Reports\Entities\InvestorPortfolio;
use Modules\Reports\Exports\Holding\MasterHoldingReportExport;
use Modules\Wallet\Entities\Wallet;

class TradeReportsController extends Controller
{
    public function settings(Request $request)
    {
        $settings['customers'] = Wallet::customers()->get();

        return response()->json($settings);
    }

    public function generatePortfolioReport(Request $request)
    {
        $equities = [];
        $summary['total_equity'] = 0;
        $summary['total_equity_value'] = 0;
        $bonds = [];
        $summary['total_bond'] = 0;
        ini_set('max_execution_time', 3600);
        ini_set('memory_limit', '-1');
        $investors = \DB::table("users")
            ->select(["id"])
            ->whereIn("type",["minor","individual","joint","corporate"])
            ->get();
        if(!empty($investors)) {

            foreach ($investors as $investor) {

                $companies = \DB::table('dealing_sheets')
                    ->where('client_id', $investor->id)
                    ->groupBy('security_id')
                    ->pluck('security_id');

                if (count($companies) > 0) {

                    if (!empty($companies)) {
                        $j = 0;
                        foreach ($companies as $i => $company) {
                            $dCompany = \DB::table('securities')->find($company);
                            $volume = Helper::customerCompanyShares($company, $investor->id);
                            $dCompanyD = \DB::table('market_data')->where('company_id', $company)->latest('date')->first();

                            if ($volume > 0) {
                                $equities[$j]['name'] = $dCompany->name ?? '';
                                $equities[$j]['volume'] = $volume;
                                if (empty($dCompanyD)) {
                                    $equities[$j]['market_price'] = 0;
                                    $equities[$j]['market_value'] = $equities[$i]['volume'] * 0;
                                } else {
                                    $equities[$j]['market_price'] = number_format($dCompanyD->close);
                                    $equities[$j]['market_value'] = $equities[$i]['volume'] * $dCompanyD->close;
                                }

                                $j++;
                            }
                        }
                    }
                    if (!empty($equities)) {
                        foreach ($equities as $equity) {
                            $summary['total_equity'] = $summary['total_equity'] + $equity['volume'];
                            $summary['total_equity_value'] = $summary['total_equity_value'] + $equity['market_value'];
                        }
                    }
                }

                $bondies = \DB::table('bond_orders')
                    ->where('client_id', $investor->id)
                    ->groupBy('bond_id')
                    ->pluck('bond_id');

                if (count($bondies) > 0) {
                        if (!empty($bondies)) {
                            $j = 0;
                            foreach ($bondies as $i => $bondy) {
                                $fValue = Helper::customerBondFaceValue($bondy, $investor->id);
                                if ($fValue > 0) {
                                    $bonds[$j]['face_value'] = $fValue;
                                    $j++;
                                }
                            }
                        }

                        if (!empty($bonds)) {
                            foreach ($bonds as $bond) {
                                $summary['total_bond'] = $summary['total_bond'] + $bond['face_value'];
                            }
                        }
                    }


                $investorPortfolio = new InvestorPortfolio();
                $investorPortfolio->investor_id = $investor->id;
                $investorPortfolio->bond = $summary['total_bond'];
                $investorPortfolio->stock = $summary['total_equity'];
                $investorPortfolio->total = $summary['total_bond'] + $summary['total_equity'];
                $investorPortfolio->created_by = auth()->id();
                $investorPortfolio->save();

            }

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

    #[NoReturn]
    public function downloadMasterHoldingReport(Request $request)
    {

        ini_set('max_execution_time', 3600);
        ini_set('memory_limit', '-1');
        return (new MasterHoldingReportExport)->download('master-holding-report.xlsx');

    }

    public function customer_holdings(Request $request)
    {

        try {
            $equities = [];
            $bonds = [];
            $summary['total_bond'] = 0;
            $summary['total_equity'] = 0;
            $summary['total_equity_value'] = 0;

            $companies = \DB::table('dealing_sheets')->where('client_id', $request->id)->groupBy('security_id')->pluck('security_id');
            if (! empty($companies)) {
                foreach ($companies as $i => $company) {
                    $icompany = \DB::table('securities')->find($company);
                    $dcompany = \DB::table('market_data')->where('company_id', $company)->latest('date')->first();
                    $equities[$i]['name'] = $icompany->name ?? '';
                    $equities[$i]['volume'] = Helper::customerCompanyShares($company, $request->id);
                    if (empty($dcompany)) {
                        $equities[$i]['market_price'] = 0;
                        $equities[$i]['market_value'] = 0;
                    } else {
                        $equities[$i]['market_price'] = number_format($dcompany->close ?? 0);
                        $equities[$i]['market_value'] = $equities[$i]['volume'] * $dcompany->close ?? 0;
                    }
                }
            }
            if (! empty($equities)) {
                foreach ($equities as $equity) {
                    $summary['total_equity'] = $summary['total_equity'] + $equity['volume'];
                    $summary['total_equity_value'] = $summary['total_equity_value'] + $equity['market_value'];
                }
            }

            $bondies = \DB::table('bond_executions')->where('client_id', $request->id)->groupBy('bond_id')->pluck('bond_id');
            if (! empty($bondies)) {
                foreach ($bondies as $i => $bondy) {
                    $bData = \DB::table('bonds')->find($bondy);
                    $unit = (strtolower($bData->category) == 'bond') ? 'Yrs' : 'Days';
                    $bonds[$i]['security_name'] = $bData->security_name ?? '';
                    $bonds[$i]['coupon'] = ($bData->coupon ?? '') == 'null' ? '-' : ($bData->coupon ?? '').'%';
                    $bonds[$i]['tenure'] = ($bData->tenure ?? '').' '.$unit;
                    $bonds[$i]['face_value'] = Helper::customerBondFaceValue($bondy, $request->id);
                }
            }

            if (! empty($bonds)) {
                foreach ($bonds as $bond) {
                    $summary['total_bond'] = $summary['total_bond'] + $bond['face_value'];
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

    public function unsent_email_equity()
    {
        try {
            $list = \DB::table('dealing_sheets')
                ->where("price",">",0)
                ->where('dealing_sheets.email_sent', 'no')
                ->selectRaw('dealing_sheets.reference')
                ->selectRaw('dealing_sheets.trade_date as date')
                ->selectRaw('dealing_sheets.id as id')
                ->selectRaw('users.name as client')
                ->selectRaw('users.id as client_id')
                ->selectRaw('dealing_sheets.email_sent')
                ->selectRaw('securities.name as security')
                ->selectRaw('dealing_sheets.type')
                ->selectRaw('dealing_sheets.price')
                ->selectRaw('dealing_sheets.status')
                ->selectRaw('dealing_sheets.executed')
                ->selectRaw('dealing_sheets.payout')
                ->leftJoin('securities', 'dealing_sheets.security_id', '=', 'securities.id')
                ->leftJoin('users', 'dealing_sheets.client_id', '=', 'users.id')
                ->paginate();

            return response()->json($list);
        } catch (\Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }

    public function unsent_email_bond()
    {
        try {
            $list = \DB::table('bond_executions')
                ->where("price",">",0)
                ->where('bond_executions.email_sent', 'no')
                ->selectRaw('bond_executions.trade_date as date')
                ->selectRaw('bond_executions.reference')
                ->selectRaw('bond_executions.id as id')
                ->selectRaw('users.id as client_id')
                ->selectRaw('users.name as client')
                ->selectRaw('bond_executions.email_sent')
                ->selectRaw('bond_executions.status')
                ->selectRaw('bonds.security_name as bond')
                ->selectRaw('bond_executions.type')
                ->selectRaw('bond_executions.price')
                ->selectRaw('bond_executions.executed')
                ->selectRaw('bond_executions.payout')
                ->leftJoin('bonds', 'bond_executions.bond_id', '=', 'bonds.id')
                ->leftJoin('users', 'bond_executions.client_id', '=', 'users.id')
                ->paginate();

            return response()->json($list);
        } catch (\Throwable $throwable) {
            report($throwable);

            return $this->onErrorResponse($throwable->getMessage());
        }
    }
}
