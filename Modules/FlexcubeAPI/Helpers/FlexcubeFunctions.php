<?php

namespace Modules\FlexcubeAPI\Helpers;

use App\Helpers\Helper;
use App\Models\Accounting\Transaction;
use App\Models\DealingSheet;
use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Modules\Accounting\Entities\SimpleTransaction;
use Modules\Bonds\Entities\BondExecution;
use Modules\Custodians\Entities\Custodian;
use Modules\FlexcubeAPI\Entities\FlexcubeChartAccounts;
use Modules\FlexcubeAPI\Entities\FlexcubeFail;
use Modules\FlexcubeAPI\Entities\FlexcubeTransactions;
use Modules\FlexcubeAPI\Models\TransactionBatch;
use Modules\Payments\Entities\Payment;
use Modules\Receipts\Entities\Receipt;
use Modules\Securities\Entities\Security;

class FlexcubeFunctions
{

    public static function prepareAndPortToFlexicube(Transaction $transaction): object
    {
        $simpleTransaction = SimpleTransaction::where("trans_id",$transaction->id)->first();
        switch (strtolower($transaction->category)) {
            case "order";
                $dealingSheet = DealingSheet::where("slip_no", $transaction->reference)->firstOrFail();

                $testDebitCredit = self::_testDebitCreditStock($dealingSheet);

                if(!$testDebitCredit->status){
                    return  $testDebitCredit;
                }

                if($dealingSheet->brokerage > 0) {
                    return self::postContractNoteStock($simpleTransaction, $transaction, $dealingSheet);
                }else{
                    return self::postContractNoteStockProBook($simpleTransaction, $transaction, $dealingSheet);
                }

            case "custodian";
                if(strtolower($transaction->custodian_type == "equity")){
                    $dealingSheet = DealingSheet::where("slip_no", $transaction->reference)->firstOrFail();

                    $testDebitCredit = self::_testDebitCreditStock($dealingSheet);

                    if(!$testDebitCredit->status){
                        return  $testDebitCredit;
                    }

                    if($dealingSheet->brokerage > 0) {
                        return self::postContractNoteStock($simpleTransaction, $transaction, $dealingSheet);
                    }else{
                        return self::postContractNoteStockProBook($simpleTransaction, $transaction, $dealingSheet);
                    }

                }else{
                    $dealingSheet = BondExecution::where("slip_no", $transaction->reference)->firstOrFail();

                    $testDebitCredit = self::_testDebitCreditBond($dealingSheet);

                    if(!$testDebitCredit->status){
                        return  $testDebitCredit;
                    }

                    if($dealingSheet->brokerage > 0) {
                        return self::postContractNoteBond($simpleTransaction, $transaction, $dealingSheet);
                    }else{
                        return self::postContractNoteBondProBook($simpleTransaction, $transaction, $dealingSheet);
                    }
                }
            case "bond";
                $dealingSheet = BondExecution::where("slip_no", $transaction->reference)->firstOrFail();

                $testDebitCredit = self::_testDebitCreditBond($dealingSheet);

                if(!$testDebitCredit->status){
                    return  $testDebitCredit;
                }

                if($dealingSheet->brokerage > 0) {
                    return self::postContractNoteBond($simpleTransaction, $transaction, $dealingSheet);
                }else{
                    return self::postContractNoteBondProBook($simpleTransaction, $transaction, $dealingSheet);
                }
            case "payment";
                $payment = Payment::where("reference", $transaction->reference)->firstOrFail();
                return  self::postPayment($simpleTransaction,$transaction,$payment);
            case "receipt";
                $receipt = Receipt::where("reference", $transaction->reference)->firstOrFail();
                return  self::postReceipt($simpleTransaction,$transaction,$receipt);
            default;
                $result = new \stdClass();
                $result->status = false;
                $result->code = "local";
                $result->message =  "System could not determine transaction type";
                $result->data =  "";
                return $result;
        }
    }

    public static function postContractNoteStock(SimpleTransaction $simpleTransaction, Transaction $transaction, DealingSheet $payloadRaw): object
    {
        if(!empty($payloadRaw->custodian_id)){
            $payload = self::generatePayloadContractNoteStockCustodian($simpleTransaction,$transaction,$payloadRaw);
        }else{
            if(strtolower($payloadRaw->type == "sell")){
                $payload = self::generatePayloadContractNoteStockSell($simpleTransaction, $transaction, $payloadRaw);
            }else {
                $payload = self::generatePayloadContractNoteStockBuy($simpleTransaction, $transaction, $payloadRaw);
            }
        }

       return self::_processPayload($simpleTransaction, $transaction,$payload);

    }

    public static function postContractNoteStockProBook(SimpleTransaction $simpleTransaction, Transaction $transaction, DealingSheet $payloadRaw): object
    {
        if(strtolower($payloadRaw->type == "sell")){
            $payloads = self::generatePayloadContractNoteStockSellProBook($simpleTransaction, $transaction, $payloadRaw);
        }else {
            $payloads = self::generatePayloadContractNoteStockBuyProBook($simpleTransaction, $transaction, $payloadRaw);
        }

        $result = new \stdClass();
        $result->status = false;
        $result->code = "";
        $result->message =  "";
        $result->data =  "";

        if(!empty($payloads)){
            foreach ($payloads as $payload){
//                $response = self::apiClient()
//                    ->withBody(json_encode($payload))
//                    ->send('POST', $endpoint)
//                    ->onError(function ($error) {
//                        return $error;
//                    });
//
//                $resultInput = (object) json_decode($response->body());

                $result = self::_processPayload($simpleTransaction, $transaction,json_encode($payload));

            }
        }

        return $result;

    }

    public static function postContractNoteBond(SimpleTransaction $simpleTransaction, Transaction $transaction, BondExecution $payloadRaw): object
    {
        if(!empty($payloadRaw->custodian_id)){
            if(strtolower($payloadRaw->type == "sell")){
                if(strtolower($payloadRaw->market) == "secondary") {
                    $payload = self::generatePayloadContractNoteBondCustodianSellSecondary($simpleTransaction, $transaction, $payloadRaw);
                }else{
                    $payload = self::generatePayloadContractNoteBondCustodianSellPrimary($simpleTransaction, $transaction, $payloadRaw);
                }
            }else {
                if(strtolower($payloadRaw->market) == "secondary") {
                    $payload = self::generatePayloadContractNoteBondCustodianBuySecondary($simpleTransaction, $transaction, $payloadRaw);
                }else{
                    $payload = self::generatePayloadContractNoteBondCustodianBuyPrimary($simpleTransaction, $transaction, $payloadRaw);
                }
            }
        }else{
            if(strtolower($payloadRaw->type == "sell")){
                if(strtolower($payloadRaw->market) == "secondary") {
                    $payload = self::generatePayloadContractNoteBondSellSecondary($simpleTransaction, $transaction, $payloadRaw);
                }else{
                    $payload = self::generatePayloadContractNoteBondSellPrimary($simpleTransaction, $transaction, $payloadRaw);
                }
            }else {
                if(strtolower($payloadRaw->market) == "secondary") {
                    $payload = self::generatePayloadContractNoteBondBuySecondary($simpleTransaction, $transaction, $payloadRaw);
                }else{
                    $payload = self::generatePayloadContractNoteBondBuyPrimary($simpleTransaction, $transaction, $payloadRaw);
                }
            }
        }

        return self::_processPayload($simpleTransaction, $transaction,$payload);

    }

    public static function postPayment(SimpleTransaction $simpleTransaction, Transaction $transaction,Payment $payloadRaw): object
    {
        $payload = self::generatePayloadPayment($simpleTransaction,$transaction, $payloadRaw);
        return self::_processPayload($simpleTransaction, $transaction,$payload);
    }

    public static function postReceipt(SimpleTransaction $simpleTransaction, Transaction $transaction, Receipt $payloadRaw): object
    {
        $payload = self::generatePayloadReceipt($simpleTransaction,$transaction, $payloadRaw);

        return self::_processPayload($simpleTransaction, $transaction,$payload);
    }

    private static function processResult(SimpleTransaction $simpleTransaction, Transaction $transaction,$input,$payload): \stdClass
    {
        $status = false;
        $payload = (array) $payload;
        if($input->Status_Code == 200 && $input->MSGSTAT == "SUCCESS"){
            $now = now(getenv('TIMEZONE'))->toDateTimeString();
            $transaction->flexcube_post_date = $simpleTransaction->date;
            $transaction->flexcube_reference = $input->DE_REF_NO;
            $transaction->flexcube_batch_no = $input->DE_BATCH_NUMBER;
            $transaction->flexcube_sync_response = $input->MSGSTAT;
            $transaction->flexcube_status = "synced";
            $transaction->flexcube_synced_by = auth()->id();
            $transaction->flexcube_synced_at = $now;
            $transaction->save();

            $simpleTransaction->flexcube_post_date = $simpleTransaction->date;
            $simpleTransaction->flexcube_reference = $input->DE_REF_NO;
            $simpleTransaction->flexcube_batch_no = $input->DE_BATCH_NUMBER;
            $simpleTransaction->flexcube_sync_response = $input->MSGSTAT;
            $simpleTransaction->flexcube_status = "synced";
            $simpleTransaction->flexcube_synced_by = auth()->id();
            $simpleTransaction->flexcube_synced_at = $now;
            $simpleTransaction->save();

            $flexcubeTransaction = new FlexcubeTransactions();
            $flexcubeTransaction->batch_no = $input->DE_BATCH_NUMBER;
            $flexcubeTransaction->trn_ref_no  = $input->DE_REF_NO;
            $flexcubeTransaction->value_dt  = $payload["Multioffsetmaster-Full"]->DE_VALUE_DATE;
            $flexcubeTransaction->ac_branch  = $payload["BRANCH"];
            $flexcubeTransaction->ac_no  = $payload["Multioffsetmaster-Full"]->DE_ACCNO;
            $flexcubeTransaction->ac_gl_desc  = FlexcubeChartAccounts::where('gl_code',$payload["Multioffsetmaster-Full"]->DE_ACCNO)->first()->gl_desc??"";
            $flexcubeTransaction->datetime  = $now;
            $flexcubeTransaction->addl_text  = $payload["Multioffsetmaster-Full"]->DE_ADDL_TEXT;
            $flexcubeTransaction->de_description  = $payload["Multioffsetmaster-Full"]->DE_DESCRIPTION;
            $flexcubeTransaction->instrument_code  = $payload["Multioffsetmaster-Full"]->DE_INSTRUMENT_NUMBER;
            $flexcubeTransaction->debits  = ($payload["Multioffsetmaster-Full"]->DE_DR_CR == "D") ? $payload["Multioffsetmaster-Full"]->DE_AMOUNT : 0;
            $flexcubeTransaction->credits  = ($payload["Multioffsetmaster-Full"]->DE_DR_CR == "C") ? $payload["Multioffsetmaster-Full"]->DE_AMOUNT : 0;
            $flexcubeTransaction->syncled_by  =  auth()->id();
            $flexcubeTransaction->syncled_at  = $now;
            $flexcubeTransaction->save();

            $offsets = $payload["Multioffsetmaster-Full"]->Mltoffsetdetail;
            if(!empty($offsets)){
                foreach ( $offsets as $offset){
                    $flexcubeTransaction = new FlexcubeTransactions();
                    $flexcubeTransaction->batch_no = $input->DE_BATCH_NUMBER;
                    $flexcubeTransaction->trn_ref_no  = $input->DE_REF_NO;
                    $flexcubeTransaction->value_dt  = $payload["Multioffsetmaster-Full"]->DE_VALUE_DATE;
                    $flexcubeTransaction->datetime  = $now;
                    $flexcubeTransaction->ac_branch  = $payload["BRANCH"];
                    $flexcubeTransaction->ac_no  = $offset->DE_ACCNO;
                    $flexcubeTransaction->ac_gl_desc  = FlexcubeChartAccounts::where('gl_code',$offset->DE_ACCNO)->first()->gl_desc??"";
                    $flexcubeTransaction->de_description  = $offset->ACCOUNT_DESCRIPTION;
                    $flexcubeTransaction->addl_text  = $offset->ACCOUNT_DESCRIPTION;
                    $flexcubeTransaction->instrument_code  = $offset->DE_INSTRUMENT_NUMBER;
                    $flexcubeTransaction->debits  = ($payload["Multioffsetmaster-Full"]->DE_DR_CR == "D") ? 0 :  $offset->DE_AMOUNT;
                    $flexcubeTransaction->credits  = ($payload["Multioffsetmaster-Full"]->DE_DR_CR == "C") ? 0 :  $offset->DE_AMOUNT;
                    $flexcubeTransaction->syncled_by  =  auth()->id();
                    $flexcubeTransaction->syncled_at  = $now;
                    $flexcubeTransaction->save();
                }
            }
            $status = true;
        }else{
            $failed = new FlexcubeFail();
            $failed->brokerlink_reference = $transaction->reference;
            $failed->error_level = "flexcube";
            $failed->description = $input->Message[0]->DESC;
            $failed->posting_date = $simpleTransaction->date;
            $failed->save();
        }

        $result = new \stdClass();
        $result->status = $status;
        $result->code = $input->Message[0]->CODE;
        $result->message =  $input->Message[0]->DESC;
        $result->data =  $input;

        return $result;
    }

    private static function generatePayloadContractNoteStockCustodian(SimpleTransaction $simpleTransaction, Transaction $transaction, DealingSheet $payloadRaw): string
    {
        $account = Custodian::findOrFail($payloadRaw->custodian_id);
        $batch = self::_generateBatch($payloadRaw->reference);

        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->executed.' @'.$payloadRaw->price;

        $totalCharges = str_ireplace(",","",number_format(self::_getTotalChargesStock($payloadRaw)));

        $payload = [
            "SOURCE" => getenv("FLEX_SOURCE"),
            "USERID" => getenv("FLEX_USER_ID"),
            "BRANCH" => "002",
            "Multioffsetmaster-Full" => [
                "DE_BATCH_NUMBER" => $batch,
                "DE_CURRNO" => "1",
                "DE_CCY_CD" => "TZS",
                "DE_MAIN" => "000",
                "DE_OFFSET" => "000",
                "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                "DE_DR_CR" => "D",
                "DE_AMOUNT" => $totalCharges,
                "DE_EXCH_RATE" => "1",
                "DE_LCY_AMOUNT" => $totalCharges,
                "MAKERID" => "",
                "DE_AUTHORIZED_BY" => "",
                "DE_DATETIME" => now()->toDateTimeString(),
                "AUTHSTAT" => "",
                "DE_MAKER_DATETIME" => "",
                "DE_DESCRIPTION" => $description,
                "DE_ADDL_TEXT" => $description,
                "DE_BATCH_DESC" => "WEB TEST",
                "DE_ACCNO" => $account->ledger,
                "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                "Mltoffsetdetail" =>  [
                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => str_ireplace(",","",$payloadRaw->brokerage),
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "2",
                        "ACCOUNT_DESCRIPTION" => "Brokerage fee on ".$description,
                        "DE_ACCNO" => "IN501101"
                    ],
                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->vat,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "3",
                        "ACCOUNT_DESCRIPTION" => "VAT fee on ".$description,
                        "DE_ACCNO" => "LB501103"
                    ],
                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->cmsa,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "4",
                        "ACCOUNT_DESCRIPTION" => "CMSA fee on ".$description,
                        "DE_ACCNO" => "LB501101"
                    ],
                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->dse,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "5",
                        "ACCOUNT_DESCRIPTION" => "DSE fee on ".$description,
                        "DE_ACCNO" => "LB501104"
                    ],
                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->fidelity,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "6",
                        "ACCOUNT_DESCRIPTION" => "FIDELITY fee on ".$description,
                        "DE_ACCNO" => "LB501102"
                    ],
                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->cds,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "7",
                        "ACCOUNT_DESCRIPTION" => "CDS fee on ".$description,
                        "DE_ACCNO" => "LB501105"
                    ]
                ]
            ]
        ];

        return json_encode($payload);
    }

    private static function generatePayloadContractNoteStockSell(SimpleTransaction $simpleTransaction, Transaction $transaction, DealingSheet $payloadRaw): string
    {

        $payout = self::_getPayoutStockSell($payloadRaw);
        $dailySettlement = "LB501200";
        $customer = User::find($payloadRaw->client_id);
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->executed.' @'.$payloadRaw->price;

        if($payloadRaw->other_charges < 1){
            $payload = [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "D",
                    "DE_AMOUNT" => $payout,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payout,
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => $dailySettlement,
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                    "Mltoffsetdetail" =>  [

                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->payout,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "1",
                            "ACCOUNT_DESCRIPTION" => $description,
                            "DE_ACCNO" => $customer->flex_acc_no
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->brokerage,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "2",
                            "ACCOUNT_DESCRIPTION" => "Brokerage fee on ".$description,
                            "DE_ACCNO" => "IN501101"
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->vat,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "3",
                            "ACCOUNT_DESCRIPTION" => "VAT fee on ".$description,
                            "DE_ACCNO" => "LB501103"
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->cmsa,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "4",
                            "ACCOUNT_DESCRIPTION" => "CMSA fee on ".$description,
                            "DE_ACCNO" => "LB501101"
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->dse,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "5",
                            "ACCOUNT_DESCRIPTION" => "DSE fee on ".$description,
                            "DE_ACCNO" => "LB501104"
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->fidelity,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "6",
                            "ACCOUNT_DESCRIPTION" => "FIDELITY fee on ".$description,
                            "DE_ACCNO" => "LB501102"
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->cds,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "7",
                            "ACCOUNT_DESCRIPTION" => "CDS fee on ".$description,
                            "DE_ACCNO" => "LB501105"
                        ]
                    ]
                ]
            ];
        }else{

            $payload = [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "D",
                    "DE_AMOUNT" => $payout,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payout,
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => $dailySettlement,
                    "Mltoffsetdetail" =>  [
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->payout,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "1",
                            "ACCOUNT_DESCRIPTION" => $description,
                            "DE_ACCNO" => $customer->flex_acc_no
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->brokerage,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "2",
                            "ACCOUNT_DESCRIPTION" => "Brokerage fee on ".$description,
                            "DE_ACCNO" => "IN501101"
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->vat,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "3",
                            "ACCOUNT_DESCRIPTION" => "VAT fee on ".$description,
                            "DE_ACCNO" => "LB501103"
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->cmsa,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "4",
                            "ACCOUNT_DESCRIPTION" => "CMSA fee on ".$description,
                            "DE_ACCNO" => "LB501101"
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->dse,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "5",
                            "ACCOUNT_DESCRIPTION" => "DSE fee on ".$description,
                            "DE_ACCNO" => "LB501104"
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->fidelity,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "6",
                            "ACCOUNT_DESCRIPTION" => "FIDELITY fee on ".$description,
                            "DE_ACCNO" => "LB501102"
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->cds,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "7",
                            "ACCOUNT_DESCRIPTION" => "CDS fee on ".$description,
                            "DE_ACCNO" => "LB501105"
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->other_charges,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "8",
                            "ACCOUNT_DESCRIPTION" => "Bank charges on ".$description,
                            "DE_ACCNO" => "EX280100"
                        ]
                    ]
                ]
            ];
        }
        return json_encode($payload);
    }

    private static function generatePayloadContractNoteStockBuy(SimpleTransaction $simpleTransaction, Transaction $transaction, DealingSheet $payloadRaw): string
    {
        $payout = self::_getPayoutStockBuy($payloadRaw);
        $dailySettlement = "LB501200";
        $customer = User::find($payloadRaw->client_id);
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->executed.' @'.$payloadRaw->price;
        $payload = [
            "SOURCE" => getenv("FLEX_SOURCE"),
            "USERID" => getenv("FLEX_USER_ID"),
            "BRANCH" => "002",
            "Multioffsetmaster-Full" => [
                "DE_BATCH_NUMBER" => $batch,
                "DE_CURRNO" => "1",
                "DE_CCY_CD" => "TZS",
                "DE_MAIN" => "000",
                "DE_OFFSET" => "000",
                "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                "DE_DR_CR" => "D",
                "DE_AMOUNT" => $payout,
                "DE_EXCH_RATE" => "1",
                "DE_LCY_AMOUNT" => $payout,
                "MAKERID" => "",
                "DE_AUTHORIZED_BY" => "",
                "DE_DATETIME" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                "AUTHSTAT" => "",
                "DE_MAKER_DATETIME" => "",
                "DE_DESCRIPTION" => $description,
                "DE_ADDL_TEXT" => $description,
                "DE_BATCH_DESC" => "WEB TEST",
                "DE_ACCNO" => $customer->flex_acc_no,
                "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                "Mltoffsetdetail" =>  [

                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->amount,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "1",
                        "ACCOUNT_DESCRIPTION" => $description,
                        "DE_ACCNO" => $dailySettlement
                    ],
                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->brokerage,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "2",
                        "ACCOUNT_DESCRIPTION" => "Brokerage fee on ".$description,
                        "DE_ACCNO" => "IN501101"
                    ],
                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->vat,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "3",
                        "ACCOUNT_DESCRIPTION" => "VAT fee on ".$description,
                        "DE_ACCNO" => "LB501103"
                    ],
                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->cmsa,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "4",
                        "ACCOUNT_DESCRIPTION" => "CMSA fee on ".$description,
                        "DE_ACCNO" => "LB501101"
                    ],
                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->dse,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "5",
                        "ACCOUNT_DESCRIPTION" => "DSE fee on ".$description,
                        "DE_ACCNO" => "LB501104"
                    ],
                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->fidelity,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "6",
                        "ACCOUNT_DESCRIPTION" => "FIDELITY fee on ".$description,
                        "DE_ACCNO" => "LB501102"
                    ],
                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->cds,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "7",
                        "ACCOUNT_DESCRIPTION" => "CDS fee on ".$description,
                        "DE_ACCNO" => "LB501105"
                    ]
                ]
            ]
        ];
        return json_encode($payload);
    }

    private static function generatePayloadContractNoteStockSellProBook(SimpleTransaction $simpleTransaction, Transaction $transaction, DealingSheet $payloadRaw): array
    {
        $dailySettlement = "LB501200";
        $investmentFund = Security::find($payloadRaw->security_id)->ledger;
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->executed.' @'.$payloadRaw->price;

        return [
            [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "D",
                    "DE_AMOUNT" => $payloadRaw->amount,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payloadRaw->amount,
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => $dailySettlement,
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                    "Mltoffsetdetail" =>  [
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->amount,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "1",
                            "ACCOUNT_DESCRIPTION" => $description,
                            "DE_ACCNO" => $investmentFund
                        ]
                    ]
                ]
            ],
            [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "D",
                    "DE_AMOUNT" => $payloadRaw->cmsa,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payloadRaw->cmsa,
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => "EX340200",
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                    "Mltoffsetdetail" =>  [
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->cmsa,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "4",
                            "ACCOUNT_DESCRIPTION" => "CMSA fee on ".$description,
                            "DE_ACCNO" => "LB501101"
                        ],
                    ]
                ]
            ],
            [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "D",
                    "DE_AMOUNT" => $payloadRaw->dse,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payloadRaw->dse,
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => "EX340100",
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                    "Mltoffsetdetail" =>  [
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->dse,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "4",
                            "ACCOUNT_DESCRIPTION" => "DSE fee on ".$description,
                            "DE_ACCNO" => "LB501104"
                        ],
                    ]
                ]
            ],
            [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "D",
                    "DE_AMOUNT" => $payloadRaw->fidelity,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payloadRaw->fidelity,
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => "EX340400",
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                    "Mltoffsetdetail" =>  [
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->fidelity,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "4",
                            "ACCOUNT_DESCRIPTION" => "Fidelity fee on ".$description,
                            "DE_ACCNO" => "LB501102"
                        ],
                    ]
                ]
            ],
            [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "D",
                    "DE_AMOUNT" => $payloadRaw->cds,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payloadRaw->cds,
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => "EX340300",
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                    "Mltoffsetdetail" =>  [
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->cds,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "4",
                            "ACCOUNT_DESCRIPTION" => "CDS fee on ".$description,
                            "DE_ACCNO" => "LB501105"
                        ],
                    ]
                ]
            ],
        ];
    }

    private static function generatePayloadContractNoteStockBuyProBook(SimpleTransaction $simpleTransaction, Transaction $transaction, DealingSheet $payloadRaw): array
    {
        $dailySettlement = "LB501200";
        $investmentFund = Security::find($payloadRaw->security_id)->ledger;
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->executed.' @'.$payloadRaw->price;

        return [
            [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "C",
                    "DE_AMOUNT" => $payloadRaw->amount,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payloadRaw->amount,
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => $dailySettlement,
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                    "Mltoffsetdetail" =>  [
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->amount,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "1",
                            "ACCOUNT_DESCRIPTION" => $description,
                            "DE_ACCNO" => $investmentFund
                        ]
                    ]
                ]
            ],
            [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "D",
                    "DE_AMOUNT" => $payloadRaw->cmsa,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payloadRaw->cmsa,
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => "EX340200",
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                    "Mltoffsetdetail" =>  [
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->cmsa,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "4",
                            "ACCOUNT_DESCRIPTION" => "CMSA fee on ".$description,
                            "DE_ACCNO" => "LB501101"
                        ],
                    ]
                ]
            ],
            [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "D",
                    "DE_AMOUNT" => $payloadRaw->dse,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payloadRaw->dse,
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => "EX340100",
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                    "Mltoffsetdetail" =>  [
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->dse,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "4",
                            "ACCOUNT_DESCRIPTION" => "DSE fee on ".$description,
                            "DE_ACCNO" => "LB501104"
                        ],
                    ]
                ]
            ],
            [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "D",
                    "DE_AMOUNT" => $payloadRaw->fidelity,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payloadRaw->fidelity,
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => "EX340400",
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                    "Mltoffsetdetail" =>  [
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->fidelity,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "4",
                            "ACCOUNT_DESCRIPTION" => "Fidelity fee on ".$description,
                            "DE_ACCNO" => "LB501102"
                        ],
                    ]
                ]
            ],
            [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "D",
                    "DE_AMOUNT" => $payloadRaw->cds,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payloadRaw->cds,
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => "EX340300",
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                    "Mltoffsetdetail" =>  [
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->cds,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "4",
                            "ACCOUNT_DESCRIPTION" => "CDS fee on ".$description,
                            "DE_ACCNO" => "LB501105"
                        ],
                    ]
                ]
            ],
        ];
    }

    private static function generatePayloadPayment(SimpleTransaction $simpleTransaction, Transaction $transaction,Payment $payloadRaw): string
    {
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars;
        $trustAccount = "0009100022602";
        $batch = self::_generateBatch($payloadRaw->reference);
        $customer = User::find($payloadRaw->client_id);
        $payload = [
            "SOURCE" => getenv("FLEX_SOURCE"),
            "USERID" => getenv("FLEX_USER_ID"),
            "BRANCH" => "002",
            "Multioffsetmaster-Full" => [
                "DE_BATCH_NUMBER" => $batch,
                "DE_CURRNO" => "1",
                "DE_CCY_CD" => "TZS",
                "DE_MAIN" => "000",
                "DE_OFFSET" => "000",
                "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->date)),
                "DE_DR_CR" => "D",
                "DE_AMOUNT" => $payloadRaw->amount,
                "DE_EXCH_RATE" => "1",
                "DE_LCY_AMOUNT" => $payloadRaw->amount,
               "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                "MAKERID" => "",
                "DE_AUTHORIZED_BY" => "",
                 "DE_DATETIME" => now()->toDateTimeString(),
                "AUTHSTAT" => "",
                "DE_MAKER_DATETIME" => "",
                "DE_DESCRIPTION" => $payloadRaw->particulars,
                "DE_ADDL_TEXT" => $description,
                "DE_BATCH_DESC" => "WEB TEST",
//             "DE_ACCNO" => $customer->flex_acc_no,
                "DE_ACCNO" => "0022200024101",
                "Mltoffsetdetail" =>  [
                    [
                       "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->amount,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "90",
                        "ACCOUNT_DESCRIPTION" => $payloadRaw->particulars,
                        "DE_ACCNO" => $trustAccount
                    ]
                ]
            ]
        ];
        return json_encode($payload);
    }

    private static function generatePayloadReceipt(SimpleTransaction $simpleTransaction, Transaction $transaction, Receipt $payloadRaw): string
    {
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars;
        $trustAccount = "0009100022602";
        $customer = User::find($payloadRaw->client_id);
        $batch = self::_generateBatch($payloadRaw->reference);
        $payload = [
            "SOURCE" => getenv("FLEX_SOURCE"),
            "USERID" => getenv("FLEX_USER_ID"),
            "BRANCH" => "002",
            "Multioffsetmaster-Full" => [
                "DE_BATCH_NUMBER" => $batch,
                "DE_CURRNO" => "1",
                "DE_CCY_CD" => "TZS",
                "DE_MAIN" => "000",
                "DE_OFFSET" => "000",
                "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->date)),
                "DE_DR_CR" => "C",
                "DE_AMOUNT" => $payloadRaw->amount,
                "DE_EXCH_RATE" => "1",
                "DE_LCY_AMOUNT" => $payloadRaw->amount,
                "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                "MAKERID" => "",
                "DE_AUTHORIZED_BY" => "",
                "DE_DATETIME" => now()->toDateTimeString(),
                "AUTHSTAT" => "",
                "DE_MAKER_DATETIME" => "",
                "DE_DESCRIPTION" => $payloadRaw->particulars,
                "DE_ADDL_TEXT" => $description,
                "DE_BATCH_DESC" => "WEB TEST",
                "DE_ACCNO" => $customer->flex_acc_no,
                "Mltoffsetdetail" =>  [
                    [
                       "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->amount,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "90",
                        "ACCOUNT_DESCRIPTION" => $payloadRaw->particulars,
                        "DE_ACCNO" => $trustAccount
                    ]
                ]
            ]
        ];
        return json_encode($payload);
    }

    private static function generatePayloadContractNoteBondBuySecondary(SimpleTransaction $simpleTransaction, Transaction $transaction,BondExecution $payloadRaw): string
    {
        $payout = self::_getPayoutBondBuy($payloadRaw);
        $dailySettlement = "LB501200";
        $customer = User::find($payloadRaw->client_id);
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->executed.' @'.$payloadRaw->price;
        $payload = [
            "SOURCE" => getenv("FLEX_SOURCE"),
            "USERID" => getenv("FLEX_USER_ID"),
            "BRANCH" => "002",
            "Multioffsetmaster-Full" => [
                "DE_BATCH_NUMBER" => $batch,
                "DE_CURRNO" => "1",
                "DE_CCY_CD" => "TZS",
                "DE_MAIN" => "000",
                "DE_OFFSET" => "000",
                "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                "DE_DR_CR" => "D",
                "DE_AMOUNT" => $payout,
                "DE_EXCH_RATE" => "1",
                "DE_LCY_AMOUNT" => $payout,
                "MAKERID" => "",
                "DE_AUTHORIZED_BY" => "",
                "DE_DATETIME" => now()->toDateTimeString(),
                "AUTHSTAT" => "",
                "DE_MAKER_DATETIME" => "",
                "DE_DESCRIPTION" => $description,
                "DE_ADDL_TEXT" => $description,
                "DE_BATCH_DESC" => "WEB TEST",
                "DE_ACCNO" => $customer->flex_acc_no,
                "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                "Mltoffsetdetail" =>  [

                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->amount,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "1",
                        "ACCOUNT_DESCRIPTION" => $description,
                        "DE_ACCNO" => $dailySettlement
                    ],
                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->brokerage,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "2",
                        "ACCOUNT_DESCRIPTION" => "Brokerage fee on ".$description,
                        "DE_ACCNO" => "IN501101"
                    ],
                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->vat,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "3",
                        "ACCOUNT_DESCRIPTION" => "VAT fee on ".$description,
                        "DE_ACCNO" => "LB501103"
                    ],
                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->cmsa,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "4",
                        "ACCOUNT_DESCRIPTION" => "CMSA fee on ".$description,
                        "DE_ACCNO" => "LB501101"
                    ],
                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->dse,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "5",
                        "ACCOUNT_DESCRIPTION" => "DSE fee on ".$description,
                        "DE_ACCNO" => "LB501104"
                    ],
                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->cds,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "7",
                        "ACCOUNT_DESCRIPTION" => "CDS fee on ".$description,
                        "DE_ACCNO" => "LB501105"
                    ]
                ]
            ]
        ];
        return json_encode($payload);
    }

    private static function generatePayloadContractNoteBondBuyPrimary(SimpleTransaction $simpleTransaction, Transaction $transaction,BondExecution $payloadRaw): string
    {
        $payout = self::_getPayoutBondBuy($payloadRaw);
        $dailySettlement = "LB501200";
        $customer = User::find($payloadRaw->client_id);
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->executed.' @'.$payloadRaw->price;
        $payload = [
            "SOURCE" => getenv("FLEX_SOURCE"),
            "USERID" => getenv("FLEX_USER_ID"),
            "BRANCH" => "002",
            "Multioffsetmaster-Full" => [
                "DE_BATCH_NUMBER" => $batch,
                "DE_CURRNO" => "1",
                "DE_CCY_CD" => "TZS",
                "DE_MAIN" => "000",
                "DE_OFFSET" => "000",
                "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                "DE_DR_CR" => "D",
                "DE_AMOUNT" => $payout,
                "DE_EXCH_RATE" => "1",
                "DE_LCY_AMOUNT" => $payout,
                "MAKERID" => "",
                "DE_AUTHORIZED_BY" => "",
                "DE_DATETIME" => now()->toDateTimeString(),
                "AUTHSTAT" => "",
                "DE_MAKER_DATETIME" => "",
                "DE_DESCRIPTION" => $description,
                "DE_ADDL_TEXT" => $description,
                "DE_BATCH_DESC" => "WEB TEST",
                "DE_ACCNO" => $customer->flex_acc_no,
                "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                "Mltoffsetdetail" =>  [

                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->amount,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "1",
                        "ACCOUNT_DESCRIPTION" => $description,
                        "DE_ACCNO" => $dailySettlement
                    ],
                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->brokerage,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "2",
                        "ACCOUNT_DESCRIPTION" => "Brokerage fee on ".$description,
                        "DE_ACCNO" => "IN501101"
                    ],
                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->vat,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "3",
                        "ACCOUNT_DESCRIPTION" => "VAT fee on ".$description,
                        "DE_ACCNO" => "LB501103"
                    ]
                ]
            ]
        ];
        return json_encode($payload);
    }

    private static function generatePayloadContractNoteBondSellSecondary(SimpleTransaction $simpleTransaction, Transaction $transaction,BondExecution $payloadRaw): string
    {
        $payout = self::_getPayoutBondSell($payloadRaw);
        $dailySettlement = "LB501200";
        $customer = User::find($payloadRaw->client_id);
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->executed.' @'.$payloadRaw->price;

        if($payloadRaw->other_charges < 1){
            $payload = [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "D",
                    "DE_AMOUNT" => $payout,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payout,
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => $dailySettlement,
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                    "Mltoffsetdetail" =>  [

                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->payout,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "1",
                            "ACCOUNT_DESCRIPTION" => $description,
                            "DE_ACCNO" => $customer->flex_acc_no
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->brokerage,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "2",
                            "ACCOUNT_DESCRIPTION" => "Brokerage fee on ".$description,
                            "DE_ACCNO" => "IN501102"
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->vat,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "3",
                            "ACCOUNT_DESCRIPTION" => "VAT fee on ".$description,
                            "DE_ACCNO" => "LB501103"
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->cmsa,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "4",
                            "ACCOUNT_DESCRIPTION" => "CMSA fee on ".$description,
                            "DE_ACCNO" => "LB501101"
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->dse,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "5",
                            "ACCOUNT_DESCRIPTION" => "DSE fee on ".$description,
                            "DE_ACCNO" => "LB501104"
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->cds,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "7",
                            "ACCOUNT_DESCRIPTION" => "CDS fee on ".$description,
                            "DE_ACCNO" => "LB501105"
                        ]
                    ]
                ]
            ];
        }else{

            $payload = [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "D",
                    "DE_AMOUNT" => $payloadRaw->amount,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payloadRaw->amount,
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => $dailySettlement,
                    "Mltoffsetdetail" =>  [
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->payout,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "1",
                            "ACCOUNT_DESCRIPTION" => $description,
                            "DE_ACCNO" => $customer->flex_acc_no
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->brokerage,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "2",
                            "ACCOUNT_DESCRIPTION" => "Brokerage fee on ".$description,
                            "DE_ACCNO" => "IN501101"
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->vat,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "3",
                            "ACCOUNT_DESCRIPTION" => "VAT fee on ".$description,
                            "DE_ACCNO" => "LB501103"
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->cmsa,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "4",
                            "ACCOUNT_DESCRIPTION" => "CMSA fee on ".$description,
                            "DE_ACCNO" => "LB501101"
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->dse,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "5",
                            "ACCOUNT_DESCRIPTION" => "DSE fee on ".$description,
                            "DE_ACCNO" => "LB501104"
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->cds,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "7",
                            "ACCOUNT_DESCRIPTION" => "CDS fee on ".$description,
                            "DE_ACCNO" => "LB501105"
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->other_charges,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "8",
                            "ACCOUNT_DESCRIPTION" => "Bank charges on ".$description,
                            "DE_ACCNO" => "EX280100"
                        ]
                    ]
                ]
            ];
        }
        return json_encode($payload);
    }

    private static function generatePayloadContractNoteBondSellPrimary(SimpleTransaction $simpleTransaction, Transaction $transaction,BondExecution $payloadRaw): string
    {
        $payout = self::_getPayoutBondBuy($payloadRaw);
        $dailySettlement = "LB501200";
        $customer = User::find($payloadRaw->client_id);
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->executed.' @'.$payloadRaw->price;

        if($payloadRaw->other_charges < 1){
            $payload = [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "D",
                    "DE_AMOUNT" => $payout,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payout,
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => $dailySettlement,
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                    "Mltoffsetdetail" =>  [
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->payout,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "1",
                            "ACCOUNT_DESCRIPTION" => $description,
                            "DE_ACCNO" => $customer->flex_acc_no
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->brokerage,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "2",
                            "ACCOUNT_DESCRIPTION" => "Brokerage fee on ".$description,
                            "DE_ACCNO" => "IN501102"
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->vat,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "3",
                            "ACCOUNT_DESCRIPTION" => "VAT fee on ".$description,
                            "DE_ACCNO" => "LB501103"
                        ]
                    ]
                ]
            ];
        }else{

            $payload = [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "D",
                    "DE_AMOUNT" => $payloadRaw->amount,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payloadRaw->amount,
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => $dailySettlement,
                    "Mltoffsetdetail" =>  [
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->payout,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "1",
                            "ACCOUNT_DESCRIPTION" => $description,
                            "DE_ACCNO" => $customer->flex_acc_no
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->brokerage,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "2",
                            "ACCOUNT_DESCRIPTION" => "Brokerage fee on ".$description,
                            "DE_ACCNO" => "IN501101"
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->vat,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "3",
                            "ACCOUNT_DESCRIPTION" => "VAT fee on ".$description,
                            "DE_ACCNO" => "LB501103"
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->other_charges,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "8",
                            "ACCOUNT_DESCRIPTION" => "Bank charges on ".$description,
                            "DE_ACCNO" => "EX280100"
                        ]
                    ]
                ]
            ];
        }
        return json_encode($payload);
    }

    private static function generatePayloadContractNoteBondCustodianBuySecondary(SimpleTransaction $simpleTransaction, Transaction $transaction, BondExecution $payloadRaw): string
    {
        $totalCharges = self::_getTotalChargesBond($payloadRaw);
        $account = Custodian::findOrFail($payloadRaw->custodian_id);
        $batch = self::_generateBatch($payloadRaw->reference);

        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->executed.' @'.$payloadRaw->price;

        $payload = [
            "SOURCE" => getenv("FLEX_SOURCE"),
            "USERID" => getenv("FLEX_USER_ID"),
            "BRANCH" => "002",
            "Multioffsetmaster-Full" => [
                "DE_BATCH_NUMBER" => $batch,
                "DE_CURRNO" => "1",
                "DE_CCY_CD" => "TZS",
                "DE_MAIN" => "000",
                "DE_OFFSET" => "000",
                "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                "DE_DR_CR" => "D",
                "DE_AMOUNT" => $totalCharges,
                "DE_EXCH_RATE" => "1",
                "DE_LCY_AMOUNT" => $totalCharges,
                "MAKERID" => "",
                "DE_AUTHORIZED_BY" => "",
                "DE_DATETIME" => now()->toDateTimeString(),
                "AUTHSTAT" => "",
                "DE_MAKER_DATETIME" => "",
                "DE_DESCRIPTION" => $description,
                "DE_ADDL_TEXT" => $description,
                "DE_BATCH_DESC" => "WEB TEST",
                "DE_ACCNO" => $account->ledger,
                "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                "Mltoffsetdetail" =>  [
                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->brokerage,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "2",
                        "ACCOUNT_DESCRIPTION" => "Brokerage fee on ".$description,
                        "DE_ACCNO" => "IN501102"
                    ],
                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->vat,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "3",
                        "ACCOUNT_DESCRIPTION" => "VAT fee on ".$description,
                        "DE_ACCNO" => "LB501103"
                    ],
                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->cmsa,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "4",
                        "ACCOUNT_DESCRIPTION" => "CMSA fee on ".$description,
                        "DE_ACCNO" => "LB501101"
                    ],
                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->dse,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "5",
                        "ACCOUNT_DESCRIPTION" => "DSE fee on ".$description,
                        "DE_ACCNO" => "LB501104"
                    ],
                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->cds,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "7",
                        "ACCOUNT_DESCRIPTION" => "CDS fee on ".$description,
                        "DE_ACCNO" => "LB501105"
                    ]
                ]
            ]
        ];

        return json_encode($payload);
    }

    private static function generatePayloadContractNoteBondCustodianBuyPrimary(SimpleTransaction $simpleTransaction, Transaction $transaction, BondExecution $payloadRaw): string
    {
        $totalCharges = self::_getTotalChargesBond($payloadRaw);
        $account = Custodian::findOrFail($payloadRaw->custodian_id);
        $batch = self::_generateBatch($payloadRaw->reference);

        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->executed.' @'.$payloadRaw->price;

        $payload = [
            "SOURCE" => getenv("FLEX_SOURCE"),
            "USERID" => getenv("FLEX_USER_ID"),
            "BRANCH" => "002",
            "Multioffsetmaster-Full" => [
                "DE_BATCH_NUMBER" => $batch,
                "DE_CURRNO" => "1",
                "DE_CCY_CD" => "TZS",
                "DE_MAIN" => "000",
                "DE_OFFSET" => "000",
                "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                "DE_DR_CR" => "D",
                "DE_AMOUNT" => $totalCharges,
                "DE_EXCH_RATE" => "1",
                "DE_LCY_AMOUNT" => $totalCharges,
                "MAKERID" => "",
                "DE_AUTHORIZED_BY" => "",
                "DE_DATETIME" => now()->toDateTimeString(),
                "AUTHSTAT" => "",
                "DE_MAKER_DATETIME" => "",
                "DE_DESCRIPTION" => $description,
                "DE_ADDL_TEXT" => $description,
                "DE_BATCH_DESC" => "WEB TEST",
                "DE_ACCNO" => $account->ledger,
                "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                "Mltoffsetdetail" =>  [
                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->brokerage,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "2",
                        "ACCOUNT_DESCRIPTION" => "Brokerage fee on ".$description,
                        "DE_ACCNO" => "IN501102"
                    ],
                    [
                        "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                        "DE_AMOUNT" => $payloadRaw->vat,
                        "DE_BRANCH_CODE" => "002",
                        "DE_SERIAL_NUMBER" => "3",
                        "ACCOUNT_DESCRIPTION" => "VAT fee on ".$description,
                        "DE_ACCNO" => "LB501103"
                    ]
                ]
            ]
        ];

        return json_encode($payload);
    }

    private static function generatePayloadContractNoteBondCustodianSellSecondary(SimpleTransaction $simpleTransaction, Transaction $transaction, BondExecution $payloadRaw): string
    {
        $totalCharges = self::_getTotalChargesBond($payloadRaw);
        $account = Custodian::findOrFail($payloadRaw->custodian_id);
        $batch = self::_generateBatch($payloadRaw->reference);

        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->executed.' @'.$payloadRaw->price;

       if($payloadRaw->other_charges < 1){
           $payload = [
               "SOURCE" => getenv("FLEX_SOURCE"),
               "USERID" => getenv("FLEX_USER_ID"),
               "BRANCH" => "002",
               "Multioffsetmaster-Full" => [
                   "DE_BATCH_NUMBER" => $batch,
                   "DE_CURRNO" => "1",
                   "DE_CCY_CD" => "TZS",
                   "DE_MAIN" => "000",
                   "DE_OFFSET" => "000",
                   "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                   "DE_DR_CR" => "D",
                   "DE_AMOUNT" => $totalCharges,
                   "DE_EXCH_RATE" => "1",
                   "DE_LCY_AMOUNT" => $totalCharges,
                   "MAKERID" => "",
                   "DE_AUTHORIZED_BY" => "",
                   "DE_DATETIME" => now()->toDateTimeString(),
                   "AUTHSTAT" => "",
                   "DE_MAKER_DATETIME" => "",
                   "DE_DESCRIPTION" => $description,
                   "DE_ADDL_TEXT" => $description,
                   "DE_BATCH_DESC" => "WEB TEST",
                   "DE_ACCNO" => $account->ledger,
                   "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                   "Mltoffsetdetail" =>  [
                       [
                           "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                           "DE_AMOUNT" => $payloadRaw->brokerage,
                           "DE_BRANCH_CODE" => "002",
                           "DE_SERIAL_NUMBER" => "2",
                           "ACCOUNT_DESCRIPTION" => "Brokerage fee on ".$description,
                           "DE_ACCNO" => "IN501102"
                       ],
                       [
                           "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                           "DE_AMOUNT" => $payloadRaw->vat,
                           "DE_BRANCH_CODE" => "002",
                           "DE_SERIAL_NUMBER" => "3",
                           "ACCOUNT_DESCRIPTION" => "VAT fee on ".$description,
                           "DE_ACCNO" => "LB501103"
                       ],
                       [
                           "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                           "DE_AMOUNT" => $payloadRaw->cmsa,
                           "DE_BRANCH_CODE" => "002",
                           "DE_SERIAL_NUMBER" => "4",
                           "ACCOUNT_DESCRIPTION" => "CMSA fee on ".$description,
                           "DE_ACCNO" => "LB501101"
                       ],
                       [
                           "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                           "DE_AMOUNT" => $payloadRaw->dse,
                           "DE_BRANCH_CODE" => "002",
                           "DE_SERIAL_NUMBER" => "5",
                           "ACCOUNT_DESCRIPTION" => "DSE fee on ".$description,
                           "DE_ACCNO" => "LB501104"
                       ],
                       [
                           "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                           "DE_AMOUNT" => $payloadRaw->cds,
                           "DE_BRANCH_CODE" => "002",
                           "DE_SERIAL_NUMBER" => "7",
                           "ACCOUNT_DESCRIPTION" => "CDS fee on ".$description,
                           "DE_ACCNO" => "LB501105"
                       ]
                   ]
               ]
           ];
       }else{
           $payload = [
               "SOURCE" => getenv("FLEX_SOURCE"),
               "USERID" => getenv("FLEX_USER_ID"),
               "BRANCH" => "002",
               "Multioffsetmaster-Full" => [
                   "DE_BATCH_NUMBER" => $batch,
                   "DE_CURRNO" => "1",
                   "DE_CCY_CD" => "TZS",
                   "DE_MAIN" => "000",
                   "DE_OFFSET" => "000",
                   "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                   "DE_DR_CR" => "D",
                   "DE_AMOUNT" => $payloadRaw->total_fees + $payloadRaw->other_charges,
                   "DE_EXCH_RATE" => "1",
                   "DE_LCY_AMOUNT" => $payloadRaw->total_fees + $payloadRaw->other_charges,
                   "MAKERID" => "",
                   "DE_AUTHORIZED_BY" => "",
                   "DE_DATETIME" => now()->toDateTimeString(),
                   "AUTHSTAT" => "",
                   "DE_MAKER_DATETIME" => "",
                   "DE_DESCRIPTION" => $description,
                   "DE_ADDL_TEXT" => $description,
                   "DE_BATCH_DESC" => "WEB TEST",
                   "DE_ACCNO" => $account->ledger,
                   "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                   "Mltoffsetdetail" =>  [
                       [
                           "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                           "DE_AMOUNT" => $payloadRaw->brokerage,
                           "DE_BRANCH_CODE" => "002",
                           "DE_SERIAL_NUMBER" => "2",
                           "ACCOUNT_DESCRIPTION" => "Brokerage fee on ".$description,
                           "DE_ACCNO" => "IN501101"
                       ],
                       [
                           "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                           "DE_AMOUNT" => $payloadRaw->vat,
                           "DE_BRANCH_CODE" => "002",
                           "DE_SERIAL_NUMBER" => "3",
                           "ACCOUNT_DESCRIPTION" => "VAT fee on ".$description,
                           "DE_ACCNO" => "LB501103"
                       ],
                       [
                           "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                           "DE_AMOUNT" => $payloadRaw->cmsa,
                           "DE_BRANCH_CODE" => "002",
                           "DE_SERIAL_NUMBER" => "4",
                           "ACCOUNT_DESCRIPTION" => "CMSA fee on ".$description,
                           "DE_ACCNO" => "LB501101"
                       ],
                       [
                           "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                           "DE_AMOUNT" => $payloadRaw->dse,
                           "DE_BRANCH_CODE" => "002",
                           "DE_SERIAL_NUMBER" => "5",
                           "ACCOUNT_DESCRIPTION" => "DSE fee on ".$description,
                           "DE_ACCNO" => "LB501104"
                       ],
                       [
                           "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                           "DE_AMOUNT" => $payloadRaw->cds,
                           "DE_BRANCH_CODE" => "002",
                           "DE_SERIAL_NUMBER" => "7",
                           "ACCOUNT_DESCRIPTION" => "CDS fee on ".$description,
                           "DE_ACCNO" => "LB501105"
                       ],
                       [
                       "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                       "DE_AMOUNT" => $payloadRaw->other_charges,
                       "DE_BRANCH_CODE" => "002",
                       "DE_SERIAL_NUMBER" => "8",
                       "ACCOUNT_DESCRIPTION" => "Bank charges on ".$description,
                       "DE_ACCNO" => "EX280100"
                     ]
                   ]
               ]
           ];
       }

        return json_encode($payload);
    }

    private static function generatePayloadContractNoteBondCustodianSellPrimary(SimpleTransaction $simpleTransaction, Transaction $transaction, BondExecution $payloadRaw): string
    {
        $totalCharges = self::_getTotalChargesBond($payloadRaw);
        $account = Custodian::findOrFail($payloadRaw->custodian_id);
        $batch = self::_generateBatch($payloadRaw->reference);

        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->executed.' @'.$payloadRaw->price;

        if($payloadRaw->other_charges < 1) {
            $payload = [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d", strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "D",
                    "DE_AMOUNT" => $totalCharges,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $totalCharges,
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => $account->ledger,
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/", "", $payloadRaw->reference),
                    "Mltoffsetdetail" => [
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/", "", $payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->brokerage,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "2",
                            "ACCOUNT_DESCRIPTION" => "Brokerage fee on " . $description,
                            "DE_ACCNO" => "IN501102"
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/", "", $payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->vat,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "3",
                            "ACCOUNT_DESCRIPTION" => "VAT fee on " . $description,
                            "DE_ACCNO" => "LB501103"
                        ]
                    ]
                ]
            ];
        }else {

            $payload = [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d", strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "D",
                    "DE_AMOUNT" => $payloadRaw->total_fees + $payloadRaw->other_charges,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payloadRaw->total_fees + $payloadRaw->other_charges,
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => $account->ledger,
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/", "", $payloadRaw->reference),
                    "Mltoffsetdetail" => [
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/", "", $payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->brokerage,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "2",
                            "ACCOUNT_DESCRIPTION" => "Brokerage fee on " . $description,
                            "DE_ACCNO" => "IN501101"
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/", "", $payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->vat,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "3",
                            "ACCOUNT_DESCRIPTION" => "VAT fee on " . $description,
                            "DE_ACCNO" => "LB501103"
                        ],
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->other_charges,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "8",
                            "ACCOUNT_DESCRIPTION" => "Bank charges on ".$description,
                            "DE_ACCNO" => "EX280100"
                        ]
                    ]
                ]
            ];
        }
        return json_encode($payload);
    }

    public static function postContractNoteBondProBook(SimpleTransaction $simpleTransaction, Transaction $transaction, BondExecution $payloadRaw): object
    {
        if(!empty($payloadRaw->custodian_id)){
            $result = new \stdClass();
            $result->status = false;
            $result->code = "";
            $result->message =  "Posting Pro Book custodian is not available at the moment";
            return $result;
        }else{
            if(strtolower($payloadRaw->type == "sell")){
                if(strtolower($payloadRaw->market) == "secondary") {
                    $payloads = self::generatePayloadContractNoteBondSellProBookSecondary($simpleTransaction, $transaction, $payloadRaw);
                }else{
                    $payloads = self::generatePayloadContractNoteBondSellProBookPrimary($simpleTransaction, $transaction, $payloadRaw);
                }
            }else {
                if(strtolower($payloadRaw->market) == "secondary") {
                    $payloads = self::generatePayloadContractNoteBondBuyProBookSecondary($simpleTransaction, $transaction, $payloadRaw);
                }else{
                    $payloads = self::generatePayloadContractNoteBondBuyProBookPrimary($simpleTransaction, $transaction, $payloadRaw);
                }
            }
        }

        $result = new \stdClass();
        $result->status = false;
        $result->code = "";
        $result->message =  "";
        $result->data =  "";

        if(!empty($payloads)){
            foreach ($payloads as $payload){
                $result = self::_processPayload($simpleTransaction, $transaction,json_encode($payload));
            }
        }

        return $result;

    }

    private static function generatePayloadContractNoteBondSellProBookPrimary(SimpleTransaction $simpleTransaction, Transaction $transaction, BondExecution $payloadRaw): array
    {
        $dailySettlement = "LB501200";
        $investmentFund = "AS102122";
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->executed.' @'.$payloadRaw->price;

        return [
            [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "D",
                    "DE_AMOUNT" => $payloadRaw->amount,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payloadRaw->amount,
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => $dailySettlement,
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                    "Mltoffsetdetail" =>  [
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->amount,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "1",
                            "ACCOUNT_DESCRIPTION" => $description,
                            "DE_ACCNO" => $investmentFund
                        ]
                    ]
                ]
            ],
        ];
    }

    private static function generatePayloadContractNoteBondSellProBookSecondary(SimpleTransaction $simpleTransaction, Transaction $transaction, BondExecution $payloadRaw): array
    {
        $dailySettlement = "LB501200";
        $investmentFund = "AS102122";
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->executed.' @'.$payloadRaw->price;

        return [
            [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "D",
                    "DE_AMOUNT" => $payloadRaw->amount,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payloadRaw->amount,
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => $dailySettlement,
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                    "Mltoffsetdetail" =>  [
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->amount,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "1",
                            "ACCOUNT_DESCRIPTION" => $description,
                            "DE_ACCNO" => $investmentFund
                        ]
                    ]
                ]
            ],
            [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "D",
                    "DE_AMOUNT" => $payloadRaw->cmsa,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payloadRaw->cmsa,
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => "EX340200",
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                    "Mltoffsetdetail" =>  [
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->cmsa,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "4",
                            "ACCOUNT_DESCRIPTION" => "CMSA fee on ".$description,
                            "DE_ACCNO" => "LB501101"
                        ],
                    ]
                ]
            ],
            [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "D",
                    "DE_AMOUNT" => $payloadRaw->dse,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payloadRaw->dse,
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => "EX340100",
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                    "Mltoffsetdetail" =>  [
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->dse,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "4",
                            "ACCOUNT_DESCRIPTION" => "DSE fee on ".$description,
                            "DE_ACCNO" => "LB501104"
                        ],
                    ]
                ]
            ],
            [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "D",
                    "DE_AMOUNT" => $payloadRaw->cds,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payloadRaw->cds,
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => "EX340300",
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                    "Mltoffsetdetail" =>  [
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->cds,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "4",
                            "ACCOUNT_DESCRIPTION" => "CDS fee on ".$description,
                            "DE_ACCNO" => "LB501105"
                        ],
                    ]
                ]
            ],
        ];
    }

    private static function generatePayloadContractNoteBondBuyProBookPrimary(SimpleTransaction $simpleTransaction, Transaction $transaction, BondExecution $payloadRaw): array
    {
        $dailySettlement = "LB501200";
        $investmentFund = "AS102122";
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->executed.' @'.$payloadRaw->price;

        return [
            [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "C",
                    "DE_AMOUNT" => $payloadRaw->amount,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payloadRaw->amount,
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => $dailySettlement,
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                    "Mltoffsetdetail" =>  [
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->amount,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "1",
                            "ACCOUNT_DESCRIPTION" => $description,
                            "DE_ACCNO" => $investmentFund
                        ]
                    ]
                ]
            ]
        ];
    }

    private static function generatePayloadContractNoteBondBuyProBookSecondary(SimpleTransaction $simpleTransaction, Transaction $transaction, BondExecution $payloadRaw): array
    {
        $dailySettlement = "LB501200";
        $investmentFund = "AS102122";
        $batch = self::_generateBatch($payloadRaw->reference);
        $description = $transaction->uid.' '.$transaction->reference.' '.$simpleTransaction->particulars.' '.$payloadRaw->executed.' @'.$payloadRaw->price;

        return [
            [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "C",
                    "DE_AMOUNT" => $payloadRaw->amount,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payloadRaw->amount,
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => $dailySettlement,
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                    "Mltoffsetdetail" =>  [
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->amount,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "1",
                            "ACCOUNT_DESCRIPTION" => $description,
                            "DE_ACCNO" => $investmentFund
                        ]
                    ]
                ]
            ],
            [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "D",
                    "DE_AMOUNT" => $payloadRaw->cmsa,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payloadRaw->cmsa,
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => "EX340200",
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                    "Mltoffsetdetail" =>  [
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->cmsa,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "4",
                            "ACCOUNT_DESCRIPTION" => "CMSA fee on ".$description,
                            "DE_ACCNO" => "LB501101"
                        ],
                    ]
                ]
            ],
            [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "D",
                    "DE_AMOUNT" => $payloadRaw->dse,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payloadRaw->dse,
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => "EX340100",
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                    "Mltoffsetdetail" =>  [
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->dse,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "4",
                            "ACCOUNT_DESCRIPTION" => "DSE fee on ".$description,
                            "DE_ACCNO" => "LB501104"
                        ],
                    ]
                ]
            ],
            [
                "SOURCE" => getenv("FLEX_SOURCE"),
                "USERID" => getenv("FLEX_USER_ID"),
                "BRANCH" => "002",
                "Multioffsetmaster-Full" => [
                    "DE_BATCH_NUMBER" => $batch,
                    "DE_CURRNO" => "1",
                    "DE_CCY_CD" => "TZS",
                    "DE_MAIN" => "000",
                    "DE_OFFSET" => "000",
                    "DE_VALUE_DATE" => date("Y-m-d",strtotime($payloadRaw->settlement_date)),
                    "DE_DR_CR" => "D",
                    "DE_AMOUNT" => $payloadRaw->cds,
                    "DE_EXCH_RATE" => "1",
                    "DE_LCY_AMOUNT" => $payloadRaw->cds,
                    "MAKERID" => "",
                    "DE_AUTHORIZED_BY" => "",
                    "DE_DATETIME" => now()->toDateTimeString(),
                    "AUTHSTAT" => "",
                    "DE_MAKER_DATETIME" => "",
                    "DE_DESCRIPTION" => $description,
                    "DE_ADDL_TEXT" => $description,
                    "DE_BATCH_DESC" => "WEB TEST",
                    "DE_ACCNO" => "EX340300",
                    "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                    "Mltoffsetdetail" =>  [
                        [
                            "DE_INSTRUMENT_NUMBER" => str_ireplace("/","",$payloadRaw->reference),
                            "DE_AMOUNT" => $payloadRaw->cds,
                            "DE_BRANCH_CODE" => "002",
                            "DE_SERIAL_NUMBER" => "4",
                            "ACCOUNT_DESCRIPTION" => "CDS fee on ".$description,
                            "DE_ACCNO" => "LB501105"
                        ],
                    ]
                ]
            ],
        ];
    }

    private static function apiClient(): PendingRequest
    {
        return Http::acceptJson()
            ->contentType('application/json');
    }

    private static function baseUrl(): string
    {
        return getenv("FLEX_URL");
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

    private static function _processPayload(SimpleTransaction $simpleTransaction, Transaction $transaction,string $payload): object
    {
        $result = new \stdClass();
        try{
            // check for account status
//            $accountCheck = json_decode($payload);
//            $account = \DB::table("user")->find($accountCheck->client_id);
//            $accountPayload = [
//                "Customer_Account_Number" => $account->flex_acc_no
//            ];
//            $accountCheckEndPoint = self::baseUrl()."/createdemultioffset";
//            $accountCheckResponse = self::apiClient()
//                ->withBody(json_encode($accountPayload))
//                ->send('POST', $accountCheckEndPoint)
//                ->onError(function ($error) {
//                    return $error;
//                });
//
//            $accountCheckResult = (object) json_decode($accountCheckResponse->body());
//            if($accountCheckResult->Status_Code == 200 && $accountCheckResult->MSGSTAT == "SUCCESS"){
//                $accountDetails = $accountCheckResult[0]->Customer_Type;
//                $result->status = false;
//                $result->code = "";
//                $result->message =  $accountDetails;
//                return $result;
//            }else{
//                $result->status = false;
//                $result->code = "";
//                $result->message =  $accountCheckResult->Message[0]->DESC;
//                return $result;
//            }

           $checkInput = json_decode($payload);
           $check = FlexcubeTransactions::where("instrument_code",$transaction->reference)->first();
           if(!empty($check->instrument_code)){
               $result->status = false;
               $result->code = "";
               $result->message =  "Duplicate Entry";
               return $result;
           }


            \Log::info("Payload ".$payload);

            $endpoint = self::baseUrl()."/createdemultioffset";
            $response = self::apiClient()
                ->withBody($payload)
                ->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            $result = (object) json_decode($response->body());

            $payload = json_decode($payload);
            \Log::info("Response ".$response->body());
            return self::processResult($simpleTransaction, $transaction, $result, $payload);

        }catch (\Throwable $throwable){
            \Log::info($throwable->getMessage());
            report($throwable);
            $result->status = false;
            $result->code = "";
            $result->message =  $throwable->getMessage();
            return $result;
        }

    }

    private static function _testDebitCreditStock(DealingSheet $dealingSheet): \stdClass
    {
        $totalCharges = $dealingSheet->brokerage + $dealingSheet->vat + $dealingSheet->cds + $dealingSheet->dse + $dealingSheet->fidelity + $dealingSheet->cmsa + $dealingSheet->other_charges;

        if(strtolower($dealingSheet->type) == "buy"){
            $computedPayout = round($dealingSheet->amount + $totalCharges,1);
        }else{
            $computedPayout = round($dealingSheet->amount - $totalCharges,1);
        }


        $payout = round($dealingSheet->payout,1);
        $result = new \stdClass();
        $result->status = true;
//        if($payout != $computedPayout){
//            $result->status = false;
//            $result->message =  "Debit Credit mismatch $payout $computedPayout";
//        }else{
//            $result->status = true;
//        }
        return $result;
    }

    private static function _testDebitCreditBond(BondExecution $dealingSheet): \stdClass
    {
        $totalCharges = $dealingSheet->brokerage + $dealingSheet->vat + $dealingSheet->cds + $dealingSheet->dse + $dealingSheet->fidelity + $dealingSheet->cmsa + $dealingSheet->other_charges;

        if(strtolower($dealingSheet->type) == "buy"){
            $computedPayout = round($dealingSheet->amount + $totalCharges);
        }else{
            $computedPayout = round($dealingSheet->amount - $totalCharges);
        }

        $payout = round($dealingSheet->payout);
        $result = new \stdClass();
        if($payout != $computedPayout){
            $result->status = false;
            $result->message =  "Debit Credit mismatch $computedPayout $payout";
        }else{
            $result->status = true;
        }
        return $result;
    }

    private static function _getPayoutBondSell(BondExecution $dealingSheet): float
    {
        $totalCharges = $dealingSheet->brokerage
            + $dealingSheet->vat
            + $dealingSheet->cds
            + $dealingSheet->dse
            + $dealingSheet->fidelity
            + $dealingSheet->cmsa
            + $dealingSheet->other_charges;

        $total = $dealingSheet->amount - $totalCharges;
        return  (float) number_format($total,"2", '.', '');

    }

    private static function _getPayoutBondBuy(BondExecution $dealingSheet): float
    {
        $totalCharges = $dealingSheet->brokerage
            + $dealingSheet->vat
            + $dealingSheet->cds
            + $dealingSheet->dse
            + $dealingSheet->fidelity
            + $dealingSheet->cmsa;

        $total = $dealingSheet->amount + $totalCharges;
        return  (float) number_format($total,"2", '.', '');
    }

    private static function _getTotalChargesStock(DealingSheet $dealingSheet)
    {
        $charges = $dealingSheet->brokerage
            + $dealingSheet->vat
            + $dealingSheet->cds
            + $dealingSheet->dse
            + $dealingSheet->fidelity
            + $dealingSheet->cmsa;

        \Log::info(" brokerage $dealingSheet->brokerage");
        \Log::info(" vat $dealingSheet->vat");
        \Log::info(" cds $dealingSheet->cds");
        \Log::info(" fidelity $dealingSheet->fidelity");
        \Log::info(" cmsa $dealingSheet->cmsa");
        \Log::info(" other charges $dealingSheet->cmsa");
        \Log::info(" total $charges");

        return  $charges;

    }

    private static function _getTotalChargesBond(BondExecution $dealingSheet): float
    {
        $charges = $dealingSheet->brokerage
            + $dealingSheet->vat
            + $dealingSheet->cds
            + $dealingSheet->dse
            + $dealingSheet->fidelity
            + $dealingSheet->cmsa
            + $dealingSheet->other_charges;
        return  (float) number_format($charges,"2", '.', '');

    }

    private static function _getPayoutStockBuy(DealingSheet $dealingSheet): float
    {
        $totalCharges = $dealingSheet->brokerage
            + $dealingSheet->vat
            + $dealingSheet->cds
            + $dealingSheet->dse
            + $dealingSheet->fidelity
            + $dealingSheet->cmsa;
        $total = $dealingSheet->amount + $totalCharges;
        return  $total;
    }

    private static function _getPayoutStockSell(DealingSheet $dealingSheet): float
    {
        $totalCharges = $dealingSheet->brokerage
            + $dealingSheet->vat
            + $dealingSheet->cds
            + $dealingSheet->dse
            + $dealingSheet->fidelity
            + $dealingSheet->cmsa + $dealingSheet->other_charges;
        $total = $dealingSheet->payout + $totalCharges;
        return  $total;
    }

   static function numberFormatPrecision($number, $precision = 2, $separator = '.')
    {
        $numberParts = explode($separator, $number);
        $response = $numberParts[0];
        if (count($numberParts)>1 && $precision > 0) {
            $response .= $separator;
            $response .= substr($numberParts[1], 0, $precision);
        }
        return $response;
    }

}
