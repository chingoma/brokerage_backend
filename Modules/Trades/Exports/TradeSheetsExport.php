<?php

namespace Modules\Trades\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Modules\Orders\Entities\Order;
use Modules\Trades\Entities\Trade;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TradeSheetsExport implements FromQuery, WithHeadings, WithMapping, WithStyles
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
    public function map($data): array
    {
        $ref = $data->uid;
        if (empty($data->uid)) {
            if (strtolower($data->status) == 'cancelled') {
                $ref = 'CANCELLED';
            } else {
                $ref = 'MGR';
            }
        }

        return [

            $ref,
            strtoupper(ucwords($data->client->name ?? '') ?? ''),
            $data->client->dse_account ?? '',
            $data->trade_date,
            strtoupper(ucwords($data->security->name ?? '')),
            $data->type,
            $data->price,
            max($data->executed, 0),
            $data->total_fees,
            $data->brokerage,
            $data->payout,
            strtoupper($data->status),
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
            'Shares',
            'Fees',
            'Brokerage',
            'Payout',
            'Status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];

    }

    public function query()
    {
        $query = Trade::query()->orderBy('trade_date', 'desc')
            ->whereDate('trade_date', '>=', $this->from)
            ->whereDate('trade_date', '<=', $this->end);

        if (! empty($this->status)) {
            $query = $query->where('status', $this->status);
        }

        $this->rows = $query->count() + 1;

        return $query;
    }
}
