<?php

// Code within app\Helpers\Helper.php

namespace App\Helpers\Clients;

use App\Models\Accounting\Transaction;
use App\Models\DealingSheet;
use App\Models\Statement;
use Modules\Bonds\Entities\BondExecution;

class UsersHelper
{
    public static function wallet_balance($id)
    {
        $statement = Statement::where('client_id', $id)
            ->latest('auto')
            ->limit(1)
            ->first();

        return $statement->balance ?? 0;

        //        $transactions = Transaction::where("category","!=","Custodian")->where("status","approved")->groupBy("reference")->where("client_id",$id)->get();
        //        if(!empty($transactions)){
        //
        //            $balance = 0;
        //
        //            foreach ($transactions as $transaction){
        //
        //                if(strtolower($transaction->category) == "receipt") {
        //                    $balance += $transaction->amount;
        //                }
        //
        //                if(strtolower($transaction->category) == "payment") {
        //                    $balance -= $transaction->amount;
        //                }
        //
        //                if(strtolower($transaction->category) == "invoice") {
        //                    $balance -= $transaction->amount;
        //                }
        //
        //                if(strtolower($transaction->category) == "order") {
        //                    $sheet = DealingSheet::where("status","approved")->where("slip_no",$transaction->reference)->first();
        //                    if(strtolower($sheet->type??"") == "buy"){
        //                        $balance -= $sheet->payout??0;
        //                    }else{
        //                        $balance += $sheet->payout??0;
        //                    }
        //                }
        //
        //                if(strtolower($transaction->category) == "bond") {
        //                    $sheet = BondExecution::where("status","approved")->where("slip_no",$transaction->reference)->first();
        //                    if(strtolower($sheet->type??"") == "buy"){
        //                        $balance -= $sheet->payout??0;
        //                    }else{
        //                        $balance += $sheet->payout??0;
        //                    }
        //                }
        //            }
        //
        //            return round($balance,2);
        //
        //        }else{
        //            return 0;
        //        }
    }

    public static function available_wallet_balance($id)
    {
        $statement = Statement::where('client_id', $id)
            ->latest('auto')
            ->limit(1)
            ->first();
        $wallet = $statement->balance ?? 0;

        return $statement->balance ?? 0;
    }

    //
    //    public static function wallet_balance($id){
    //        $statement = Statement::where("client_id",$id)
    //            ->orderBy("date","desc")
    //            ->limit(1)->first();
    //        return $statement->balance??0;
    //    }
    //
    public static function wallet_status($id)
    {
        $statement = Statement::where('client_id', $id)
            ->latest('auto')
            ->limit(1
            )->first();

        return $statement->state ?? '';
    }
}
