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
use App\Models\User;
use Goat1000\SVGGraph\SVGGraph;
use Illuminate\Support\Facades\File;
use TCPDF;

class CustomerHoldingReportPdf extends TCPDF
{
    protected string $font = 'helvetica';

    protected int $fontSize = 8;

    protected User $client;

    protected $colours = ['#003a6c', '#f06413', '#f06413'];

    /**
     * @param  bool  $generateFile
     */
    public function __construct(User $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    public function Header()
    {
        $image_file = public_path('business/landscape_header.png');
        $this->Image($image_file, -3, 0, 210, 0, '', '', 'C', true, 300, '', false, false, 0);
        //        $image_file = public_path('business/background_icon.png');
        //        $this->Image($image_file, 75, 120, 60, 60, '', "", 'C', true, 300, '', false, false, 0);
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

    public function create(): string
    {
        //            Helper::pdfSignature($this);
        //            Helper::pdfProtection($this);

        // set default monospaced font
        $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $this->SetMargins(PDF_MARGIN_LEFT, 42, PDF_MARGIN_RIGHT);
        $this->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->SetFooterMargin(PDF_MARGIN_FOOTER);
        $this->setPageOrientation('P');

        // set default font sub setting mode
        $this->setFontSubsetting(true);

        // Set font
        $this->SetFont(family: $this->font, size: $this->fontSize, fontfile: storage_path('fonts/nunito/static/Nunito-Black.ttf'), subset: true);

        // This method has several options, check the source code documentation for more information.
        $this->AddPage();

        $this->SetFont(family: $this->font, style: 'B', size: 12, subset: true);
        $this->Cell(w: '', h: '', txt: 'CLIENT VALUATION STATEMENT ', ln: 1, align: 'C');
        $this->Ln(5);

        $customer = new Profile($this->client->id);
        $signLeft = ($customer->wallet_balance < 0) ? '(' : '';
        $signRight = ($customer->wallet_balance < 0) ? ')' : '';
        $this->SetFont($this->font, '', 10, '', true);
        // Multicell test
        $this->MultiCell(0, 7, 'Name: '.ucwords($customer->name), 0, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(0, 7, 'Postal Address:  '.$customer->address, 0, 'L', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(0, 7, 'CDS Number:  '.$customer->dse_account, 0, 'L', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(0, 7, 'Wallet:  TZS '.$signLeft.number_format(abs($customer->wallet_balance), 2).$signRight, 0, 'L', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(0, 7, 'Email:  '.$customer->a_email, 0, 'L', 0, 1, '', '', true, valign: 'M');

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

        //            $this->Ln(80);
        $this->equitiesTable($this->client);
        $this->Ln(5);
        $this->bondsTable($this->client);

        $path = Helper::storageArea().'statements';
        File::isDirectory($path) or File::makeDirectory($path, 0777, true, true);
        $this->Output($path.'/customer-holding-report.pdf', 'F');
        $this->file = $path.'/customer-holding-report.pdf';

        return $this->file;

    }

    public function equitiesTable(User $client): void
    {
        $headerEquity = ['COMPANY', 'VOLUME', 'MARKET PRICE (TZS)', 'MARKET VALUE (TZS)'];
        $equities = [];
        $summary['total_equity'] = 0;
        $summary['total_equity_value'] = 0;
        $companies = \DB::table('dealing_sheets')
            ->where('client_id', $client->id)
            ->groupBy('security_id')
            ->pluck('security_id');
        if (count($companies) > 0) {
            if (! empty($companies)) {
                $j = 0;
                foreach ($companies as $i => $company) {
                    $dcompany = \DB::table('securities')->find($company);
                    $volume = Helper::customerCompanyShares($company, $client->id);
                    $dcompanyD = \DB::table('market_data')->where('company_id', $company)->latest('date')->first();

                    //                    if($volume > 0) {
                    $equities[$j]['name'] = $dcompany->name ?? '';
                    $equities[$j]['volume'] = $volume;
                    if (empty($dcompanyD)) {
                        $equities[$j]['market_price'] = 0;
                        $equities[$j]['market_value'] = $equities[$i]['volume'] * 0;
                    } else {
                        $equities[$j]['market_price'] = number_format($dcompanyD->close);
                        $equities[$j]['market_value'] = $equities[$i]['volume'] * $dcompanyD->close;
                    }

                    $j++;
                    //                    }
                }
            }
            if (! empty($equities)) {
                foreach ($equities as $equity) {
                    $summary['total_equity'] = $summary['total_equity'] + $equity['volume'];
                    $summary['total_equity_value'] = $summary['total_equity_value'] + $equity['market_value'];
                }
            }

            $this->SetFillColor(245, 245, 245);
            $this->SetTextColor(0);
            $this->SetFont('');
            // Data
            $fill = 0;
            $this->SetFont(family: $this->font, style: 'B', size: 8, subset: true);
            $this->Cell(180, 6, 'EQUITIES', 0, 1, 'L', 1, '', '', true, valign: 'C');

            // Colors, line width and bold font
            $this->SetFillColor(0, 58, 108);
            $this->SetTextColor(255);
            $this->SetDrawColor(0, 58, 108);
            $this->SetLineWidth(0.2);
            $this->SetFont('', 'B', size: 8);
            // Header
            $w = [45, 45, 45, 45];
            $num_headers = count($headerEquity);
            for ($i = 0; $i < $num_headers; $i++) {
                $this->Cell($w[$i], 7, $headerEquity[$i], 1, 0, 'C', 1);
            }
            $this->Ln();
            // Color and font restoration
            $this->SetFillColor(245, 245, 245);
            $this->SetTextColor(0);
            $this->SetFont('');
            // Data
            $fill = 0;

            $this->SetFont(family: $this->font, style: '', size: 8, subset: true);
            foreach ($equities as $data) {
                $data = (object) $data;
                if($data->volume > 0) {
                    $this->Cell($w[0], 6, strtoupper($data->name), 'LTB', 0, 'C', $fill);
                    $this->Cell($w[0], 6, number_format(str_ireplace(',', '', $data->volume), 2), 'LRTB', 0, 'C', $fill);
                    $this->Cell($w[0], 6, number_format(str_ireplace(',', '', $data->market_price), 2), 'LRTB', 0, 'C', $fill);
                    $this->Cell($w[0], 6, number_format(str_ireplace(',', '', $data->market_value), 2), 'LRTB', 0, 'C', $fill);
                    $this->Ln();
                    $fill = !$fill;
                }
            }
            $this->SetFont(family: $this->font, style: 'B', size: 8, subset: true);
            $this->Cell(45, 6, 'Total ', 1, 0, 'C', 1, '', '', valign: 'C');
            $this->Cell(45, 6, number_format($summary['total_equity'], 2), 1, 0, 'C', 1, '', '', valign: 'C');
            $this->Cell(45, 6, '', 1, 0, 'C', 1, '', '', valign: 'C');
            $this->Cell(45, 6, number_format($summary['total_equity_value'], 2), 1, 1, 'C', 1, '', '', valign: 'C');
        }

        if (! empty($equities)) {
            $pieValue = [];
            $legendTitles = [];
            foreach ($equities as $k => $equity) {
                $data = (object) $equity;
                $pieValue[$data->name] = $data->market_value;
                $legendTitles[$k] = $data->name;
            }
        }
        $options = [
            'legend_entries' => $legendTitles,
            'inner_text' => 'Equities',
            'auto_fit' => true,
            'back_colour' => '#003a6c',
            'back_stroke_width' => 0,
            'back_stroke_colour' => '#003a6c',
            'stroke_colour' => '#000',
            'label_colour' => '#fff',
            'pad_right' => 0,
            'pad_left' => 10,
            'link_base' => '/',
            'link_target' => '_top',
            'sort' => true,
            'show_labels' => false,
            'legend_entry_height' => 10,
            'legend_title' => 'Eqities',
            'legend_text_side' => 'left',
            'legend_position' => 'outer right -5 40',
            'legend_stroke_width' => 0,
            'legend_shadow_opacity' => 0,
            'label_font' => 'Arial',
            'label_font_size' => '10',
            'units_before_label' => '',
            'depth' => 20,
        ];
        $graph = new SVGGraph(300, 200, $options);
        $graph->colours($this->colours);
        $graph->values($pieValue);
        $output = $graph->fetch('ExplodedPie3DGraph');
        //        $this->ImageSVG('@' . $output, $x=5, $y=80, $w="80", $h="80", $link='', $align='', $palign='', $border=0, $fitonpage=false);
    }

    public function bondsTable(User $client): void
    {
        $bonds = [];
        $summary['total_bond'] = 0;
        $headerBond = ['BOND TYPE', 'COUPON', 'TENURE', 'FACE VALUE (TZS)'];
        $bondies = \DB::table('bond_executions')->where('client_id', $client->id)->groupBy('bond_id')->pluck('bond_id');

        if (count($bondies) > 0) {
            if (! empty($bondies)) {
                $j = 0;
                foreach ($bondies as $i => $bondy) {
                    $fValue = Helper::customerBondFaceValue($bondy, $client->id);
                    if ($fValue > 0) {
                        $bData = \DB::table('bonds')->find($bondy);
                        $unit = (strtolower($bData->category) == 'bond') ? 'Yrs' : 'Days';
                        $bonds[$j]['security_name'] = $bData->security_name ?? '';
                        $bonds[$j]['coupon'] = ($bData->coupon ?? '') == 'null' ? '-' : ($bData->coupon ?? '').'%';
                        $bonds[$j]['tenure'] = ($bData->tenure ?? '').' '.$unit;
                        $bonds[$j]['face_value'] = $fValue;
                        $j++;
                    }
                }
            }
            if (! empty($bonds)) {
                foreach ($bonds as $bond) {
                    $summary['total_bond'] = $summary['total_bond'] + $bond['face_value'];
                }
            }

            $this->Cell(180, 6, 'BONDS', 0, 1, 'L', 1, '', '', true, valign: 'C');

            // Colors, line width and bold font
            $this->SetFillColor(0, 58, 108);
            $this->SetTextColor(255);
            $this->SetDrawColor(0, 58, 108);
            $this->SetLineWidth(0.2);
            $this->SetFont('', 'B', size: 8);
            // Header
            $w = [45, 45, 45, 45];
            $num_headers = count($headerBond);
            for ($i = 0; $i < $num_headers; $i++) {
                $this->Cell($w[$i], 7, $headerBond[$i], 1, 0, 'C', 1);
            }
            $this->Ln();
            // Color and font restoration
            $this->SetFillColor(245, 245, 245);
            $this->SetTextColor(0);
            $this->SetFont('');
            // Data
            $fill = 0;
            $this->SetFont(family: $this->font, style: '', size: 8, subset: true);
            foreach ($bonds as $data) {
                $data = (object) $data;
                $this->Cell($w[0], 6, strtoupper($data->security_name), 'LTB', 0, 'C', $fill);
                $this->Cell($w[0], 6, strtoupper($data->coupon), 'LTB', 0, 'C', $fill);
                $this->Cell($w[0], 6, strtoupper($data->tenure), 'LTB', 0, 'C', $fill);
                $this->Cell($w[0], 6, number_format(str_ireplace(',', '', $data->face_value), 2), 'LRTB', 0, 'C', $fill);
                $this->Ln();
                $fill = ! $fill;
            }
            $this->SetFont(family: $this->font, style: 'B', size: 8, subset: true);
            $this->Cell(90, 6, '', 1, 0, 'R', 1, '', '', true, valign: 'C');
            $this->Cell(45, 6, 'Total', 1, 0, 'C', 1, '', '', true, valign: 'C');
            $this->Cell(45, 6, number_format($summary['total_bond'], 2), 1, 0, 'C', 1, '', '', true, valign: 'C');

            if (! empty($bonds)) {
                $pieValue = [];
                $legendTitles = [];
                foreach ($bonds as $k => $bond) {
                    $data = (object) $bond;
                    $pieValue[$data->security_name] = $data->face_value;
                    $legendTitles[$k] = $data->security_name;
                }
            }
            $options = [
                'legend_entries' => $legendTitles,
                'auto_fit' => true,
                'back_colour' => '#eee',
                'back_stroke_width' => 0,
                'back_stroke_colour' => '#eee',
                'stroke_colour' => '#000',
                'label_colour' => '#fff',
                'pad_right' => 20,
                'pad_left' => 20,
                'pad_bottom' => 50,
                'link_base' => '/',
                'link_target' => '_top',
                'sort' => true,
                'show_labels' => false,
                'legend_entry_height' => 10,
                'legend_title' => 'Bonds',
                'legend_text_side' => 'left',
                'legend_position' => 'outer bottom -5 40',
                'legend_stroke_width' => 0,
                'legend_columns' => 3,
                'aspect_ratio' => 'auto',
                'legend_shadow_opacity' => 0,
                'label_font' => 'Arial',
                'label_font_size' => '10',
                'units_before_label' => '',
                'inner_text' => 'Bonds',
                'depth' => 20,
            ];
            $graph = new SVGGraph(550, 400, $options);
            $graph->colours($this->colours);
            $graph->values($pieValue);
            $output = $graph->fetch('ExplodedPie3DGraph');
            //            $this->ImageSVG('@' . $output, $x=100, $y=100, $w="130", $h="130", $link='', $align='R', $palign='R', $border=1, $fitonpage=false);
        }
    }
}
