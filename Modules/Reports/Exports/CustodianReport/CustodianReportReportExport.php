<?php

namespace Modules\Reports\Exports\CustodianReport;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CustodianReportReportExport implements WithMultipleSheets
{
    use Exportable;

    public $custodian_id;

    public $from;

    public $end;

    public function __construct(string $custodian_id, ?string $from = null, ?string $end = null)
    {
        $this->custodian_id = $custodian_id;
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

        $sheets[0] = new SettlementReportEquitiesExport($this->custodian_id, $this->from, $this->end);
        $sheets[1] = new SettlementReportBondsExport($this->custodian_id, $this->from, $this->end);
        $sheets[2] = new SettlementReportBillsExport($this->custodian_id, $this->from, $this->end);

        return $sheets;
    }
}
