<?php

namespace App\Exports\Orders;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\Orders\Entities\Order;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdersExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle
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
            $data = Order::query()
                ->where('status', '!=', 'pending')
                ->where('status', '!=', 'complete')
                ->where('status', '!=', 'cancelled')
                ->whereDate('created_at', '>=', $this->from)
                ->whereDate('created_at', '<=', $this->end);
        } else {
            $data = Order::query()
                ->where('status', '!=', 'pending')
                ->where('status', '!=', 'complete')
                ->where('status', '!=', 'cancelled')
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
            $data->date,
            $data->client->name ?? '',
            $data->client->dse_account ?? '',
            $data->trade_date,
            $data->security->name ?? '',
            $data->type,
            $data->price,
            $data->volume,
            $data->executed,
            $data->balance,
            $data->brokerage,
            $data->amount,
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'Date',
            'Client',
            'CDS',
            'Date',
            'Security',
            'Type',
            'Price',
            'Volume',
            'Executed',
            'Balance',
            'Brokerage',
            'Amount',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];

    }

    public function title(): string
    {
        return 'Orders';
    }
}
