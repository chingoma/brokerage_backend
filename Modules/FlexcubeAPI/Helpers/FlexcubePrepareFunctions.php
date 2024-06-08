<?php

namespace Modules\FlexcubeAPI\Helpers;

use App\Helpers\Helper;
use App\Models\Accounting\Transaction;
use App\Models\DealingSheet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Accounting\Entities\SimpleTransaction;
use Modules\Bonds\Entities\BondExecution;
use Modules\FlexcubeAPI\Entities\FlexcubePosting;
use Modules\FlexcubeAPI\Models\TransactionBatch;
use Modules\Payments\Entities\Payment;
use Modules\Receipts\Entities\Receipt;

class FlexcubePrepareFunctions
{

    public static function prepareFlexcubePosting(Transaction $transaction): void
    {
        try {
            $simpleTransaction = SimpleTransaction::where("trans_id",$transaction->id)->first();

            switch (strtolower($transaction->category)) {
                case "order";
                    $dealingSheet = DealingSheet::where("slip_no", $transaction->reference)->firstOrFail();

                    if($dealingSheet->brokerage > 0) {
                        self::postContractNoteStock($simpleTransaction, $transaction, $dealingSheet);
                    }else{
                        self::postContractNoteStockProBook($simpleTransaction, $transaction, $dealingSheet);
                    }

                case "custodian";

                    if(strtolower($transaction->custodian_type) == "equity"){
                        $dealingSheet = DealingSheet::where("slip_no", $transaction->reference)->firstOrFail();

                        if($dealingSheet->brokerage > 0) {
                            self::postContractNoteStock($simpleTransaction, $transaction, $dealingSheet);
                        }else{
                            self::postContractNoteStockProBook($simpleTransaction, $transaction, $dealingSheet);
                        }

                    }else{
                        $dealingSheet = BondExecution::where("slip_no", $transaction->reference)->firstOrFail();

                        if($dealingSheet->brokerage > 0) {

                            self::postContractNoteBond($simpleTransaction, $transaction, $dealingSheet);
                        }else{

                            self::postContractNoteBondProBook($simpleTransaction, $transaction, $dealingSheet);
                        }
                    }
                case "bond";


                    $dealingSheet = BondExecution::where("slip_no", $transaction->reference)->firstOrFail();

                    if($dealingSheet->brokerage > 0) {

                        self::postContractNoteBond($simpleTransaction, $transaction, $dealingSheet);
                    }else{

                        self::postContractNoteBondProBook($simpleTransaction, $transaction, $dealingSheet);
                    }
                case "payment";
                    $payment = Payment::where("reference", $transaction->reference)->firstOrFail();
                    self::postPayment($simpleTransaction,$transaction,$payment);
                case "receipt";
                    $receipt = Receipt::where("reference", $transaction->reference)->firstOrFail();
                    self::postReceipt($simpleTransaction,$transaction,$receipt);
                default;


                    break;
            }
        }catch (\Throwable $throwable){
            report($throwable);
        }
    }

    public static function postContractNoteStock(SimpleTransaction $simpleTransaction, Transaction $transaction, DealingSheet $payloadRaw): void
    {
        if(!empty($payloadRaw->custodian_id)){
            if(strtolower($payloadRaw->type == "sell")){
                self::generatePayloadContractNoteStockCustodianSell($simpleTransaction,$transaction,$payloadRaw);
            }else {
                self::generatePayloadContractNoteStockCustodianBuy($simpleTransaction,$transaction,$payloadRaw);
            }
        }else{
            if(strtolower($payloadRaw->type == "sell")){
                self::generatePayloadContractNoteStockSell($simpleTransaction, $transaction, $payloadRaw);
            }else {
                self::generatePayloadContractNoteStockBuy($simpleTransaction, $transaction, $payloadRaw);
            }
        }
    }

    public static function postContractNoteStockProBook(SimpleTransaction $simpleTransaction, Transaction $transaction, DealingSheet $payloadRaw): void
    {
        if(strtolower($payloadRaw->type) == "sell"){
            self::generatePayloadContractNoteStockProBookSale($simpleTransaction, $transaction, $payloadRaw);
        }else{
            self::generatePayloadContractNoteStockProBookBuy($simpleTransaction, $transaction, $payloadRaw);
        }
    }

    public static function postContractNoteBond(SimpleTransaction $simpleTransaction, Transaction $transaction, BondExecution $payloadRaw): void
    {

        if(!empty($payloadRaw->custodian_id)){
            if(strtolower($payloadRaw->type == "sell")){
                if(strtolower($payloadRaw->market) == "secondary") {
                    self::generatePayloadContractNoteBondCustodianSellSecondary($simpleTransaction, $transaction, $payloadRaw);
                }else{
                    self::generatePayloadContractNoteBondCustodianSellPrimary($simpleTransaction, $transaction, $payloadRaw);
                }
            }else {
                if(strtolower($payloadRaw->market) == "secondary") {
                    self::generatePayloadContractNoteBondCustodianBuySecondary($simpleTransaction, $transaction, $payloadRaw);
                }else{
                    self::generatePayloadContractNoteBondCustodianBuyPrimary($simpleTransaction, $transaction, $payloadRaw);
                }
            }
        }else{
            if(strtolower($payloadRaw->type == "sell")){
                if(strtolower($payloadRaw->market) == "secondary") {
                    self::generatePayloadContractNoteBondSellSecondary($simpleTransaction, $transaction, $payloadRaw);
                }else{
                   self::generatePayloadContractNoteBondSellPrimary($simpleTransaction, $transaction, $payloadRaw);
                }
            }else {
                if(strtolower($payloadRaw->market) == "secondary") {
                    self::generatePayloadContractNoteBondBuySecondary($simpleTransaction, $transaction, $payloadRaw);
                }else{
                    self::generatePayloadContractNoteBondBuyPrimary($simpleTransaction, $transaction, $payloadRaw);
                }
            }
        }


    }

    public static function postPayment(SimpleTransaction $simpleTransaction, Transaction $transaction,Payment $payloadRaw): void
    {
       self::generatePayloadPayment($simpleTransaction,$transaction, $payloadRaw);
    }

    public static function postReceipt(SimpleTransaction $simpleTransaction, Transaction $transaction, Receipt $payloadRaw): void
    {
       self::generatePayloadReceipt($simpleTransaction,$transaction, $payloadRaw);
    }

    private static function generatePayloadContractNoteStockCustodianBuy(SimpleTransaction $simpleTransaction, Transaction $transaction, DealingSheet $payloadRaw): void
    {
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->executed.' @'.$payloadRaw->price;

        $posting = new FlexcubePosting();
        $posting->type = "PURCHASE";
        $posting = new FlexcubePosting();
        $posting->category = "CUSTODIAN STOCK";
        $posting->type = "PURCHASE";
        $posting->entity_id = $payloadRaw->id;
        $posting->tran_reference = $transaction->reference;
        $posting->batch_no = $batch;
        $posting->settlement_date = $payloadRaw->settlement_date;
        $posting->value_date = $payloadRaw->settlement_date;
        $posting->description = $description;
        $posting->main_action = "D";

        $posting->debit = $payloadRaw->total_fees + $payloadRaw->other_charges;

        $posting->credit = $payloadRaw->brokerage + $payloadRaw->vat + $payloadRaw->cmsa + $payloadRaw->fidelity + $payloadRaw->cds + $payloadRaw->dse + $payloadRaw->other_charges;
        $posting->total_charges = $payloadRaw->brokerage + $payloadRaw->vat + $payloadRaw->cmsa + $payloadRaw->fidelity + $payloadRaw->cds + $payloadRaw->dse + $payloadRaw->other_charges;
        $posting->consideration = $payloadRaw->amount;
        $posting->payout = $payloadRaw->payout;

        $posting->debit_credit_diff = $posting->debit - $posting->credit;
        $posting->brokerage_account = self::ledgerMapper("brokerage");
        $posting->brokerage = $payloadRaw->brokerage;

        $posting->vat_account = self::ledgerMapper("vat");
        $posting->vat = $payloadRaw->vat;

        $posting->cmsa_account = self::ledgerMapper("cmsa");
        $posting->cmsa = $payloadRaw->cmsa;

        $posting->dse_account = self::ledgerMapper("dse");
        $posting->dse = $payloadRaw->dse;

        $posting->fidelity_account = self::ledgerMapper("fidelity");
        $posting->fidelity = $payloadRaw->fidelity;

        $posting->cds_account = self::ledgerMapper("cds");
        $posting->cds = $payloadRaw->cds;

        $posting->other_charges_account = self::ledgerMapper("other_charges");
        $posting->other_charges = $payloadRaw->other_charges;

        $posting->quantity = $payloadRaw->executed;
        $posting->price = $payloadRaw->price;
        $posting->save();

        self::finishPreparation($simpleTransaction,$transaction,$payloadRaw);

    }

    private static function generatePayloadContractNoteStockCustodianSell(SimpleTransaction $simpleTransaction, Transaction $transaction, DealingSheet $payloadRaw): void
    {
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->executed.' @'.$payloadRaw->price;

        $posting = new FlexcubePosting();
        $posting->type = "PURCHASE";
        $posting = new FlexcubePosting();
        $posting->category = "CUSTODIAN STOCK";
        $posting->type = "SALE";
        $posting->entity_id = $payloadRaw->id;
        $posting->tran_reference = $transaction->reference;
        $posting->batch_no = $batch;
        $posting->settlement_date = $payloadRaw->settlement_date;
        $posting->value_date = $payloadRaw->settlement_date;
        $posting->description = $description;
        $posting->main_action = "D";

        $posting->debit = $payloadRaw->total_fees + $payloadRaw->other_charges;

        $posting->credit = $payloadRaw->brokerage + $payloadRaw->vat + $payloadRaw->cmsa + $payloadRaw->fidelity + $payloadRaw->cds + $payloadRaw->dse + $payloadRaw->other_charges;
        $posting->total_charges = $payloadRaw->brokerage + $payloadRaw->vat + $payloadRaw->cmsa + $payloadRaw->fidelity + $payloadRaw->cds + $payloadRaw->dse + $payloadRaw->other_charges;
        $posting->consideration = $payloadRaw->amount;
        $posting->payout = $payloadRaw->payout;

        $posting->debit_credit_diff = $posting->debit - $posting->credit;
        $posting->brokerage_account = self::ledgerMapper("brokerage");
        $posting->brokerage = $payloadRaw->brokerage;

        $posting->vat_account = self::ledgerMapper("vat");
        $posting->vat = $payloadRaw->vat;

        $posting->cmsa_account = self::ledgerMapper("cmsa");
        $posting->cmsa = $payloadRaw->cmsa;

        $posting->dse_account = self::ledgerMapper("dse");
        $posting->dse = $payloadRaw->dse;

        $posting->fidelity_account = self::ledgerMapper("fidelity");
        $posting->fidelity = $payloadRaw->fidelity;

        $posting->cds_account = self::ledgerMapper("cds");
        $posting->cds = $payloadRaw->cds;

        $posting->other_charges_account = self::ledgerMapper("other_charges");
        $posting->other_charges = $payloadRaw->other_charges;

        $posting->quantity = $payloadRaw->executed;
        $posting->price = $payloadRaw->price;
        $posting->save();

        self::finishPreparation($simpleTransaction,$transaction,$payloadRaw);

    }

    private static function generatePayloadContractNoteStockSell(SimpleTransaction $simpleTransaction, Transaction $transaction, DealingSheet $payloadRaw): void
    {

        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->executed.' @'.$payloadRaw->price;

        $posting = new FlexcubePosting();
        $posting->type = "PURCHASE";
        $posting = new FlexcubePosting();
        $posting->category = "STOCK";
        $posting->type = "SALE";
        $posting->entity_id = $payloadRaw->id;
        $posting->tran_reference = $transaction->reference;
        $posting->batch_no = $batch;
        $posting->settlement_date = $payloadRaw->settlement_date;
        $posting->value_date = $payloadRaw->settlement_date;
        $posting->description = $description;
        $posting->main_action = "D";

        $posting->debit = $payloadRaw->amount -  ($payloadRaw->total_fees + $payloadRaw->other_charges);
        $posting->credit = $payloadRaw->payout;

        $posting->total_charges = $payloadRaw->total_fees;
        $posting->consideration = $payloadRaw->amount;
        $posting->payout = $payloadRaw->payout;

        $posting->debit_credit_diff = $posting->debit - $posting->credit;
        $posting->brokerage_account = self::ledgerMapper("brokerage");
        $posting->brokerage = $payloadRaw->brokerage;

        $posting->vat_account = self::ledgerMapper("vat");
        $posting->vat = $payloadRaw->vat;

        $posting->cmsa_account = self::ledgerMapper("cmsa");
        $posting->cmsa = $payloadRaw->cmsa;

        $posting->dse_account = self::ledgerMapper("dse");
        $posting->dse = $payloadRaw->dse;

        $posting->fidelity_account = self::ledgerMapper("fidelity");
        $posting->fidelity = $payloadRaw->fidelity;

        $posting->cds_account = self::ledgerMapper("cds");
        $posting->cds = $payloadRaw->cds;

        $posting->other_charges_account = self::ledgerMapper("other_charges");
        $posting->other_charges = $payloadRaw->other_charges;

        $posting->quantity = $payloadRaw->executed;
        $posting->price = $payloadRaw->price;

        $posting->save();

        self::finishPreparation($simpleTransaction,$transaction,$payloadRaw);

    }

    private static function generatePayloadContractNoteStockBuy(SimpleTransaction $simpleTransaction, Transaction $transaction, DealingSheet $payloadRaw):void
    {

        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->executed.' @'.$payloadRaw->price;

        $posting = new FlexcubePosting();
        $posting->type = "PURCHASE";
        $posting = new FlexcubePosting();
        $posting->category = "STOCK";
        $posting->type = "PURCHASE";
        $posting->entity_id = $payloadRaw->id;
        $posting->tran_reference = $transaction->reference;
        $posting->batch_no = $batch;
        $posting->settlement_date = $payloadRaw->settlement_date;
        $posting->value_date = $payloadRaw->settlement_date;
        $posting->description = $description;
        $posting->main_action = "D";

        $posting->debit = $payloadRaw->amount + $payloadRaw->total_fees + $payloadRaw->other_charges;
        $posting->credit = $payloadRaw->payout;

        $posting->total_charges = $payloadRaw->total_fees;
        $posting->consideration = $payloadRaw->amount;
        $posting->payout = $payloadRaw->payout;

        $posting->debit_credit_diff = $posting->debit - $posting->credit;
        $posting->brokerage_account = self::ledgerMapper("brokerage");
        $posting->brokerage = $payloadRaw->brokerage;

        $posting->vat_account = self::ledgerMapper("vat");
        $posting->vat = $payloadRaw->vat;

        $posting->cmsa_account = self::ledgerMapper("cmsa");
        $posting->cmsa = $payloadRaw->cmsa;

        $posting->dse_account = self::ledgerMapper("dse");
        $posting->dse = $payloadRaw->dse;

        $posting->fidelity_account = self::ledgerMapper("fidelity");
        $posting->fidelity = $payloadRaw->fidelity;

        $posting->cds_account = self::ledgerMapper("cds");
        $posting->cds = $payloadRaw->cds;

        $posting->other_charges_account = self::ledgerMapper("other_charges");
        $posting->other_charges = $payloadRaw->other_charges;

        $posting->quantity = $payloadRaw->executed;
        $posting->price = $payloadRaw->price;

        $posting->save();

        self::finishPreparation($simpleTransaction,$transaction,$payloadRaw);
    }

    private static function generatePayloadContractNoteStockProBookBuy(SimpleTransaction $simpleTransaction, Transaction $transaction, DealingSheet $payloadRaw): void
    {

        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->executed.' @'.$payloadRaw->price;
        $posting = new FlexcubePosting();
        $posting->type = "PURCHASE";
        $posting = new FlexcubePosting();
        $posting->category = "PROBOOK STOCK";
        $posting->type = "PURCHASE";
        $posting->entity_id = $payloadRaw->id;
        $posting->tran_reference = $transaction->reference;
        $posting->batch_no = $batch;
        $posting->settlement_date = $payloadRaw->settlement_date;
        $posting->value_date = $payloadRaw->settlement_date;
        $posting->description = $description;
        $posting->main_action = "D";

        $posting->debit = $payloadRaw->amount + $payloadRaw->total_fees + $payloadRaw->other_charges;
        $posting->credit = $payloadRaw->payout;

        $posting->total_charges = $payloadRaw->total_fees;
        $posting->consideration = $payloadRaw->amount;
        $posting->payout = $payloadRaw->payout;

        $posting->debit_credit_diff = $posting->debit - $posting->credit;
        $posting->brokerage_account = self::ledgerMapper("brokerage");
        $posting->brokerage = $payloadRaw->brokerage;

        $posting->vat_account = self::ledgerMapper("vat");
        $posting->vat = $payloadRaw->vat;

        $posting->cmsa_account = self::ledgerMapper("cmsa");
        $posting->cmsa = $payloadRaw->cmsa;

        $posting->dse_account = self::ledgerMapper("dse");
        $posting->dse = $payloadRaw->dse;

        $posting->fidelity_account = self::ledgerMapper("fidelity");
        $posting->fidelity = $payloadRaw->fidelity;

        $posting->cds_account = self::ledgerMapper("cds");
        $posting->cds = $payloadRaw->cds;

        $posting->other_charges_account = self::ledgerMapper("other_charges");
        $posting->other_charges = $payloadRaw->other_charges;

        $posting->quantity = $payloadRaw->executed;
        $posting->price = $payloadRaw->price;

        $posting->save();

        self::finishPreparation($simpleTransaction,$transaction,$payloadRaw);
    }

    private static function generatePayloadContractNoteStockProBookSale(SimpleTransaction $simpleTransaction, Transaction $transaction, DealingSheet $payloadRaw): void
    {

        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->executed.' @'.$payloadRaw->price;

        $posting = new FlexcubePosting();
        $posting->type = "PURCHASE";
        $posting = new FlexcubePosting();
        $posting->category = "PROBOOK STOCK";
        $posting->type = "SALE";
        $posting->entity_id = $payloadRaw->id;
        $posting->tran_reference = $transaction->reference;
        $posting->batch_no = $batch;
        $posting->settlement_date = $payloadRaw->settlement_date;
        $posting->value_date = $payloadRaw->settlement_date;
        $posting->description = $description;
        $posting->main_action = "D";

        $posting->debit = $payloadRaw->amount - $payloadRaw->total_fees + $payloadRaw->other_charges;
        $posting->credit = $payloadRaw->payout;

        $posting->total_charges = $payloadRaw->total_fees;
        $posting->consideration = $payloadRaw->amount;
        $posting->payout = $payloadRaw->payout;

        $posting->debit_credit_diff = $posting->debit - $posting->credit;
        $posting->brokerage_account = self::ledgerMapper("brokerage");
        $posting->brokerage = $payloadRaw->brokerage;

        $posting->vat_account = self::ledgerMapper("vat");
        $posting->vat = $payloadRaw->vat;

        $posting->cmsa_account = self::ledgerMapper("cmsa");
        $posting->cmsa = $payloadRaw->cmsa;

        $posting->dse_account = self::ledgerMapper("dse");
        $posting->dse = $payloadRaw->dse;

        $posting->fidelity_account = self::ledgerMapper("fidelity");
        $posting->fidelity = $payloadRaw->fidelity;

        $posting->cds_account = self::ledgerMapper("cds");
        $posting->cds = $payloadRaw->cds;

        $posting->other_charges_account = self::ledgerMapper("other_charges");
        $posting->other_charges = $payloadRaw->other_charges;

        $posting->quantity = $payloadRaw->executed;
        $posting->price = $payloadRaw->price;

        $posting->save();

        self::finishPreparation($simpleTransaction,$transaction,$payloadRaw);
    }

    private static function generatePayloadPayment(SimpleTransaction $simpleTransaction, Transaction $transaction,Payment $payloadRaw): void
    {

        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars;
        $customer = DB::table("users")->find($payloadRaw->client_id);


        $posting = new FlexcubePosting();
        $posting->type = "PURCHASE";
        $posting = new FlexcubePosting();
        $posting->category = "PAYMENT";
        $posting->type = "WITHDRAW";
        $posting->entity_id = $payloadRaw->id;
        $posting->tran_reference = $transaction->reference;
        $posting->batch_no = $batch;
        $posting->settlement_date = $payloadRaw->date;
        $posting->value_date = $payloadRaw->date;
        $posting->description = $description;
        $posting->main_action = "D";

        $posting->debit = $payloadRaw->amount;
        $posting->debit_account = "0009100022602";
        $posting->credit = $payloadRaw->amount;
        $posting->credit_account = $customer->flex_acc_no;

        $posting->save();

        self::finishPreparation($simpleTransaction,$transaction,$payloadRaw);
    }

    private static function generatePayloadReceipt(SimpleTransaction $simpleTransaction, Transaction $transaction, Receipt $payloadRaw): void
    {
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars;
        $customer = DB::table("users")->find($payloadRaw->client_id);
        $posting = new FlexcubePosting();
        $posting->type = "PURCHASE";
        $posting = new FlexcubePosting();
        $posting->category = "RECEIPT";
        $posting->type = "DEPOSIT";
        $posting->entity_id = $payloadRaw->id;
        $posting->tran_reference = $transaction->reference;
        $posting->batch_no = $batch;
        $posting->settlement_date = $payloadRaw->date;
        $posting->value_date = $payloadRaw->date;
        $posting->description = $description;
        $posting->main_action = "D";

        $posting->debit = $payloadRaw->amount;
        $posting->debit_account = $customer->flex_acc_no;
        $posting->credit = $payloadRaw->amount;
        $posting->credit_account = "0009100022602";

        $posting->save();

        self::finishPreparation($simpleTransaction,$transaction,$payloadRaw);
    }

    private static function generatePayloadContractNoteBondBuySecondary(SimpleTransaction $simpleTransaction, Transaction $transaction,BondExecution $payloadRaw): void
    {
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->face_value.' @'.$payloadRaw->price;

        $posting = new FlexcubePosting();
        $posting->type = "PURCHASE";
        $posting = new FlexcubePosting();
        $posting->category = "BOND";
        $posting->type = "PURCHASE";
        $posting->bond_type = strtoupper($payloadRaw->market);
        $posting->entity_id = $payloadRaw->id;
        $posting->tran_reference = $transaction->reference;
        $posting->batch_no = $batch;
        $posting->settlement_date = $payloadRaw->settlement_date;
        $posting->value_date = $payloadRaw->settlement_date;
        $posting->description = $description;
        $posting->main_action = "D";

        $posting->debit = $payloadRaw->amount + $payloadRaw->total_fees + $payloadRaw->other_charges;
        $posting->credit = $payloadRaw->payout;

        $posting->total_charges = $payloadRaw->total_fees;
        $posting->consideration = $payloadRaw->amount;
        $posting->payout = $payloadRaw->payout;

        $posting->debit_credit_diff = $posting->debit - $posting->credit;
        $posting->brokerage_account = self::ledgerMapper("brokerage");
        $posting->brokerage = $payloadRaw->brokerage;

        $posting->vat_account = self::ledgerMapper("vat");
        $posting->vat = $payloadRaw->vat;

        $posting->cmsa_account = self::ledgerMapper("cmsa");
        $posting->cmsa = $payloadRaw->cmsa;

        $posting->dse_account = self::ledgerMapper("dse");
        $posting->dse = $payloadRaw->dse;

        $posting->fidelity_account = self::ledgerMapper("fidelity");
        $posting->fidelity = $payloadRaw->fidelity;

        $posting->cds_account = self::ledgerMapper("cds");
        $posting->cds = $payloadRaw->cds;

        $posting->other_charges_account = self::ledgerMapper("other_charges");
        $posting->other_charges = $payloadRaw->other_charges;

        $posting->quantity = $payloadRaw->executed;
        $posting->price = $payloadRaw->price;

        $posting->save();

        self::finishPreparation($simpleTransaction,$transaction,$payloadRaw);

    }

    private static function generatePayloadContractNoteBondBuyPrimary(SimpleTransaction $simpleTransaction, Transaction $transaction,BondExecution $payloadRaw): void
    {
        \Illuminate\Support\Facades\Log::error($payloadRaw);
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->face_value.' @'.$payloadRaw->price;

        $posting = new FlexcubePosting();
        $posting->type = "PURCHASE";
        $posting = new FlexcubePosting();
        $posting->category = "BOND";
        $posting->type = "SALE";
        $posting->bond_type = strtoupper($payloadRaw->market);
        $posting->entity_id = $payloadRaw->id;
        $posting->tran_reference = $transaction->reference;
        $posting->batch_no = $batch;
        $posting->settlement_date = $payloadRaw->settlement_date;
        $posting->value_date = $payloadRaw->settlement_date;
        $posting->description = $description;
        $posting->main_action = "D";

        $posting->debit = $payloadRaw->amount + $payloadRaw->total_fees + $payloadRaw->other_charges;
        $posting->credit = $payloadRaw->payout;

        $posting->total_charges = $payloadRaw->total_fees;
        $posting->consideration = $payloadRaw->amount;
        $posting->payout = $payloadRaw->payout;

        $posting->debit_credit_diff = $posting->debit - $posting->credit;
        $posting->brokerage_account = self::ledgerMapper("brokerage");
        $posting->brokerage = $payloadRaw->brokerage;

        $posting->vat_account = self::ledgerMapper("vat");
        $posting->vat = $payloadRaw->vat;

        $posting->cmsa_account = self::ledgerMapper("cmsa");
        $posting->cmsa = $payloadRaw->cmsa;

        $posting->dse_account = self::ledgerMapper("dse");
        $posting->dse = $payloadRaw->dse;

        $posting->fidelity_account = self::ledgerMapper("fidelity");
        $posting->fidelity = $payloadRaw->fidelity;

        $posting->cds_account = self::ledgerMapper("cds");
        $posting->cds = $payloadRaw->cds;

        $posting->other_charges_account = self::ledgerMapper("other_charges");
        $posting->other_charges = $payloadRaw->other_charges;

        $posting->quantity = $payloadRaw->executed;
        $posting->price = $payloadRaw->price;

        $posting->save();

        self::finishPreparation($simpleTransaction,$transaction,$payloadRaw);
    }

    private static function generatePayloadContractNoteBondSellSecondary(SimpleTransaction $simpleTransaction, Transaction $transaction,BondExecution $payloadRaw): void
    {
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->face_value.' @'.$payloadRaw->price;

        $posting = new FlexcubePosting();
        $posting->type = "PURCHASE";
        $posting = new FlexcubePosting();
        $posting->category = "BOND";
        $posting->type = "SALE";
        $posting->bond_type = strtoupper($payloadRaw->market);
        $posting->entity_id = $payloadRaw->id;
        $posting->tran_reference = $transaction->reference;
        $posting->batch_no = $batch;
        $posting->settlement_date = $payloadRaw->settlement_date;
        $posting->value_date = $payloadRaw->settlement_date;
        $posting->description = $description;
        $posting->main_action = "D";

        $posting->debit = $payloadRaw->amount - $payloadRaw->total_fees + $payloadRaw->other_charges;
        $posting->credit = $payloadRaw->payout;

        $posting->total_charges = $payloadRaw->total_fees;
        $posting->consideration = $payloadRaw->amount;
        $posting->payout = $payloadRaw->payout;

        $posting->debit_credit_diff = $posting->debit - $posting->credit;
        $posting->brokerage_account = self::ledgerMapper("brokerage");
        $posting->brokerage = $payloadRaw->brokerage;

        $posting->vat_account = self::ledgerMapper("vat");
        $posting->vat = $payloadRaw->vat;

        $posting->cmsa_account = self::ledgerMapper("cmsa");
        $posting->cmsa = $payloadRaw->cmsa;

        $posting->dse_account = self::ledgerMapper("dse");
        $posting->dse = $payloadRaw->dse;

        $posting->fidelity_account = self::ledgerMapper("fidelity");
        $posting->fidelity = $payloadRaw->fidelity;

        $posting->cds_account = self::ledgerMapper("cds");
        $posting->cds = $payloadRaw->cds;

        $posting->other_charges_account = self::ledgerMapper("other_charges");
        $posting->other_charges = $payloadRaw->other_charges;

        $posting->quantity = $payloadRaw->executed;
        $posting->price = $payloadRaw->price;

        $posting->save();

        self::finishPreparation($simpleTransaction,$transaction,$payloadRaw);
    }

    private static function generatePayloadContractNoteBondSellPrimary(SimpleTransaction $simpleTransaction, Transaction $transaction,BondExecution $payloadRaw): void
    {
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->face_value.' @'.$payloadRaw->price;

        $posting = new FlexcubePosting();
        $posting->type = "PURCHASE";
        $posting = new FlexcubePosting();
        $posting->category = "BOND";
        $posting->type = "SALE";
        $posting->bond_type = strtoupper($payloadRaw->market);
        $posting->entity_id = $payloadRaw->id;
        $posting->tran_reference = $transaction->reference;
        $posting->batch_no = $batch;
        $posting->settlement_date = $payloadRaw->settlement_date;
        $posting->value_date = $payloadRaw->settlement_date;
        $posting->description = $description;
        $posting->main_action = "D";

        $posting->debit = $payloadRaw->amount - $payloadRaw->total_fees + $payloadRaw->other_charges;
        $posting->credit = $payloadRaw->payout;

        $posting->total_charges = $payloadRaw->total_fees;
        $posting->consideration = $payloadRaw->amount;
        $posting->payout = $payloadRaw->payout;

        $posting->debit_credit_diff = $posting->debit - $posting->credit;
        $posting->brokerage_account = self::ledgerMapper("brokerage");
        $posting->brokerage = $payloadRaw->brokerage;

        $posting->vat_account = self::ledgerMapper("vat");
        $posting->vat = $payloadRaw->vat;

        $posting->cmsa_account = self::ledgerMapper("cmsa");
        $posting->cmsa = $payloadRaw->cmsa;

        $posting->dse_account = self::ledgerMapper("dse");
        $posting->dse = $payloadRaw->dse;

        $posting->fidelity_account = self::ledgerMapper("fidelity");
        $posting->fidelity = $payloadRaw->fidelity;

        $posting->cds_account = self::ledgerMapper("cds");
        $posting->cds = $payloadRaw->cds;

        $posting->other_charges_account = self::ledgerMapper("other_charges");
        $posting->other_charges = $payloadRaw->other_charges;

        $posting->quantity = $payloadRaw->executed;
        $posting->price = $payloadRaw->price;

        $posting->save();

        self::finishPreparation($simpleTransaction,$transaction,$payloadRaw);
    }

    private static function generatePayloadContractNoteBondCustodianBuySecondary(SimpleTransaction $simpleTransaction, Transaction $transaction, BondExecution $payloadRaw): void
    {
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->face_value.' @'.$payloadRaw->price;

        $posting = new FlexcubePosting();
        $posting->type = "PURCHASE";
        $posting = new FlexcubePosting();
        $posting->category = "CUSTODIAN BOND";
        $posting->type = "PURCHASE";
        $posting->bond_type = strtoupper($payloadRaw->market);
        $posting->entity_id = $payloadRaw->id;
        $posting->tran_reference = $transaction->reference;
        $posting->batch_no = $batch;
        $posting->settlement_date = $payloadRaw->settlement_date;
        $posting->value_date = $payloadRaw->settlement_date;
        $posting->description = $description;
        $posting->main_action = "D";

        $posting->debit = $payloadRaw->amount + $payloadRaw->total_fees + $payloadRaw->other_charges;
        $posting->credit = $payloadRaw->payout;

        $posting->total_charges = $payloadRaw->total_fees;
        $posting->consideration = $payloadRaw->amount;
        $posting->payout = $payloadRaw->payout;

        $posting->debit_credit_diff = $posting->debit - $posting->credit;
        $posting->brokerage_account = self::ledgerMapper("brokerage");
        $posting->brokerage = $payloadRaw->brokerage;

        $posting->vat_account = self::ledgerMapper("vat");
        $posting->vat = $payloadRaw->vat;

        $posting->cmsa_account = self::ledgerMapper("cmsa");
        $posting->cmsa = $payloadRaw->cmsa;

        $posting->dse_account = self::ledgerMapper("dse");
        $posting->dse = $payloadRaw->dse;

        $posting->fidelity_account = self::ledgerMapper("fidelity");
        $posting->fidelity = $payloadRaw->fidelity;

        $posting->cds_account = self::ledgerMapper("cds");
        $posting->cds = $payloadRaw->cds;

        $posting->other_charges_account = self::ledgerMapper("other_charges");
        $posting->other_charges = $payloadRaw->other_charges;

        $posting->quantity = $payloadRaw->executed;
        $posting->price = $payloadRaw->price;

        $posting->save();

        self::finishPreparation($simpleTransaction,$transaction,$payloadRaw);
    }

    private static function generatePayloadContractNoteBondCustodianBuyPrimary(SimpleTransaction $simpleTransaction, Transaction $transaction, BondExecution $payloadRaw): void
    {
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->face_value.' @'.$payloadRaw->price;

        $posting = new FlexcubePosting();
        $posting->type = "PURCHASE";
        $posting = new FlexcubePosting();
        $posting->category = "CUSTODIAN BOND";
        $posting->type = "PURCHASE";
        $posting->bond_type = strtoupper($payloadRaw->market);
        $posting->entity_id = $payloadRaw->id;
        $posting->tran_reference = $transaction->reference;
        $posting->batch_no = $batch;
        $posting->settlement_date = $payloadRaw->settlement_date;
        $posting->value_date = $payloadRaw->settlement_date;
        $posting->description = $description;
        $posting->main_action = "D";

        $posting->debit = $payloadRaw->amount + $payloadRaw->total_fees + $payloadRaw->other_charges;
        $posting->credit = $payloadRaw->payout;

        $posting->total_charges = $payloadRaw->total_fees;
        $posting->consideration = $payloadRaw->amount;
        $posting->payout = $payloadRaw->payout;

        $posting->debit_credit_diff = $posting->debit - $posting->credit;
        $posting->brokerage_account = self::ledgerMapper("brokerage");
        $posting->brokerage = $payloadRaw->brokerage;

        $posting->vat_account = self::ledgerMapper("vat");
        $posting->vat = $payloadRaw->vat;

        $posting->cmsa_account = self::ledgerMapper("cmsa");
        $posting->cmsa = $payloadRaw->cmsa;

        $posting->dse_account = self::ledgerMapper("dse");
        $posting->dse = $payloadRaw->dse;

        $posting->fidelity_account = self::ledgerMapper("fidelity");
        $posting->fidelity = $payloadRaw->fidelity;

        $posting->cds_account = self::ledgerMapper("cds");
        $posting->cds = $payloadRaw->cds;

        $posting->other_charges_account = self::ledgerMapper("other_charges");
        $posting->other_charges = $payloadRaw->other_charges;

        $posting->quantity = $payloadRaw->executed;
        $posting->price = $payloadRaw->price;

        $posting->save();

        self::finishPreparation($simpleTransaction,$transaction,$payloadRaw);
    }

    private static function generatePayloadContractNoteBondCustodianSellSecondary(SimpleTransaction $simpleTransaction, Transaction $transaction, BondExecution $payloadRaw): void
    {
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->face_value.' @'.$payloadRaw->price;

        $posting = new FlexcubePosting();
        $posting->type = "PURCHASE";
        $posting = new FlexcubePosting();
        $posting->category = "CUSTODIAN BOND";
        $posting->type = "SALE";
        $posting->bond_type = strtoupper($payloadRaw->market);
        $posting->entity_id = $payloadRaw->id;
        $posting->tran_reference = $transaction->reference;
        $posting->batch_no = $batch;
        $posting->settlement_date = $payloadRaw->settlement_date;
        $posting->value_date = $payloadRaw->settlement_date;
        $posting->description = $description;
        $posting->main_action = "D";

        $posting->debit = $payloadRaw->amount - $payloadRaw->total_fees + $payloadRaw->other_charges;
        $posting->credit = $payloadRaw->payout;

        $posting->total_charges = $payloadRaw->total_fees;
        $posting->consideration = $payloadRaw->amount;
        $posting->payout = $payloadRaw->payout;

        $posting->debit_credit_diff = $posting->debit - $posting->credit;
        $posting->brokerage_account = self::ledgerMapper("brokerage");
        $posting->brokerage = $payloadRaw->brokerage;

        $posting->vat_account = self::ledgerMapper("vat");
        $posting->vat = $payloadRaw->vat;

        $posting->cmsa_account = self::ledgerMapper("cmsa");
        $posting->cmsa = $payloadRaw->cmsa;

        $posting->dse_account = self::ledgerMapper("dse");
        $posting->dse = $payloadRaw->dse;

        $posting->fidelity_account = self::ledgerMapper("fidelity");
        $posting->fidelity = $payloadRaw->fidelity;

        $posting->cds_account = self::ledgerMapper("cds");
        $posting->cds = $payloadRaw->cds;

        $posting->other_charges_account = self::ledgerMapper("other_charges");
        $posting->other_charges = $payloadRaw->other_charges;

        $posting->quantity = $payloadRaw->executed;
        $posting->price = $payloadRaw->price;

        $posting->save();

        self::finishPreparation($simpleTransaction,$transaction,$payloadRaw);
    }

    private static function generatePayloadContractNoteBondCustodianSellPrimary(SimpleTransaction $simpleTransaction, Transaction $transaction, BondExecution $payloadRaw): void
    {
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->face_value.' @'.$payloadRaw->price;

        $posting = new FlexcubePosting();
        $posting->type = "PURCHASE";
        $posting = new FlexcubePosting();
        $posting->category = "CUSTODIAN BOND";
        $posting->type = "SALE";
        $posting->bond_type = strtoupper($payloadRaw->market);
        $posting->entity_id = $payloadRaw->id;
        $posting->tran_reference = $transaction->reference;
        $posting->batch_no = $batch;
        $posting->settlement_date = $payloadRaw->settlement_date;
        $posting->value_date = $payloadRaw->settlement_date;
        $posting->description = $description;
        $posting->main_action = "D";

        $posting->debit = $payloadRaw->amount - $payloadRaw->total_fees + $payloadRaw->other_charges;
        $posting->credit = $payloadRaw->payout;

        $posting->total_charges = $payloadRaw->total_fees;
        $posting->consideration = $payloadRaw->amount;
        $posting->payout = $payloadRaw->payout;

        $posting->debit_credit_diff = $posting->debit - $posting->credit;
        $posting->brokerage_account = self::ledgerMapper("brokerage");
        $posting->brokerage = $payloadRaw->brokerage;

        $posting->vat_account = self::ledgerMapper("vat");
        $posting->vat = $payloadRaw->vat;

        $posting->cmsa_account = self::ledgerMapper("cmsa");
        $posting->cmsa = $payloadRaw->cmsa;

        $posting->dse_account = self::ledgerMapper("dse");
        $posting->dse = $payloadRaw->dse;

        $posting->fidelity_account = self::ledgerMapper("fidelity");
        $posting->fidelity = $payloadRaw->fidelity;

        $posting->cds_account = self::ledgerMapper("cds");
        $posting->cds = $payloadRaw->cds;

        $posting->other_charges_account = self::ledgerMapper("other_charges");
        $posting->other_charges = $payloadRaw->other_charges;

        $posting->quantity = $payloadRaw->executed;
        $posting->price = $payloadRaw->price;

        $posting->save();

        self::finishPreparation($simpleTransaction,$transaction,$payloadRaw);
    }

    public static function postContractNoteBondProBook(SimpleTransaction $simpleTransaction, Transaction $transaction, BondExecution $payloadRaw): void
    {
        if(!empty($payloadRaw->custodian_id)){
         //todo probook custodian


        }else{

            if(strtolower($payloadRaw->type == "sell")){

                if(strtolower($payloadRaw->market) == "secondary") {

                    self::generatePayloadContractNoteBondSellProBookSecondary($simpleTransaction, $transaction, $payloadRaw);
                }else{
                    self::generatePayloadContractNoteBondSellProBookPrimary($simpleTransaction, $transaction, $payloadRaw);
                }
            }else {
                if(strtolower($payloadRaw->market) == "secondary") {
                    self::generatePayloadContractNoteBondBuyProBookSecondary($simpleTransaction, $transaction, $payloadRaw);
                }else{
                    self::generatePayloadContractNoteBondBuyProBookPrimary($simpleTransaction, $transaction, $payloadRaw);
                }
            }
        }

    }

    private static function generatePayloadContractNoteBondSellProBookPrimary(SimpleTransaction $simpleTransaction, Transaction $transaction, BondExecution $payloadRaw): void
    {
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->face_value.' @'.$payloadRaw->price;

        $posting = new FlexcubePosting();
        $posting->type = "PURCHASE";
        $posting = new FlexcubePosting();
        $posting->category = "PROBOOK BOND";
        $posting->type = "SALE";
        $posting->bond_type = strtoupper($payloadRaw->market);
        $posting->entity_id = $payloadRaw->id;
        $posting->tran_reference = $transaction->reference;
        $posting->batch_no = $batch;
        $posting->settlement_date = $payloadRaw->settlement_date;
        $posting->value_date = $payloadRaw->settlement_date;
        $posting->description = $description;
        $posting->main_action = "D";

        $posting->debit = $payloadRaw->amount - $payloadRaw->total_fees + $payloadRaw->other_charges;
        $posting->credit = $payloadRaw->payout;

        $posting->total_charges = $payloadRaw->total_fees;
        $posting->consideration = $payloadRaw->amount;
        $posting->payout = $payloadRaw->payout;

        $posting->debit_credit_diff = $posting->debit - $posting->credit;
        $posting->brokerage_account = self::ledgerMapper("brokerage");
        $posting->brokerage = $payloadRaw->brokerage;

        $posting->vat_account = self::ledgerMapper("vat");
        $posting->vat = $payloadRaw->vat;

        $posting->cmsa_account = self::ledgerMapper("cmsa");
        $posting->cmsa = $payloadRaw->cmsa;

        $posting->dse_account = self::ledgerMapper("dse");
        $posting->dse = $payloadRaw->dse;

        $posting->fidelity_account = self::ledgerMapper("fidelity");
        $posting->fidelity = $payloadRaw->fidelity;

        $posting->cds_account = self::ledgerMapper("cds");
        $posting->cds = $payloadRaw->cds;

        $posting->other_charges_account = self::ledgerMapper("other_charges");
        $posting->other_charges = $payloadRaw->other_charges;

        $posting->quantity = $payloadRaw->face_value;
        $posting->price = $payloadRaw->price;

        $posting->save();

        self::finishPreparation($simpleTransaction,$transaction,$payloadRaw);
    }

    private static function generatePayloadContractNoteBondSellProBookSecondary(SimpleTransaction $simpleTransaction, Transaction $transaction, BondExecution $payloadRaw): void
    {
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->face_value.' @'.$payloadRaw->price;


        try {
            $posting = new FlexcubePosting();
            $posting->category = "PROBOOK BOND";
            $posting->type = "SALE";
            $posting->bond_type = strtoupper($payloadRaw->market);
            $posting->entity_id = $payloadRaw->id;
            $posting->tran_reference = $transaction->reference;
            $posting->batch_no = $batch;
            $posting->settlement_date = $payloadRaw->settlement_date;
            $posting->value_date = $payloadRaw->settlement_date;
            $posting->description = $description;
            $posting->main_action = "D";

            $posting->debit = $payloadRaw->amount - $payloadRaw->total_fees + $payloadRaw->other_charges;
            $posting->credit = $payloadRaw->payout;

            $posting->total_charges = $payloadRaw->total_fees;
            $posting->consideration = $payloadRaw->amount;
            $posting->payout = $payloadRaw->payout;

            $posting->debit_credit_diff = $posting->debit - $posting->credit;
            $posting->brokerage_account = self::ledgerMapper("brokerage");
            $posting->brokerage = $payloadRaw->brokerage;

            $posting->vat_account = self::ledgerMapper("vat");
            $posting->vat = $payloadRaw->vat;

            $posting->cmsa_account = self::ledgerMapper("cmsa");
            $posting->cmsa = $payloadRaw->cmsa;

            $posting->dse_account = self::ledgerMapper("dse");
            $posting->dse = $payloadRaw->dse;

            $posting->fidelity_account = self::ledgerMapper("fidelity");
            $posting->fidelity = $payloadRaw->fidelity;

            $posting->cds_account = self::ledgerMapper("cds");
            $posting->cds = $payloadRaw->cds;

            $posting->other_charges_account = self::ledgerMapper("other_charges");
            $posting->other_charges = $payloadRaw->other_charges;

            $posting->quantity = $payloadRaw->face_value;
            $posting->price = $payloadRaw->price;

            $posting->save();
        }catch (\Throwable $throwable){
            report($throwable);
        }
        self::finishPreparation($simpleTransaction,$transaction,$payloadRaw);
    }

    private static function generatePayloadContractNoteBondBuyProBookPrimary(SimpleTransaction $simpleTransaction, Transaction $transaction, BondExecution $payloadRaw): void
    {
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->face_value.' @'.$payloadRaw->price;

        $posting = new FlexcubePosting();
        $posting->type = "PURCHASE";
        $posting = new FlexcubePosting();
        $posting->category = "PROBOOK BOND";
        $posting->type = "PURCHASE";
        $posting->bond_type = strtoupper($payloadRaw->market);
        $posting->entity_id = $payloadRaw->id;
        $posting->tran_reference = $transaction->reference;
        $posting->batch_no = $batch;
        $posting->settlement_date = $payloadRaw->settlement_date;
        $posting->value_date = $payloadRaw->settlement_date;
        $posting->description = $description;
        $posting->main_action = "D";

        $posting->debit = $payloadRaw->amount + $payloadRaw->total_fees + $payloadRaw->other_charges;
        $posting->credit = $payloadRaw->payout;

        $posting->total_charges = $payloadRaw->total_fees;
        $posting->consideration = $payloadRaw->amount;
        $posting->payout = $payloadRaw->payout;

        $posting->debit_credit_diff = $posting->debit - $posting->credit;
        $posting->brokerage_account = self::ledgerMapper("brokerage");
        $posting->brokerage = $payloadRaw->brokerage;

        $posting->vat_account = self::ledgerMapper("vat");
        $posting->vat = $payloadRaw->vat;

        $posting->cmsa_account = self::ledgerMapper("cmsa");
        $posting->cmsa = $payloadRaw->cmsa;

        $posting->dse_account = self::ledgerMapper("dse");
        $posting->dse = $payloadRaw->dse;

        $posting->fidelity_account = self::ledgerMapper("fidelity");
        $posting->fidelity = $payloadRaw->fidelity;

        $posting->cds_account = self::ledgerMapper("cds");
        $posting->cds = $payloadRaw->cds;

        $posting->other_charges_account = self::ledgerMapper("other_charges");
        $posting->other_charges = $payloadRaw->other_charges;

        $posting->quantity = $payloadRaw->face_value;
        $posting->price = $payloadRaw->price;

        $posting->save();

        self::finishPreparation($simpleTransaction,$transaction,$payloadRaw);
    }

    private static function generatePayloadContractNoteBondBuyProBookSecondary(SimpleTransaction $simpleTransaction, Transaction $transaction, BondExecution $payloadRaw): void
    {
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->face_value.' @'.$payloadRaw->price;

        $posting = new FlexcubePosting();
        $posting->type = "PURCHASE";
        $posting = new FlexcubePosting();
        $posting->category = "PROBOOK BOND";
        $posting->type = "PURCHASE";
        $posting->bond_type = strtoupper($payloadRaw->market);
        $posting->entity_id = $payloadRaw->id;
        $posting->tran_reference = $transaction->reference;
        $posting->batch_no = $batch;
        $posting->settlement_date = $payloadRaw->settlement_date;
        $posting->value_date = $payloadRaw->settlement_date;
        $posting->description = $description;
        $posting->main_action = "D";

        $posting->debit = $payloadRaw->amount + $payloadRaw->total_fees + $payloadRaw->other_charges;
        $posting->credit = $payloadRaw->payout;

        $posting->total_charges = $payloadRaw->total_fees;
        $posting->consideration = $payloadRaw->amount;
        $posting->payout = $payloadRaw->payout;

        $posting->debit_credit_diff = $posting->debit - $posting->credit;
        $posting->brokerage_account = self::ledgerMapper("brokerage");
        $posting->brokerage = $payloadRaw->brokerage;

        $posting->vat_account = self::ledgerMapper("vat");
        $posting->vat = $payloadRaw->vat;

        $posting->cmsa_account = self::ledgerMapper("cmsa");
        $posting->cmsa = $payloadRaw->cmsa;

        $posting->dse_account = self::ledgerMapper("dse");
        $posting->dse = $payloadRaw->dse;

        $posting->fidelity_account = self::ledgerMapper("fidelity");
        $posting->fidelity = $payloadRaw->fidelity;

        $posting->cds_account = self::ledgerMapper("cds");
        $posting->cds = $payloadRaw->cds;

        $posting->other_charges_account = self::ledgerMapper("other_charges");
        $posting->other_charges = $payloadRaw->other_charges;

        $posting->quantity = $payloadRaw->face_value;
        $posting->price = $payloadRaw->price;

        $posting->save();

        self::finishPreparation($simpleTransaction,$transaction,$payloadRaw);
    }

    static function ledgerMapper($mapper): string
    {
        return match($mapper){
             'daily_settlement'  => 'LB501200',
             'brokerage'  => 'IN501101',
             'vat' => 'LB501103',
             'cmsa' =>  'LB501101',
             'dse' => 'LB501104',
             'fidelity' => 'LB501102',
             'cds' => 'LB501105',
             'other_charges' =>  'EX280100',
             default => 'unknown',
        };
    }

    private static function finishPreparation(SimpleTransaction $simpleTransaction, Transaction $transaction, mixed $payloadRaw)
    {
    }

    private static function _generateBatch(string $reference): string
    {
//        $date = Helper::systemDateTime();
        $date = Helper::systemDateTime();
        $date = date("Y-m-d",strtotime($date['today']));
        $batchNumber = null;
        $batch = TransactionBatch::whereDate("date",$date)->latest()->first();

        if(empty($batch)){
            $batch = new TransactionBatch();
            $batch->batch_reference = $reference;
            $batch->date = $date;
            $batch->batch_prefix = "B";
            $batch->batch_number = 1;
            $batchNumber = $batch->batch_prefix."001";
        }else{
            $number =  (int) $batch->batch_number + 1;
            $batchNumber = $batch->batch_prefix."$number";

            if(strlen($number) == 1) {
                $batchNumber = $batch->batch_prefix."00$number";
            }

            if(strlen($number) == 2) {
                $batchNumber = $batch->batch_prefix."0$number";
            }

            $batch = new TransactionBatch();
            $batch->batch_reference = $reference;
            $batch->date = $date;
            $batch->batch_prefix = "B";
            $batch->batch_number = $number;
        }
        $batch->batch = $batchNumber;
        $batch->save();

        return $batch->batch;
    }

}
