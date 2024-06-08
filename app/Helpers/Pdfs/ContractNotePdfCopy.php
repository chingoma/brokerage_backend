<?php
/*
 * Copyright (c) 2022. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace App\Helpers\Pdfs;

use App\Helpers\Helper;
use App\Models\DealingSheet;
use Illuminate\Support\Facades\File;
use Modules\Orders\Entities\Order;
use TCPDF;

class ContractNotePdfCopy extends TCPDF
{
    protected string $font = 'helvetica';

    protected int $fontSize = 10;

    protected string $type = '';

    public $file;

    //Page header
    public function Header()
    {
        // Logo
        //        $image_file =public_path('business/background.png');
        //        $this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        //        // Set font
        //        $this->SetFont('helvetica', 'B', 20);
        //        // Title
        //        $this->Cell(0, 15, env("APP_NAME"), 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    // Page footer
    public function Footer()
    {
        // Position at 15 mm from bottom
        //        $this->SetY(-15);
        //        // Set font
        //        $this->SetFont('helvetica', 'I', 8);
        //        // Page number
        //        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }

    public function create(Order $order, DealingSheet $dealingSheet): string
    {

        // create new PDF document
        // set document information
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor('Kelvin chingoma');
        $this->SetTitle('Contract Note');
        $this->SetSubject('Contract Note');
        $this->SetKeywords(env('KEYWORDS'));

        // set default monospaced font
        $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $this->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $this->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // ---------------------------------------------------------

        // set default font sub setting mode
        $this->setFontSubsetting(true);

        // Set font
        $this->SetFont(family: $this->font, size: $this->fontSize, fontfile: storage_path('fonts/nunito/static/Nunito-Black.ttf'), subset: true);

        // This method has several options, check the source code documentation for more information.
        $this->AddPage();

        // get the current page break margin
        $bMargin = $this->getBreakMargin();
        // get current auto-page-break mode
        $auto_page_break = $this->getAutoPageBreak();
        // disable auto-page-break
        $this->SetAutoPageBreak(false, 0);
        // set background image
        $this->Image(file: public_path('business/bg.png'), x: 0, y: 0, w: 210, h: 297, resize: false, ismask: false, imgmask: false);
        // restore auto-page-break status
        $this->SetAutoPageBreak($auto_page_break, $bMargin);
        // set the starting point for the page content
        $this->setPageMark();

        $this->Ln(10);
        $this->Cell(w: '', h: '', txt: 'ORDER NO: '.$dealingSheet->order_number, align: 'R');
        $this->Ln(10);
        $this->Cell(w: '', h: '', txt: 'FORM NO: '.$dealingSheet->uid, align: 'R');
        $this->Ln(10);

        if (strtolower($order->type) == 'buy') {
            $this->type = 'PURCHASE';
        } else {
            $this->type = 'SELL';
        }

        $this->Cell(w: '', h: '', txt: 'EQUITY '.$this->type.' TRADE CONFIRMATION & CONTRACT NOTE SUMMARY', ln: 1, align: 'C', valign: 'B');
        $this->Ln(7);

        $this->SetFont($this->font, '', 10, '', true);
        // Multicell test
        $this->MultiCell(70, 7, 'Exchange: DSE', 0, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(50, 7, 'Trade Date: '.date('Y-m-d', strtotime($dealingSheet->trade_date)), 0, 'R', 0, 1, '', '', true);
        $this->MultiCell(70, 7, 'Contract Summary Ref. No. '.$dealingSheet->slip_no, 0, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 7, 'Settlement Date: '.date('Y-m-d', strtotime($dealingSheet->settlement_date)), 0, 'R', 0, 1, '', '', true, valign: 'M');
        $this->Ln(7);
        $this->MultiCell(0, 7, 'Client Details:', 0, 'L', 0, 1, '', '', true, true, valign: 'M');
        $this->MultiCell(0, 7, 'Client Name:'.ucwords($order->client->name), 0, 'L', 0, 1, '', '', true, true, valign: 'M');
        $this->MultiCell(0, 7, 'Trading Account No: '.$order->client->uid, 0, 'L', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(0, 7, 'DSE CDS No: '.$order->client->dse_account, 0, 'L', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(0, 7, 'Custodian Name: '.$order->client->custodiam, 0, 'L', 0, 1, '', '', true, valign: 'M');
        $this->Ln(7);
        $this->MultiCell(0, 7, 'Security Details: '.$order->client->custodiam, 0, 'L', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(0, 7, 'Security Name: '.$order->security->name, 0, 'L', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(0, 7, 'Total Order Quantity : '.number_format($dealingSheet->order_total), 0, 'L', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(0, 7, 'No. of Shares Traded : '.number_format($dealingSheet->executed), 0, 'L', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(0, 7, 'Balance Order Quantity : '.number_format($dealingSheet->order_balance), 0, 'L', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(0, 7, 'Average Transaction Price: '.number_format($dealingSheet->price), 0, 'L', 0, 1, '', '', true, valign: 'M');
        $this->Ln(7);
        $this->MultiCell(0, 7, 'Gross Consideration', 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(0, 7, $dealingSheet->price.' @ '.$dealingSheet->volume, 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(0, 7, number_format($dealingSheet->price * $dealingSheet->executed), 1, 'L', 0, 1, '', '', true, valign: 'R');

        $this->MultiCell(0, 7, 'Add ', 1, 'L', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(120, 7, '', 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 7, 'TZS', 1, 'C', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(120, 7, 'Brokerage Commission up to TZS 10mn @ 1.7% ', 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 7, number_format($dealingSheet->commission_step_one), 1, 'C', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(120, 7, 'Brokerage Commission for the next TZS 40mn @ 1.5% ', 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 7, number_format($dealingSheet->commission_step_two), 1, 'C', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(120, 7, 'Brokerage Commission for the sum above TZS 70mn @ 0.8% ', 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 7, number_format($dealingSheet->commission_step_three), 1, 'C', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(120, 7, 'Total Brokerage Commission', 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 7, number_format($dealingSheet->brokerage), 1, 'C', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(120, 7, 'VAT @ 18%', 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 7, number_format($dealingSheet->vat), 1, 'C', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(120, 7, 'DSE Fee @ 0.14% ', 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 7, number_format($dealingSheet->dse), 1, 'C', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(120, 7, 'CMSA Fee @ 0.14%', 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 7, number_format($dealingSheet->cmsa), 1, 'C', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(120, 7, 'Fidelity Fee @ 0.02% ', 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 7, number_format($dealingSheet->fidelity), 1, 'C', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(120, 7, 'CDS Fee @ 0.06%', 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 7, number_format($dealingSheet->cds), 1, 'C', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(120, 7, 'Total Charges (TZS) ', 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 7, number_format($dealingSheet->total_fees), 1, 'C', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(120, 7, 'TOTAL CONSIDERATION: ', 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 7, number_format($dealingSheet->payout), 1, 'C', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(0, 7, '', 1, 'C', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(0, 7, 'This is electronic generated document it is valid without a seal', 1, 'L', 0, 1, '', '', true, '', true, '', '', 'M');

        $path = Helper::storageArea().'contract_notes';
        File::isDirectory($path) or File::makeDirectory($path, 0777, true, true);
        $this->Output($path.'/contract-note.pdf', 'F');
        $this->file = $path.'/contract-note.pdf';

        return $this->file;
    }
}
