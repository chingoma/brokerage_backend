<?php

namespace App\Exports;

use App\Models\Payroll\PayrollListItems;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Modules\Orders\Entities\Order;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayrollPaylistExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    public $id;

    public function id(string $id)
    {
        $this->id = $id;

        return $this;
    }

    public function query()
    {

        $data = PayrollListItems::query()

            ->where('payroll_list_id', $this->id);

        $this->rows = $data->count() + 1;

        return $data;
    }

    /**
     * @var Order
     */
    public function map($data): array
    {
        return [

            $data->employee->name ?? '',
            $data->basic ?? '',
            $data->gross,
            $data->pensions,
            $data->taxable,
            $data->paye,
            $data->insurances,
            $data->deductions,
            $data->net_salary,
        ];
    }

    public function headings(): array
    {
        return [
            'BASIC',
            'ALLOWANCE',
            'GROSS',
            'PENSION',
            'TAXABLE',
            'PAYE',
            'INSURANCE',
            'DEDUCTIONS',
            'NET SALARY',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];

    }
}
