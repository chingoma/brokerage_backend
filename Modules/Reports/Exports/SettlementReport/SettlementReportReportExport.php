<?php

namespace Modules\Reports\Exports\SettlementReport;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SettlementReportReportExport implements WithMultipleSheets
{
    use Exportable;

    public $from;

    public $end;

    public function __construct(?string $from = null, ?string $end = null)
    {
        $this->from = $from;
        $this->end = $end;
    }

    public function from(string $from)
    {
        $this->from = $from;

        return $this;
    }

    public function end(string $end)
    {
        $this->end = $end;

        return $this;
    }

    public function sheets(): array
    {

        $sheets = [];

        $sheets[0] = new SettlementReportEquitiesExport($this->from, $this->end);
        $sheets[1] = new SettlementReportBondsExport($this->from, $this->end);
        $sheets[2] = new SettlementReportBillsExport($this->from, $this->end);

        return $sheets;
    }
}
