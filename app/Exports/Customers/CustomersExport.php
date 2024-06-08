<?php

namespace App\Exports\Customers;

use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Modules\Orders\Entities\Order;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersExport implements FromQuery, WithHeadings, WithMapping, WithStyles
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
        if (! empty($this->status)) {
            $data = User::query()
                ->customers()
                ->whereDate('created_at', '>=', $this->from)
                ->whereDate('created_at', '<=', $this->end)
                ->where('status', $this->status);
        } else {
            $data = User::query()
                ->customers()
                ->whereDate('created_at', '>=', $this->from)
                ->whereDate('created_at', '<=', $this->end);
        }

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
            $data->name ?? '',
            $data->dse_account ?? '',
            $data->created_at,
            $data->email,
            $data->mobile,
            $data->status,
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
            'Status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];

    }
}
