<?php

namespace Modules\Reports\Exports\TradeRegisters;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TradeRegisterReportExport implements WithMultipleSheets
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

        $sheets[0] = new TradeRegisterEquitiesExport($this->from, $this->end);
        $sheets[1] = new TradeRegisterBondsExport($this->from, $this->end);
        $sheets[2] = new TradeRegisterBillsExport($this->from, $this->end);

        return $sheets;
    }
}
