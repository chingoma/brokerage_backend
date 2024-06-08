<?php
/*
 * Copyright (c) 2022. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace App\Helpers\Pdfs;

use App\Helpers\Clients\Profile;
use App\Helpers\Helper;
use App\Models\DealingSheet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Modules\Bonds\Entities\BondExecution;
use stdClass;
use TCPDF;

class StatementPdf extends TCPDF
{
    protected string $font = 'helvetica';

    protected int $fontSize = 8;

    protected string $type = '';

    public string $file;

    protected $transaction_date;

    protected $title;

    public $generateFile = true;

    public array $statement = [];

    public string $category = '';

    public string $reference = '';
    //Page header

    public string $price;

    public string $quantity;

    public function __construct(bool $generateFile = true)
    {
        parent::__construct();
        $this->generateFile = $generateFile;
    }

    public function Header()
    {
        $image_file = public_path('business/landscape_header.png');
        $this->Image($image_file, 0, 0, 295, 0, '', '', 'C', true, 300, '', false, false, 0);
        //        $image_file = public_path('business/background_icon.png');
        //        $this->Image($image_file, 95, 80, 90, 90, '', "", 'C', true, 300, '', false, false, 0);
    }

    // Page footer
    public function Footer()
    {
        //         Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }

    /**
     * @return string|void
     */
    public function create($transactions, $client)
    {

        if ($this->generateFile) {

            Helper::pdfSignature($this);
            Helper::pdfProtection($this);

            // set default monospaced font
            $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // set margins
            $this->SetMargins(PDF_MARGIN_LEFT, 42, PDF_MARGIN_RIGHT);
            $this->SetHeaderMargin(PDF_MARGIN_HEADER);
            $this->SetFooterMargin(PDF_MARGIN_FOOTER);
            $this->setPageOrientation('L');

            // set auto page breaks

            // ---------------------------------------------------------

            // set default font sub setting mode
            $this->setFontSubsetting(true);

            // Set font
            $this->SetFont(family: $this->font, size: $this->fontSize, fontfile: storage_path('fonts/nunito/static/Nunito-Black.ttf'), subset: true);

            // This method has several options, check the source code documentation for more information.
            $this->AddPage();

            $this->SetFont(family: $this->font, style: 'B', size: 12, subset: true);
            $this->Cell(w: '', h: '', txt: 'CLIENT ACCOUNT STATEMENT ', ln: 1, align: 'C');
            $this->Ln(5);

            $this->SetFont($this->font, '', 10, '', true);
            // Multicell test
            $customer = new Profile($client->id);
            $this->MultiCell(0, 7, 'Name: '.ucwords($customer->name), 0, 'L', 0, 0, '', '', true, valign: 'M');
            $this->MultiCell(0, 7, 'Postal Address:  '.$customer->address, 0, 'L', 0, 1, '', '', true, valign: 'M');
            $this->MultiCell(0, 7, 'CDS Number:  '.$client->dse_account, 0, 'L', 0, 1, '', '', true, valign: 'M');
            //            $this->MultiCell(0, 7, 'Telephone Number (s):' . $client->contact_telephone, 0, 'L', 0, 1, '', '', true, valign: "M");
            //            $this->MultiCell(0, 7, 'Identification/ Passport No: ' . $client->identity, 0, 'L', 0, 1, '', '', true, valign: "M");
            //            $this->MultiCell(0, 7, 'Nationality: ' . $client->country, 0, 'L', 0, 1, '', '', true, valign: "M");
            $timestamp = now(getenv('TIMEZONE'))->timestamp;
            $day = date("d",$timestamp);
            $dayM = "";
            if(strlen($day) == 1){
                if($day == 1) {
                    $dayM = $day . '<sup>st</sup>';
                }elseif ($day == 2){
                    $dayM = $day . '<sup>nd</sup>';
                }elseif ($day == 3){
                    $dayM = $day . '<sup>rd</sup>';
                }else{
                    $dayM = $day . '<sup>th</sup>';
                }
            }else{
                $dayI = $day[1];
                if($dayI == 1) {
                    $dayM = $day . '<sup>st</sup>';
                }elseif ($dayI == 2){
                    $dayM = $day . '<sup>nd</sup>';
                }elseif ($dayI == 3){
                    $dayM = $day . '<sup>rd</sup>';
                }else{
                    $dayM = $day . '<sup>th</sup>';
                }
            }
            $this->MultiCell(0, 7, 'Generated on:  '.$dayM.' '.date("F",$timestamp).' '.date("Y",$timestamp).' at'.' '.date("H:i:s a",$timestamp).' ', 0, 'L', 0, 1, '', '', true, ishtml: true, valign: 'M');
            $this->Ln(5);
        }

        if (! empty($transactions)) {
            $balance = 0;
            foreach ($transactions as $key => $transaction) {
                $quantity = 0;
                $price = 0;
                $debit = 0;
                $credit = 0;
                $order_type = '';
                $order_id = '';

                $this->transaction_date = $transaction->transaction_date;
                $this->title = $transaction->title;

                if (strtolower($transaction->category) == 'receipt') {
                    $this->type = 'Wallet';
                    $price = 0;
                    $quantity = 0;
                    $balance += $transaction->amount;
                    $credit = $transaction->amount;
                    $amount = $transaction->amount;
                }

                if (strtolower($transaction->category) == 'payment') {
                    $this->type = 'Wallet';
                    $price = 0;
                    $quantity = 0;
                    $balance -= $transaction->amount;
                    $debit = $transaction->amount;
                    $amount = $transaction->amount;
                }

                if (strtolower($transaction->category) == 'order') {
                    $sheet = DB::table('dealing_sheets')
                        ->select(['securities.name', 'dealing_sheets.type', 'dealing_sheets.payout', 'dealing_sheets.executed', 'dealing_sheets.price', 'dealing_sheets.id'])
                        ->leftJoin('securities', 'dealing_sheets.security_id', '=', 'securities.id')
                        ->where('dealing_sheets.status', 'approved')
                        ->where('dealing_sheets.slip_no', $transaction->reference)
                        ->first();

                    $this->title = (strtolower($sheet->type) == 'buy') ? ' Purchase of '.$sheet->name.' shares ' : ' Sale of '.$sheet->name.' shares';
                    if (strtolower($sheet->type) == 'buy') {
                        $price = $sheet?->price;
                        $quantity = $sheet?->executed;
                        $balance -= $sheet->payout ?? 0;
                        $debit = $sheet->payout ?? 0;
                    } else {
                        $price = $sheet?->price;
                        $quantity = $sheet?->executed;
                        $balance += $sheet->payout ?? 0;
                        $credit = $sheet->payout ?? 0;
                    }

                    $amount = $sheet->payout ?? 0;
                    $order_type = 'equity';
                    $order_id = $sheet->id;
                    $this->type = 'Wallet';
                }

                if (strtolower($transaction->category) == 'bond') {
                    $sheet = DB::table('bond_executions')
                        ->select(['bonds.security_name', 'bond_executions.type', 'bond_executions.payout', 'bond_executions.executed', 'bond_executions.price', 'bond_executions.id'])
                        ->leftJoin('bonds', 'bond_executions.bond_id', '=', 'bonds.id')
                        ->where('bond_executions.status', 'approved')
                        ->where('bond_executions.slip_no', $transaction->reference)
                        ->first();

                    $this->title = (strtolower($sheet->type) == 'buy') ? ' Purchase of '.ucfirst($sheet->security_name ?? '').' Bond' : ' Sale of '.ucfirst($sheet->security_name ?? '').' Bond';

                    if (strtolower($sheet->type) == 'buy') {
                        $price = $sheet?->price;
                        $quantity = $sheet?->executed;
                        $balance -= $sheet->payout ?? 0;
                        $debit = $sheet->payout ?? 0;
                    } else {
                        $price = $sheet?->price;
                        $quantity = $sheet?->executed;
                        $balance += $sheet->payout ?? 0;
                        $credit = $sheet->payout ?? 0;
                    }

                    $amount = $sheet->payout ?? 0;
                    $order_type = 'bond';
                    $order_id = $sheet->id;
                    $this->type = 'Wallet';

                }

                if (strtolower($transaction->category) == 'custodian') {

                    $sheet = DB::table('dealing_sheets')
                        ->leftJoin('securities', 'dealing_sheets.security_id', '=', 'securities.id')
                        ->where('dealing_sheets.status', 'approved')
                        ->where('dealing_sheets.slip_no', $transaction->reference)
                        ->first();

                    if (! empty($sheet)) {
                        $order_type = 'equity';
                        $order_id = $sheet->id;
                        $this->title = (strtolower($sheet->type) == 'buy') ? ' Purchase of '.$sheet->name.' shares ' : ' Sale of '.$sheet->name.' shares';
                    }

                    if (empty($sheet)) {
                        $sheet = DB::table('bond_executions')
                            ->select(['bonds.security_name', 'bond_executions.type', 'bond_executions.payout', 'bond_executions.executed', 'bond_executions.price', 'bond_executions.id'])
                            ->leftJoin('bonds', 'bond_executions.bond_id', '=', 'bonds.id')
                            ->where('bond_executions.status', 'approved')
                            ->where('bond_executions.slip_no', $transaction->reference)
                            ->first();
                        $order_type = 'bond';
                        $order_id = $sheet->id;
                        $this->title = (strtolower($sheet->type) == 'buy') ? ' Purchase of '.$sheet->security_name.' Bond ' : ' Sale of '.$sheet->security_name.' Bond';
                    }

                    if (strtolower($sheet->type) == 'buy') {
                        $price = $sheet?->price;
                        $quantity = $sheet?->executed;
                        $debit = $sheet->payout ?? 0;
                    } else {
                        $price = $sheet?->price;
                        $quantity = $sheet?->executed;
                        $credit = $sheet->payout ?? 0;
                    }

                    $amount = $sheet->payout ?? 0;
                    $this->type = 'Custodian';
                }

                if (strtolower($transaction->category) == 'expense') {
                    $this->type = 'Wallet';
                    $price = 0;
                    $quantity = 0;
                    $balance += $transaction->amount;
                    $credit = $transaction->amount;
                }

                if (strtolower($transaction->category) == 'journal') {
                    $this->type = ucfirst($transaction->action);
                    $price = 0;
                    $quantity = 0;
                    $balance -= $transaction->amount;

                    if (strtolower($this->type) == 'debit') {
                        $debit = $transaction->amount;
                    } else {
                        $credit = $transaction->amount;
                    }
                }

                if (strtolower($transaction->category) == 'invoice') {
                    $this->type = 'Wallet';
                    $price = 0;
                    $quantity = 0;
                    $balance -= $transaction->amount;
                    $debit = $transaction->amount;
                }

                switch (strtolower($transaction->category)) {
                    case 'custodian':
                        $trade = DealingSheet::where('slip_no', $transaction->reference)->first();
                        if (empty($sheet)) {
                            $trade = BondExecution::where('slip_no', $transaction->reference)->first();
                        }
                        $this->reference = $trade->reference ?? $transaction->uid;
                        $this->category = ($trade->type == 'buy') ? 'PURCHASE' : 'SELL';
                    case 'order':
                        $trade = DealingSheet::where('slip_no', $transaction->reference)->first();
                        $this->reference = $trade->reference ?? $transaction->uid;
                        $this->category = ($trade->type == 'buy') ? 'PURCHASE' : 'SELL';
                        break;
                    case 'bond':
                        $tradeB = BondExecution::where('slip_no', $transaction->reference)->first();
                        $this->reference = $tradeB->reference ?? $transaction->uid;
                        $this->category = ($tradeB->type == 'buy') ? 'PURCHASE' : 'SELL';
                        break;
                    case 'receipt':
                        $this->category = 'RECEIPT';
                        $this->reference = $transaction->uid;
                        break;
                    case 'payment':
                        $this->category = 'WITHDRAW';
                        $this->reference = $transaction->uid;
                        break;
                    default:
                        $this->category = $transaction->category;
                        $this->reference = $transaction->uid;
                        break;
                }

                $this->statement[$key]['amount'] = $amount;
                $this->statement[$key]['order_type'] = $order_type;
                $this->statement[$key]['order_id'] = $order_id;
                $this->statement[$key]['trans_id'] = $transaction->id;
                $this->statement[$key]['trans_reference'] = $transaction->reference;
                $this->statement[$key]['trans_category'] = $transaction->category;
                $this->statement[$key]['date'] = date('Y-m-d', strtotime($transaction->transaction_date));
                $this->statement[$key]['raw_date'] = date('Y-m-d H:i:s', strtotime($transaction->transaction_date));
                $this->statement[$key]['type'] = strtoupper($this->type);
                $this->statement[$key]['category'] = $this->category;
                $this->statement[$key]['reference'] = strtoupper($this->reference);
                $this->statement[$key]['particulars'] = strtoupper($this->title);
                $this->statement[$key]['quantity'] = round($quantity, 2);
                $this->statement[$key]['price'] = round($price, 2);
                $this->statement[$key]['debit'] = round($debit, 2);
                $this->statement[$key]['credit'] = round($credit, 2);
                $this->statement[$key]['balance'] = round($balance, 2);
                $state = $balance > -1 ? 'Cr' : 'Dr';
                $this->statement[$key]['state'] = $state;
            }
        }

        if ($this->generateFile) {
            $header = ['DATE', 'TYPE', 'CATEGORY', 'REFERENCE', 'PARTICULARS', 'QUANTITY', 'PRICE', 'DEBIT (TZS)', 'CREDIT (TZS)', 'BALANCE (TZS)'];
            $this->ColoredTable($header, $this->statement);
            $path = Helper::storageArea().'statements';
            File::isDirectory($path) or File::makeDirectory($path, 0777, true, true);
            $this->Output($path.'/statement.pdf', 'F');
            $this->file = $path.'/statement.pdf';

            return $this->file;
        }

    }

    public function ColoredTable($header, $input): void
    {
        // Colors, line width and bold font
        $this->SetFillColor(0, 58, 108);
        $this->SetTextColor(255);
        $this->SetDrawColor(0, 58, 108);
        $this->SetLineWidth(0.2);
        $this->SetFont('', 'B', size: 8);
        // Header
        $w = [20, 20, 20, 26, 57, 27, 16, 27, 27, 30];
        $num_headers = count($header);
        for ($i = 0; $i < $num_headers; $i++) {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', 1);
        }
        $this->Ln();
        // Color and font restoration
        $this->SetFillColor(245, 245, 245);
        $this->SetTextColor(0);
        $this->SetFont('');
        // Data
        $this->SetFont(family: $this->font, style: 'B', size: 8, subset: true);
        $this->MultiCell(270, 6, 'Opening Balance 0 <b>Cr</b>', 1, 'R', 0, 1, '', '', true, ishtml: true, valign: 'C');
        $fill = 0;
        $this->SetFont(family: $this->font, style: '', size: 8, subset: true);
        $data = new stdClass();
        $data->balance = 0;
        $data->state = 'Cr';
        foreach ($input as $data) {
            $data = (object) $data;
            $this->Cell($w[0], 6, $data->date, 'LTB', 0, 'L', $fill);
            $this->Cell($w[1], 6, $data->type, 'LTB', 0, 'L', $fill);
            $this->Cell($w[2], 6, $data->category, 'LTB', 0, 'L', $fill);
            $this->Cell($w[3], 6, $data->reference, 'LTB', 0, 'L', $fill);
            $this->Cell($w[4], 6, $data->particulars, 'LTB', 0, 'L', $fill);
            $this->Cell($w[5], 6, number_format(str_ireplace(',', '', $data->quantity), 2), 'LTB', 0, 'R', $fill);
            $this->Cell($w[6], 6, number_format(str_ireplace(',', '', $data->price), 2), 'LTB', 0, 'R', $fill);
            $this->Cell($w[7], 6, number_format(str_ireplace(',', '', $data->debit), 2), 'LTB', 0, 'R', $fill);
            $this->Cell($w[8], 6, number_format(str_ireplace(',', '', $data->credit), 2), 'LTB', 0, 'R', $fill);
            $this->Cell($w[9], 6, number_format(str_ireplace(',', '', abs($data->balance)), 2).' '.$data->state, 'LRTB', 0, 'R', $fill);
            $this->Ln();
            $fill = ! $fill;
        }
        $this->SetFont(family: $this->font, style: 'B', size: 8, subset: true);
        $this->MultiCell(270, 6, 'Closing Balance '.number_format(str_ireplace(',', '', $data->balance), 2).' <b>'.$data->state.'</b>', 1, 'R', 0, 1, '', '', true, ishtml: true, valign: 'C');
    }
}
