<?php

namespace App\Exports\VAT;

use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class VATAllMonthsExport implements WithMultipleSheets
{
    use Exportable;

    public function sheets(): array
    {

        $sheets = [];

        $start = new Carbon('first day of January '.date('Y'));
        $end = new Carbon('last day of January '.date('Y'));
        $sheets[0] = new VATMonthlyExport('January', $start->toDateString(), $end->toDateString());

        $start = new Carbon('first day of February '.date('Y'));
        $end = new Carbon('last day of February '.date('Y'));
        $sheets[1] = new VATMonthlyExport('February', $start->toDateString(), $end->toDateString());

        $start = new Carbon('first day of March '.date('Y'));
        $end = new Carbon('last day of March '.date('Y'));
        $sheets[2] = new VATMonthlyExport('march', $start->toDateString(), $end->toDateString());

        $start = new Carbon('first day of April '.date('Y'));
        $end = new Carbon('last day of April '.date('Y'));
        $sheets[3] = new VATMonthlyExport('April', $start->toDateString(), $end->toDateString());

        $start = new Carbon('first day of May '.date('Y'));
        $end = new Carbon('last day of May '.date('Y'));
        $sheets[4] = new VATMonthlyExport('may', $start->toDateString(), $end->toDateString());

        $start = new Carbon('first day of June '.date('Y'));
        $end = new Carbon('last day of June '.date('Y'));
        $sheets[5] = new VATMonthlyExport('June', $start->toDateString(), $end->toDateString());

        $start = new Carbon('first day of July '.date('Y'));
        $end = new Carbon('last day of July '.date('Y'));
        $sheets[6] = new VATMonthlyExport('July', $start->toDateString(), $end->toDateString());

        $start = new Carbon('first day of August '.date('Y'));
        $end = new Carbon('last day of August '.date('Y'));
        $sheets[7] = new VATMonthlyExport('August', $start->toDateString(), $end->toDateString());

        $start = new Carbon('first day of September '.date('Y'));
        $end = new Carbon('last day of September '.date('Y'));
        $sheets[8] = new VATMonthlyExport('September', $start->toDateString(), $end->toDateString());

        $start = new Carbon('first day of October '.date('Y'));
        $end = new Carbon('last day of October '.date('Y'));
        $sheets[9] = new VATMonthlyExport('October', $start->toDateString(), $end->toDateString());

        $start = new Carbon('first day of November '.date('Y'));
        $end = new Carbon('last day of November '.date('Y'));
        $sheets[10] = new VATMonthlyExport('November', $start->toDateString(), $end->toDateString());

        $start = new Carbon('first day of December '.date('Y'));
        $end = new Carbon('last day of December '.date('Y'));
        $sheets[11] = new VATMonthlyExport('December', $start->toDateString(), $end->toDateString());

        return $sheets;
    }
}
