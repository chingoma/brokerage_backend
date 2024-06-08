<?php

namespace App\Exports\Orders;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\Orders\Entities\Order;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdersExportAll implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle
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
        $data = Order::query()
            ->where('status', '!=', 'pending')
            ->where('status', '!=', 'cancelled');

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
            ucwords($data->client->name) ?? '',
            $data->client->dse_account ?? '',
            $data->trade_date,
            $data->security->name,
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

    public function columnFormats(): array
    {
        return [
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'J' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'K' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'L' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'M' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function title(): string
    {
        return 'All Order';
    }
}
