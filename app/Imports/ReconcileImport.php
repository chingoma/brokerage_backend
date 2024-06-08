<?php

namespace App\Imports;

use App\Helpers\Helper;
use App\Models\DealingSheet;
use App\Models\Security;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Modules\Orders\Entities\OrderReconcile;

class ReconcileImport implements ToCollection
{
    //    public function mapping(): array
    //    {
    //        return [
    //            'dse_reference'  => 'A1',
    //            'dse_bpid' => 'B1',
    //            'dse_sor_account'  => 'C1',
    //            'dse_client_name' => 'D1',
    //            'dse_trans_type'  => 'E1',
    //            'dse_instrument' => 'F1',
    //            'dse_quantity'  => 'G1',
    //            'dse_price' => 'H1',
    //            'dse_amount'  => 'I1',
    //            'dse_status' => 'J1',
    //            'dse_trade_date'  => 'K1',
    //            'dse_settlement_date' => 'L1'
    //        ];
    //    }

    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {

            $flag = Security::where('type', 'security')->where('name', $row[5])->first();

            if ($flag && $row[7] > 0) {
                $data = OrderReconcile::where('slip_no', $row[0])->first();

                if (empty($data)) {
                    $data = new OrderReconcile();
                }

                $order = DealingSheet::where('slip_no', $row[0])->first();

                $data->bbo_present = 'NO';
                $data->dse_present = 'YES';

                if (! empty($order)) {
                    $data->bbo_present = 'YES';
                    $data->client_id = $order->client_id;
                    $data->amount = $order->amount;
                    $data->type = $order->type;
                    $data->trade_date = $order->trade_date;
                    $data->price = $order->price;
                    $data->status = $order->status;
                    $data->volume = $order->executed;
                    $data->security_id = $order->security_id;

                    $data->vat = $order->vat;
                    $data->brokerage = $order->brokerage;
                    $data->total_commissions = $order->total_commissions;
                    $data->cmsa = $order->cmsa;
                    $data->dse = $order->dse;
                    $data->closed = $order->closed;
                    $data->fidelity = $order->fidelity;
                    $data->total_fees = $order->total_fees;
                    $data->cds = $order->cds;
                    $data->commission_step_one = $order->commission_step_one;
                    $data->commission_step_two = $order->commission_step_two;
                    $data->commission_step_three = $order->commission_step_three;
                    $data->payout = $order->payout;
                    $data->executed = $order->executed;
                    $data->balance = $order->balance;
                    $data->order_id = $order->order_id;
                }

                $data->slip_no = $row[0];
                $data->dse_bpid = $row[1];
                $data->dse_sor_account = $row[2];
                $data->dse_client_name = $row[3];
                $data->dse_trans_type = $row[4];
                $data->dse_instrument = $row[5];
                $data->dse_quantity = $row[6];
                $data->dse_price = $row[7];
                $data->dse_total = $row[8];
                $data->dse_status = $row[9];
                $date = ($row[10] - 25569) * 86400;
                $data->dse_trade_date = date('Y-m-d', $date);
                $date = ($row[11] - 25569) * 86400;
                $data->dse_settlement_date = date('Y-m-d', $date);

                $data->financial_year_id = Helper::business()->financial_year;

                //            $data->slip_no = $row['dse_reference'];
                //            $data->dse_bpid = $row['dse_bpid'];
                //            $data->dse_sor_account = $row['dse_sor_account'];
                //            $data->dse_client_name = $row['dse_client_name'];
                //            $data->dse_trans_type = $row['dse_client_name'];
                //            $data->dse_instrument = $row['dse_instrument'];
                //            $data->dse_quantity = $row['dse_quantity'];
                //            $data->dse_price = $row['dse_price'];
                //            $data->dse_amount = $row['dse_amount'];
                //            $data->dse_status = $row['dse_status'];
                //            $data->dse_trade_date = $row['dse_trade_date'];
                //            $data->dse_settlement_date = $row['dse_settlement_date'];

                $data->save();
            }
        }
    }
}
