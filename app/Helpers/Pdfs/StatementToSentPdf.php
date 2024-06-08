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
use Illuminate\Support\Facades\File;
use TCPDF;

class StatementToSentPdf extends TCPDF
{
    protected string $font = 'helvetica';

    protected int $fontSize = 8;

    protected string $type = '';

    public string $file;

    public array $statement = [];

    public function __construct()
    {
        parent::__construct();
    }

    public function Header()
    {
        $image_file = public_path('business/bg-new.png');
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
     * @param  array  $statement
     */
    public function create($statement, $client): string
    {
        // set document information
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor('Kelvin Chingoma');
        $this->SetTitle('CLIENT STATEMENT ACCOUNT');
        $this->SetKeywords(env('KEYWORDS'));

        // set default monospaced font
        $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $this->SetMargins(PDF_MARGIN_LEFT, 42, PDF_MARGIN_RIGHT);
        $this->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->SetFooterMargin(PDF_MARGIN_FOOTER);
        $this->setPageOrientation('L');

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
        $this->Cell(135, 7, 'Name: '.ucwords($customer->name), 0, 0, 'L', 0, '', '', true, valign: 'M');
        $this->Cell(135, 7, 'Start: '.$statement[0]['date'], 0, 1, 'R', 0, '', '', true, valign: 'M');

        $this->Cell(135, 7, 'CDS Number:  '.$customer->dse_account, 0, 0, 'L', 0, '', '', true, valign: 'M');
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
                $dayM = $day . '<sup>th'.$dayI.'</sup>';
            }
        }
        $this->MultiCell(0, 7, 'Generated on:  '.$dayM.' '.date("F",$timestamp).' '.date("Y",$timestamp).' at'.' '.date("H:i:s a",$timestamp).' ', 0, 'L', 0, 1, '', '', true, ishtml: true, valign: 'M');
        $this->Ln(5);

        $header = ['DATE', 'TYPE', 'CATEGORY', 'REFERENCE', 'PARTICULARS', 'QUANTITY', 'PRICE', 'DEBIT (TZS)', 'CREDIT (TZS)', 'BALANCE (TZS)'];

        $this->ColoredTable($header, $statement);
        $path = Helper::storageArea().'statements';
        File::isDirectory($path) or File::makeDirectory($path, 0777, true, true);
        $this->Output($path.'/statement.pdf', 'F');
        $this->file = $path.'/statement.pdf';

        return $this->file;

    }

    public function ColoredTable($header, $input)
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
        $this->MultiCell(270, 6, 'Opening Balance '.$input[0]['balance'].'<b>'.$input[0]['state'].'</b>', 1, 'R', 0, 1, '', '', true, ishtml: true, valign: 'C');
        $fill = 0;
        $this->SetFont(family: $this->font, style: '', size: 8, subset: true);
        $balance = 0;
        $state = 'Cr';
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
            $this->Cell($w[9], 6, number_format(str_ireplace(',', '', $data->balance), 2).' '.$data->state, 'LRTB', 0, 'R', $fill);
            $this->Ln();
            $fill = ! $fill;
            $balance = $data->balance;
            $state = $data->state;
        }
        $this->SetFont(family: $this->font, style: 'B', size: 8, subset: true);
        $this->MultiCell(270, 6, 'Closing Balance '.number_format(str_ireplace(',', '', $balance), 2).' <b>'.$state.'</b>', 1, 'R', 0, 1, '', '', true, ishtml: true, valign: 'C');
    }
}
