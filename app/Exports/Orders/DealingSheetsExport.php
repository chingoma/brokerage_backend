<?php

namespace App\Exports\Orders;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Modules\Orders\Entities\Order;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DealingSheetsExport implements FromArray, WithHeadings, WithMapping, WithStyles
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

    /**
     * @var Order
     */
    public function map($datax): array
    {
        $data = (object) $datax;

        return [

            $data->uid,
            ucwords($data->client->name ?? '') ?? '',
            $data->client->dse_account ?? '',
            $data->date,
            ucwords($data->security->name ?? ''),
            $data->type,
            $data->price,
            $data->volume,
            max($data->executed, 0),
            $data->balance,
            $data->brokerage,
            $data->amount,
        ];
    }

    public function headings(): array
    {
        return [
            '#',
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

    public function array(): array
    {
        $list = Order::orderBy('date', 'desc')
            ->where('status', '!=', 'pending')
            ->where('status', '!=', 'complete')
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'closed')
            ->whereDate('date', '>=', $this->from)
            ->whereDate('date', '<=', $this->end)
            ->get()->toArray();

        $data = [];
        if (! empty($list)) {
            foreach ($list as $key => $item) {
                if ($item['balance'] > 0) {
                    $data[$key] = $item;
                }
            }
        }
        $this->rows = count($data) + 1;

        return $data;
    }
}
