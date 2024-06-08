<?php
/*
 * Copyright (c) 2022. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace App\Helpers\Pdfs;

use App\Models\Accounting\Transaction;
use App\Models\User;
use TCPDF;

class ReceiptPdf extends TCPDF
{
    protected string $font = 'helvetica';

    protected int $fontSize = 10;

    protected string $type = '';

    public $file;

    //Page header
    public function Header()
    {

    }

    // Page footer
    public function Footer()
    {
    }

    public function create(Transaction $transaction): string
    {
        if (empty($transaction)) {
            exit();
        }

        $client = User::find($transaction->client_id)->profile;
        // create new PDF document
        // set document information
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor('Kelvin chingoma');
        $this->SetTitle($this->type.' ORDER FORM');
        $this->SetSubject('Subject to Rules and Practices of the DSE');
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

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once dirname(__FILE__).'/lang/eng.php';
            $this->setLanguageArray($l);
        }

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
        $this->Image(file: public_path('business/background.png'), x: 0, y: 0, w: 210, h: 297, resize: false, ismask: false, imgmask: false);
        // restore auto-page-break status
        $this->SetAutoPageBreak($auto_page_break, $bMargin);
        // set the starting point for the page content
        $this->setPageMark();

        $this->Ln(10);

        $this->SetFont(family: $this->font, style: 'B', size: 10, subset: true);
        $this->Cell(w: '', h: '', txt: 'RECEIPT', ln: 1, align: 'C');
        $this->Ln(7);

        $this->SetFont(family: $this->font, style: 'B', size: 10, subset: true);
        $this->Cell(w: 0, h: 0, txt: 'CLIENT DETAILS:', ln: 1, align: 'L');
        $this->Ln(2);

        $this->SetFont($this->font, '', 10, '', true);
        // Multicell test
        $this->MultiCell(0, 7, 'Name in Full: '.ucwords($client->name), 1, 'L', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(0, 7, 'Postal Address:  '.$client->address, 1, 'L', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(0, 7, 'CDS Number:  '.$client->dse_account, 1, 'L', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(0, 7, 'Telephone Number (s):'.$client->contact_telephone, 1, 'L', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(0, 7, 'Identification/ Passport No: '.$client->identity, 1, 'L', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(0, 7, 'Nationality: '.$client->country, 1, 'L', 0, 1, '', '', true, valign: 'M');

        $this->Ln(7);
        $this->SetFont($this->font, '', 10, '', true);
        $this->MultiCell(45, 7, 'Date', 1, 'C', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(45, 7, 'Reference', 1, 'C', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(45, 7, 'Particular', 1, 'C', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(45, 7, 'Amount', 1, 'C', 0, 1, '', '', true, valign: 'M');

        $this->MultiCell(45, 7, $transaction->transaction_date, 1, 'C', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(45, 7, $transaction->reference, 1, 'C', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(45, 7, $transaction->title, 1, 'C', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(45, 7, number_format($transaction->amount).' TZS', 1, 'C', 0, 1, '', '', true, valign: 'M');
        $this->Ln(7);

        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        $this->Output(public_path('files/receipt.pdf'), 'F');
        $this->file = asset('files/receipt.pdf');

        return public_path('files/receipt.pdf');
    }
}
