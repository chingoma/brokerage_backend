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
use Illuminate\Support\Facades\File;
use Modules\Bonds\Entities\Bond;
use Modules\Bonds\Entities\BondExecution;
use Modules\Bonds\Entities\BondOrder;
use Modules\CRM\Entities\CustomerCategory;
use Modules\Custodians\Entities\Custodian;
use TCPDF;

class ContractNoteBondPdf extends TCPDF
{
    protected string $font = 'helvetica';

    protected int $fontSize = 10;

    protected string $type = '';

    protected string $typeT = '';

    public $file;

    private $payoutText = '';

    private $category;

    private User $user;

    private Bond $bond;

    //Page header
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
        $image_file = public_path('itrust-qr.png');
        $this->Image($image_file, 174, 262, 25, 25, '', '', 'C', true, 300, '', false, false, 0);
    }

    public function create(BondOrder $order, BondExecution $dealingSheet): string
    {

        Helper::pdfSignature($this);
        Helper::pdfProtection($this);

        $bond = Bond::find($order->bond_id);
        $custodian = strtoupper(Custodian::find($order->custodian_id)->name ?? '');
        $this->user = User::find($dealingSheet->client_id);
        $this->category = CustomerCategory::find($this->user->category_id);
        if (empty($this->category)) {
            $this->category = CustomerCategory::where('default', 'yes')->first();
        }

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
        //        $this->Image(file:public_path('business/bg.png'), x: 0, y:0, w:210, h:297, resize: false, ismask: false, imgmask: false);
        // restore auto-page-break status
        $this->SetAutoPageBreak($auto_page_break, $bMargin);
        // set the starting point for the page content
        $this->setPageMark();

        //    $this->Ln(10);
        //    $this->Cell(w:"",h: "", txt:"CDS Ref No. : ".$dealingSheet->slip_no,align: "R");
        //    $this->Ln(10);
        //    $this->Cell(w:"",h: "", txt:"FORM NO: ".$dealingSheet->uid,align: "R");
        $this->Ln(15);
        if (strtolower($order->type) == 'buy') {
            $this->type = 'PURCHASE';
            $this->typeT = 'PURCHASED';
            $this->payoutText = 'Payable';
        } else {
            $this->type = 'SALE';
            $this->typeT = 'SOLD';
            $this->payoutText = 'Receivable';
        }

        $profile = new Profile($order->client->id);
        $this->SetFont($this->font, 'BU', 7, '', true);
        $this->Cell(w: '', h: '', txt: strtoupper($order->bond->category ?? 'BOND').' '.$this->type.' TRADE CONFIRMATION & CONTRACT NOTE SUMMARY', ln: 1, align: 'C', valign: 'B');
        $this->Ln(3);

        $this->SetFont($this->font, '', 7, '', true);
        // Multicell test
        $this->MultiCell(45, 5, 'Exchange', 0, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(90, 5, ': DSE', 0, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(20, 5, 'Trade Date', 0, 'L', 0, 0, '', '', true);
        $this->MultiCell(25, 5, ': '.date('d-M-Y', strtotime($dealingSheet->trade_date)), 0, 'R', 0, 1, '', '', true);
        $this->MultiCell(45, 5, 'Transaction Ref. No.', 0, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(90, 5, ': '.$dealingSheet->reference, 0, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(20, 5, 'Settlement Date', 0, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(25, 5, ': '.date('d-M-Y', strtotime($dealingSheet->settlement_date)), 0, 'R', 0, 1, '', '', true, valign: 'M');

        $this->SetFont($this->font, 'BU', 7, '', true);
        $this->Ln(2);
        $this->MultiCell(0, 5, 'Client Details', 0, 'L', 0, 1, '', '', true, true, valign: 'M');
        $this->SetFont($this->font, '', 7, '', true);
        $this->MultiCell(45, 5, 'Client Name ', 0, 'L', 0, 0, '', '', true, true, valign: 'M');
        $this->MultiCell(135, 5, ': '.strtoupper($profile->name), 0, 'L', 0, 1, '', '', true, true, valign: 'M');
        $this->MultiCell(45, 5, 'Trading Account No ', 0, 'L', 0, 0, '', '', true, true, valign: 'M');
        $this->MultiCell(135, 5, ': '.$profile->flex_acc_no, 0, 'L', 0, 1, '', '', true, true, valign: 'M');
        $this->MultiCell(45, 5, 'DSE CDS No ', 0, 'L', 0, 0, '', '', true, true, valign: 'M');
        $this->MultiCell(135, 5, ': '.$order->client->dse_account, 0, 'L', 0, 1, '', '', true, true, valign: 'M');
        if (! empty($custodian)) {
            $this->MultiCell(45, 5, 'Custodian Name ', 0, 'L', 0, 0, '', '', true, true, valign: 'M');
            $this->MultiCell(135, 5, ': '.ucwords($custodian), 0, 'L', 0, 1, '', '', true, true, valign: 'M');
        }

        $this->SetFont($this->font, 'BU', 7, '', true);
        $this->Ln(2);

        $this->MultiCell(0, 5, 'Security Details', 0, 'L', 0, 1, '', '', true, true, valign: 'M');
        $this->SetFont($this->font, '', 7, '', true);
        $this->MultiCell(45, 5, 'Security Name ', 0, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(135, 5, ': '.strtoupper($bond->security_name), 0, 'L', 0, 1, '', '', true, valign: 'M');
        $this->MultiCell(45, 5, 'Security ID ', 0, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(135, 5, ': '.strtoupper($bond->isin), 0, 'L', 0, 1, '', '', true, valign: 'M');

        $this->MultiCell(45, 5, 'Bond Number', 0, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(135, 5, ': '.strtoupper($bond->number), 0, 'L', 0, 1, '', '', true, valign: 'M');

        $this->MultiCell(45, 5, 'Holding Number', 0, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(135, 5, ': ', 0, 'L', 0, 1, '', '', true, valign: 'M');

        $this->MultiCell(45, 5, 'Face Value', 0, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(135, 5, ': '.number_format($order->face_value, 2), 0, 'L', 0, 1, '', '', true, valign: 'M');

        $this->MultiCell(45, 5, 'Issue Date', 0, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(135, 5, ': '.strtoupper($bond->issue_date), 0, 'L', 0, 1, '', '', true, valign: 'M');

        $this->MultiCell(45, 5, 'Maturity Date', 0, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(135, 5, ': '.strtoupper($bond->maturity_date), 0, 'L', 0, 1, '', '', true, valign: 'M');

        $this->MultiCell(45, 5, 'Coupon', 0, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(135, 5, ': '.strtoupper($bond->coupon)."%", 0, 'L', 0, 1, '', '', true, valign: 'M');

        if(!empty($dealingSheet->ytm)) {
            $this->MultiCell(45, 5, 'YTM', 0, 'L', 0, 0, '', '', true, valign: 'M');
            $this->MultiCell(135, 5, ': ' .$dealingSheet->ytm . '%', 0, 'L', 0, 1, '', '', true, valign: 'M');
        }else{
            $this->MultiCell(45, 5, 'YTM', 0, 'L', 0, 0, '', '', true, valign: 'M');
            $this->MultiCell(135, 5, ': '.number_format($order?->bond?->yield_time_maturity,2) . '%', 0, 'L', 0, 1, '', '', true, valign: 'M');
        }

        $this->MultiCell(45, 5, 'Dirty Price', 0, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(135, 5, ': '.number_format($dealingSheet->price,4), 0, 'L', 0, 1, '', '', true, valign: 'M');

        $this->MultiCell(45, 5, 'Settlement Amount', 0, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(135, 5, ': '.number_format($dealingSheet->payout,2), 0, 'L', 0, 1, '', '', true, valign: 'M');

        $this->Ln(2);
        $this->SetFont($this->font, 'B', 8, '', true);
        $this->MultiCell(60, 4, 'Gross Consideration', 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 4, number_format($dealingSheet->executed,2).' @ '.number_format($dealingSheet->price,4), 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 4, number_format($dealingSheet->price / 100 * $dealingSheet->executed, 2), 1, 'R', 0, 1, '', '', true, valign: 'M');
        $this->SetFont($this->font, '', 7, '', true);

        if ($dealingSheet->commission_step_one > 0) {

            $this->MultiCell(60, 4, 'Brokerage Commission up to TZS 40mn', 1, 'L', 0, 0, '', '', true, valign: 'M');
            $this->MultiCell(60, 4, '0.062% ', 1, 'L', 0, 0, '', '', true, valign: 'M');
            $this->MultiCell(60, 4, number_format($dealingSheet->commission_step_one, 2), 1, 'R', 0, 1, '', '', true, valign: 'M');

            $this->MultiCell(60, 4, 'Brokerage Commission over TZS 40mn', 1, 'L', 0, 0, '', '', true, valign: 'M');
            $this->MultiCell(60, 4, '0.03125% ', 1, 'L', 0, 0, '', '', true, valign: 'M');
            $this->MultiCell(60, 4, number_format($dealingSheet->commission_step_two, 2), 1, 'R', 0, 1, '', '', true, valign: 'M');

            //            $this->MultiCell(60, 4, 'Brokerage Commission up to TZS 70mn', 1, 'L', 0, 0, '', '', true,valign: "M");
            //            $this->MultiCell(60, 4, '0.8% ', 1, 'L', 0, 0, '', '', true,valign: "M");
            //            $this->MultiCell(60, 4, number_format($dealingSheet->commission_step_three,2), 1, 'R', 0, 1, '', '', true,valign: "M");
        } else {
            $com = $this->category->shares_rate;
            $this->MultiCell(60, 4, 'Brokerage Commission ', 1, 'L', 0, 0, '', '', true, valign: 'M');
            $this->MultiCell(60, 4, ($com * 100).'%', 1, 'L', 0, 0, '', '', true, valign: 'M');
            $this->MultiCell(60, 4, number_format($dealingSheet->brokerage, 2), 1, 'R', 0, 1, '', '', true, valign: 'M');
        }

        $this->MultiCell(60, 4, 'VAT', 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 4, '18%', 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 4, number_format($dealingSheet->vat, 2), 1, 'R', 0, 1, '', '', true, valign: 'M');

        $this->MultiCell(60, 4, 'DSE Fee ', 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 4, '0.017% ', 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 4, number_format($dealingSheet->dse, 2), 1, 'R', 0, 1, '', '', true, valign: 'M');

        $this->MultiCell(60, 4, 'CMSA Fee ', 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 4, '0.01%', 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 4, number_format($dealingSheet->cmsa, 2), 1, 'R', 0, 1, '', '', true, valign: 'M');

        //        $this->MultiCell(60, 4, 'Fidelity Fee ', 1, 'L', 0, 0, '', '', true,valign: "M");
        //        $this->MultiCell(60, 4, '0.02% ', 1, 'L', 0, 0, '', '', true,valign: "M");
        //        $this->MultiCell(60, 4, number_format($dealingSheet->fidelity,2), 1, 'R', 0, 1, '', '', true,valign: "M");

        $this->MultiCell(60, 4, 'CDS Fee  (VAT Inclusive)', 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 4, '0.0118%', 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 4, number_format($dealingSheet->cds, 2), 1, 'R', 0, 1, '', '', true, valign: 'M');

        if ($dealingSheet->other_charges > 0) {
            $this->MultiCell(60, 4, 'Other Charges (TZS) ', 1, 'L', 0, 0, '', '', true, valign: 'M');
            $this->MultiCell(60, 4, ' -  ', 1, 'L', 0, 0, '', '', true, valign: 'M');
            $this->MultiCell(60, 4, number_format($dealingSheet->other_charges, 2), 1, 'R', 0, 1, '', '', true, valign: 'M');
        }

        $this->MultiCell(60, 4, 'Total Charges (TZS) ', 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 4, ' -  ', 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 4, number_format($dealingSheet->total_fees + $dealingSheet->other_charges, 2), 1, 'R', 0, 1, '', '', true, valign: 'M');

        $this->SetFont($this->font, 'B', 8, '', true);
        $this->MultiCell(60, 4, 'Net Amount '.$this->payoutText, 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 4, ' -', 1, 'L', 0, 0, '', '', true, valign: 'M');
        $this->MultiCell(60, 4, number_format($dealingSheet->payout, 2), 1, 'R', 0, 1, '', '', true, valign: 'M');
        $this->SetFont($this->font, 'B', 7, '', true);
        $this->Ln(5);
        $this->MultiCell(0, 5, 'All Figures are in TZS', 0, 'R', 0, 1, '', '', true, true, valign: 'L');

        $this->Ln(3);
        $this->SetFont($this->font, 'BU', 7, '', true);
        $this->MultiCell(0, 5, 'Contract Note and Bond Details', 0, 'C', 0, 1, '', '', true, true, valign: 'L');
        $this->SetFont($this->font, '', 7, '', true);
        $this->Ln(3);
        $this->MultiCell(25.72, 4, 'Order No.', 1, 'C', 0, 0, '', '', true, true, valign: 'L');
        $this->MultiCell(25.72, 4, 'Exchange Order No.', 1, 'C', 0, 0, '', '', true, true, valign: 'L');
        $this->MultiCell(25.72, 4, 'CSD Ref. No.', 1, 'C', 0, 0, '', '', true, true, valign: 'L');
        $this->MultiCell(25.72, 4, 'Contract No.', 1, 'C', 0, 0, '', '', true, true, valign: 'L');
        $this->MultiCell(25.72, 4, 'Order Qty.', 1, 'C', 0, 0, '', '', true, true, valign: 'L');
        $this->MultiCell(25.72, 4, 'Traded Qty.', 1, 'C', 0, 0, '', '', true, true, valign: 'L');
        $this->MultiCell(25.72, 4, 'Balance Qty.', 1, 'C', 0, 1, '', '', true, true, valign: 'L');

        $parts = explode(' ', $dealingSheet->slip_no);
        $rowheight = (4 * count($parts)) - 2;
        $this->MultiCell(25.72, $rowheight, $order->uid, 1, 'C', 0, 0, '', '', true, true, valign: 'M');
        $this->MultiCell(25.72, $rowheight, '-', 1, 'C', 0, 0, '', '', true, true, valign: 'M');
        $this->MultiCell(25.72, $rowheight, $dealingSheet->slip_no, 1, 'C', 0, 0, '', '', true, true, valign: 'M');
        $this->MultiCell(25.72, $rowheight, $dealingSheet->uid, 1, 'C', 0, 0, '', '', true, true, valign: 'M');
        $this->MultiCell(25.72, $rowheight, number_format($order->face_value), 1, 'C', 0, 0, '', '', true, true, valign: 'M');
        $this->MultiCell(25.72, $rowheight, number_format($dealingSheet->executed), 1, 'C', 0, 0, '', '', true, true, valign: 'M');
        $this->MultiCell(25.72, $rowheight, number_format($order->balance), 1, 'C', 0, 1, '', '', true, true, valign: 'M');

        $this->Ln(5);
        $this->SetFont($this->font, 'B', 8, '', true);
        $this->MultiCell(0, 5, 'Terms and Conditions', 0, 'C', 0, 1, '', '', true, true, valign: 'L');
        $this->SetFont($this->font, '', 7, '', true);
        $this->Ln(3);
        $this->MultiCell(0, 0, 'We wish to advise that in accordance with your instructions, we have '.$this->typeT.' on your account subject to the Rules and Regulations of the Dar es Salaam Stock Exchange. The contract is also governed by provisions of the Capital Markets and Securities Act, its subsidiary regulations, and the provisions of the Client Agreement.', 0, 'J', 0, 1, '', '', true, true, valign: 'M');
        $this->Ln(3);
        $this->MultiCell(75, 0, 'Our Settlement Instructions: Bank: <b>NBC Limited</b>', 0, 'L', 0, 0, '', '', true, true, ishtml: true, valign: 'M', fitcell: true);
        $this->MultiCell(65, 0, 'Account Name: <b>iTrust Finance Limited</b>', 0, 'L', 0, 0, '', '', true, true, ishtml: true, valign: 'M', fitcell: true);
        $this->MultiCell(40, 0, 'Account No: <b>011103041540</b>', 0, 'R', 0, 1, '', '', true, true, ishtml: true, valign: 'M', fitcell: true);
        $this->Ln(3);
        $this->MultiCell(0, 0, "<i>For Custodial Clients:</i> please instruct your custodian to release the due amount upon T+3. If you do not notify us of any discrepancy herein
within 48 hours, we will deem our details to be correct.<br/><br/>
For any discrepancy/complaints, please contact our dealing team, Email: <a href='mailto:dealing@itrust.co.tz'>dealing@itrust.co.tz</a> Telephone: +255 769 665066", 0, 'J', 0, 1, '', '', true, true, ishtml: true, valign: 'M', fitcell: true);

        $path = Helper::storageArea().'contract_notes';
        File::isDirectory($path) or File::makeDirectory($path, 0777, true, true);
        $this->Output($path.'/contract-note.pdf', 'F');
        $this->file = $path.'/contract-note.pdf';

        return $this->file;
    }
}
