<?php
/*
 * Copyright (c) 2022. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace App\Helpers\Pdfs;

use App\Models\DealingSheet;
use App\Models\MarketReports\Weekly\WeeklyMarketReport;
use App\Models\MarketReports\Weekly\WeeklyMarketReportCooperateBond;
use App\Models\MarketReports\Weekly\WeeklyMarketReportEquityOverview;
use App\Models\MarketReports\Weekly\WeeklyMarketReportEquitySummary;
use App\Models\MarketReports\Weekly\WeeklyMarketReportGovernmentBond;
use Modules\Orders\Entities\Order;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use TCPDF;

class MarketReportsPdf extends TCPDF
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

    public function create(WeeklyMarketReport $report): string
    {
        $this->original_lMargin = 6;
        $this->original_rMargin = 6;

        $dealingSheet = DealingSheet::find(1);
        $order = Order::find($dealingSheet->order_id);
        $overview = WeeklyMarketReportEquityOverview::where('report_id', $report->id)->get();
        $summary = WeeklyMarketReportEquitySummary::where('report_id', $report->id)->get();
        $gainers = WeeklyMarketReportEquitySummary::orderBy('change', 'DESC')->where('report_id', $report->id)->limit(5)->get();
        $losers = WeeklyMarketReportEquitySummary::orderBy('change', 'ASC')->where('report_id', $report->id)->limit(5)->get();
        $movers = WeeklyMarketReportEquitySummary::where('report_id', $report->id)->limit(5)->get();
        $totalTurnover = WeeklyMarketReportEquitySummary::where('report_id', $report->id)->sum('turnover');
        $governmentBonds = WeeklyMarketReportGovernmentBond::where('report_id', $report->id)->get();
        $cooperateBonds = WeeklyMarketReportCooperateBond::where('report_id', $report->id)->get();

        $spreadsheet = IOFactory::load(base_path('templates/WeeklyReportTemplate.xlsx'));
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->getCell('B4')->setValue($report->description);

        if (! empty($cooperateBonds)) {
            foreach ($cooperateBonds as $y => $value) {
                $i = 14 + $y;
                $worksheet->getCell('U'.$i)->setValue($value['bond_no'].' '.$value['duration'].' Years');
                $worksheet->getCell('V'.$i)->setValue($value['week_close']);
                $worksheet->getCell('W'.$i)->setValue($value['coupon']);
                $worksheet->getCell('X'.$i)->setValue($value['yield']);
                $worksheet->getStyle('X'.$i)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            }
        }

        if (! empty($governmentBonds)) {
            foreach ($governmentBonds as $y => $value) {
                $i = 5 + $y;
                $worksheet->getCell('U'.$i)->setValue($value['duration'].' Years');
                $worksheet->getCell('V'.$i)->setValue($value['week_close']);
                $worksheet->getCell('W'.$i)->setValue($value['coupon']);
                $worksheet->getCell('X'.$i)->setValue($value['yield']);
                $worksheet->getStyle('X'.$i)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            }
        }

        if (! empty($movers)) {
            foreach ($movers as $y => $value) {
                $i = 22 + $y;
                $worksheet->getCell('Q'.$i)->setValue($value['company']);
                $worksheet->getCell('R'.$i)->setValue($value['turnover']);
                $percent = $value['turnover'] / $totalTurnover * 100;
                $worksheet->getCell('S'.$i)->setValue($percent);
            }
        }

        if (! empty($gainers)) {
            foreach ($gainers as $y => $value) {
                $i = 4 + $y;
                $worksheet->getCell('Q'.$i)->setValue($value['company']);
                $worksheet->getCell('R'.$i)->setValue($value['change']);
                $worksheet->getCell('S'.$i)->setValue($value['week_close']);
            }
        }

        if (! empty($losers)) {
            foreach ($losers as $y => $value) {
                $i = 13 + $y;
                $worksheet->getCell('Q'.$i)->setValue($value['company']);
                $worksheet->getCell('R'.$i)->setValue($value['change']);
                $worksheet->getCell('S'.$i)->setValue($value['week_close']);
            }
        }

        if (! empty($summary)) {
            foreach ($summary as $y => $value) {
                $i = 5 + $y;
                $worksheet->getCell('L'.$i)->setValue($value['company']);
                $worksheet->getCell('M'.$i)->setValue($value['week_close']);
                $worksheet->getCell('N'.$i)->setValue($value['change']);
                $worksheet->getCell('O'.$i)->setValue($value['turnover']);
            }
        }

        // create new PDF document
        // set document information
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor('Kelvin chingoma');
        $this->SetTitle('Contract Note');
        $this->SetSubject('Contract Note');
        $this->SetKeywords(env('KEYWORDS'));

        // set default monospaced font
        $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        $this->setFillColor(8, 52, 84);

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
        $this->AddPage(orientation: 'L', format: 'A3');

        // get the current page break margin
        $bMargin = $this->getBreakMargin();
        // get current auto-page-break mode
        $auto_page_break = $this->getAutoPageBreak();
        // disable auto-page-break
        $this->SetAutoPageBreak(false, 0);
        // set background image
        $this->Image(file: public_path('business/weekly-report-background-0.png'), x: 0, y: 0, w: 420, h: 297, resize: false, ismask: false, imgmask: false);
        // restore auto-page-break status
        $this->SetAutoPageBreak($auto_page_break, $bMargin);
        // set the starting point for the page content
        $this->setPageMark();

        $this->resetColumns();
        $columns = [
            ['w' => 220, 'y' => 0, 's' => 3],
            ['w' => 100, 'y' => 5, 's' => 3],
            ['w' => 80, 'y' => 5, 's' => 3],
        ];
        $this->setColumnsArray($columns);

        $this->ln(10);
        $this->selectColumn(0);
        $this->MultiCell(w: 0, h: '', txt: $report->description, align: 'L', ishtml: true, valign: 'B');

        $this->ln(10);
        $this->MultiCell(w: 50, h: '', txt: '<b>Equity Overview</b>', align: 'L', ln: 0, ishtml: true, valign: 'B');
        $this->MultiCell(w: 22, h: '', txt: '<b>Previous</b>', align: 'L', ln: 0, ishtml: true, valign: 'B');
        $this->MultiCell(w: 22, h: '', txt: '<b>Current</b>', align: 'L', ln: 0, ishtml: true, valign: 'B');
        $this->MultiCell(w: 22, h: '', txt: '<b>Change %</b>', align: 'L', ln: 0, ishtml: true, valign: 'B');
        $this->MultiCell(w: 50, h: '', txt: '<b>Equity Overview</b>', align: 'L', ln: 1, ishtml: true, valign: 'B');

        if (! empty($overview)) {
            foreach ($overview as $y => $value) {
                $this->MultiCell(w: 60, h: 8, txt: $value['equity_overview'], align: 'L', ln: 0, ishtml: true, valign: 'B');
                $this->MultiCell(w: 22, h: 8, txt: $value['previous'], align: 'L', ln: 0, ishtml: true, valign: 'B');
                $this->MultiCell(w: 22, h: 8, txt: $value['current'], align: 'L', ln: 0, ishtml: true, valign: 'B');
                $this->MultiCell(w: 22, h: 8, txt: $value['change'], align: 'L', ishtml: true, valign: 'B');
                $this->ln(1);
            }
        }

        $this->selectColumn(1);
        $this->MultiCell(w: '', h: '', txt: $report->description, align: 'L', ishtml: true, valign: 'B');

        $this->selectColumn(2);
        $this->MultiCell(w: '', h: '', txt: $report->description, align: 'L', ishtml: true, valign: 'B');

        $this->selectColumn(3);
        $this->MultiCell(w: '', h: '', txt: $report->description, align: 'C', ishtml: true, valign: 'B');

        $this->AddPage(orientation: 'L', format: 'A3');

        // get the current page break margin
        $bMargin = $this->getBreakMargin();
        // get current auto-page-break mode
        $auto_page_break = $this->getAutoPageBreak();
        // disable auto-page-break
        $this->SetAutoPageBreak(false, 0);
        // set background image
        $this->Image(file: public_path('business/weekly-report-background-1.png'), x: 0, y: 0, w: 420, h: 297, resize: false, ismask: false, imgmask: false);
        // restore auto-page-break status
        $this->SetAutoPageBreak($auto_page_break, $bMargin);
        // set the starting point for the page content
        $this->setPageMark();

        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        $this->Output(public_path('files/market-report.pdf'), 'F');
        $this->file = asset('files/market-report.pdf');

        return public_path('files/market-report.pdf');
    }
}
