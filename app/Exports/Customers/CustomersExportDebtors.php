<?php

namespace App\Exports\Customers;

use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Modules\Orders\Entities\Order;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersExportDebtors implements FromQuery, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    public $from;

    public $end;

    public $rows = 0;

    public $status = '';

    public function __construct(?string $from = null, ?string $end = null)
    {
        $this->from = $from;
        $this->end = $end;
    }

    public function status($status = '')
    {
        $this->status = $status;

        return $this;
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

    public function query()
    {
        $users = User::latest()->customers()->get();
        $ids = [];
        if (! empty($users)) {
            foreach ($users as $key => $user) {
                if ($user->wallet_balance < 0) {
                    $ids[$key] = $user->id;
                }
            }
        }

        $data = User::query()->customers()->whereIn('id', $ids)->latest();

        $this->rows = $data->count() + 1;

        return $data;
    }

    /**
     * @var Order
     */
    public function map($data): array
    {
        return [

            $data->uid,
            ucwords($data->name) ?? '',
            $data->dse_account ?? '',
            $data->created_at,
            $data->email,
            $data->mobile,
            $data->wallet_balance,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'Name',
            'CDS Account',
            'Date',
            'Email',
            'Phone',
            'Balance',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];

    }
}
