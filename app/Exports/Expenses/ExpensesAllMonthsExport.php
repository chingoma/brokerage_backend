<?php

namespace App\Exports\Expenses;

use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ExpensesAllMonthsExport implements WithMultipleSheets
{
    use Exportable;

    public function sheets(): array
    {

        $sheets = [];

        $start = new Carbon('first day of January '.date('Y'));
        $end = new Carbon('last day of January '.date('Y'));
        $sheets[0] = new ExpensesMonthlyExport('January', $start->toDateString(), $end->toDateString());

        $start = new Carbon('first day of February '.date('Y'));
        $end = new Carbon('last day of February '.date('Y'));
        $sheets[1] = new ExpensesMonthlyExport('February', $start->toDateString(), $end->toDateString());

        $start = new Carbon('first day of March '.date('Y'));
        $end = new Carbon('last day of March '.date('Y'));
        $sheets[2] = new ExpensesMonthlyExport('march', $start->toDateString(), $end->toDateString());

        $start = new Carbon('first day of April '.date('Y'));
        $end = new Carbon('last day of April '.date('Y'));
        $sheets[3] = new ExpensesMonthlyExport('April', $start->toDateString(), $end->toDateString());

        $start = new Carbon('first day of May '.date('Y'));
        $end = new Carbon('last day of May '.date('Y'));
        $sheets[4] = new ExpensesMonthlyExport('may', $start->toDateString(), $end->toDateString());

        $start = new Carbon('first day of June '.date('Y'));
        $end = new Carbon('last day of June '.date('Y'));
        $sheets[5] = new ExpensesMonthlyExport('June', $start->toDateString(), $end->toDateString());

        $start = new Carbon('first day of July '.date('Y'));
        $end = new Carbon('last day of July '.date('Y'));
        $sheets[6] = new ExpensesMonthlyExport('July', $start->toDateString(), $end->toDateString());

        $start = new Carbon('first day of August '.date('Y'));
        $end = new Carbon('last day of August '.date('Y'));
        $sheets[7] = new ExpensesMonthlyExport('August', $start->toDateString(), $end->toDateString());

        $start = new Carbon('first day of September '.date('Y'));
        $end = new Carbon('last day of September '.date('Y'));
        $sheets[8] = new ExpensesMonthlyExport('September', $start->toDateString(), $end->toDateString());

        $start = new Carbon('first day of October '.date('Y'));
        $end = new Carbon('last day of October '.date('Y'));
        $sheets[9] = new ExpensesMonthlyExport('October', $start->toDateString(), $end->toDateString());

        $start = new Carbon('first day of November '.date('Y'));
        $end = new Carbon('last day of November '.date('Y'));
        $sheets[10] = new ExpensesMonthlyExport('November', $start->toDateString(), $end->toDateString());

        $start = new Carbon('first day of December '.date('Y'));
        $end = new Carbon('last day of December '.date('Y'));
        $sheets[11] = new ExpensesMonthlyExport('December', $start->toDateString(), $end->toDateString());

        return $sheets;
    }
}
