<?php

namespace App\Exports\Receipts;

use App\Models\Accounting\Transaction;
use App\Models\DealingSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\Orders\Entities\Order;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReceiptsMonthlyExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use Exportable;

    public $from;

    public $end;

    public $rows = 0;

    public $status = '';

    public $month = '';

    public function __construct($month, ?string $from = null, ?string $end = null)
    {
        $this->from = $from;
        $this->end = $end;
        $this->month = $month;
    }

    public function month($month = '')
    {
        $this->month = $month;

        return $this;
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
        $data = Transaction::query()
            ->orderBy('transaction_date', 'asc')
            ->receiptsReport()->groupBy('reference')->whereDate('transaction_date', '<=', $this->end)->whereDate('transaction_date', '>=', $this->from)->where('status', 'approved');

        $this->rows = $data->count() + 1;

        return $data;
    }

    /**
     * @var Order
     */
    public function map($data): array
    {
        $quantity = 0;
        $price = 0;

        if (strtolower($data->category) == 'receipt') {
            $this->type = 'Credit';
            $price = $data->amount;
            $quantity = 1;
        }

        if (strtolower($data->category) == 'payment') {
            $this->type = 'Debit';
            $price = $data->amount;
            $quantity = 1;
        }

        if (strtolower($data->category) == 'invoice') {
            $this->type = 'Debit';
            $price = $data->amount;
            $quantity = 1;
        }

        if (strtolower($data->category) == 'order') {

            if (strtolower($data->action) == 'debit') {
                $sheet = DealingSheet::where('slip_no', $data->reference)->first();
                $this->type = 'Debit';
                $price = $sheet->price;
                $quantity = $sheet->executed;
            } else {
                $sheet = DealingSheet::where('slip_no', $data->reference)->first();
                $price = $sheet->price;
                $quantity = $sheet->executed;
                $this->type = 'Credit';
            }

        }

        return [
            $data->uid,
            ucwords($data->client->name) ?? '',
            $data->transaction_date,
            (string) $data->reference,
            $data->title,
            $data->amount,
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'Client',
            'Date',
            'Reference',
            'Particular',
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
        return $this->month;
    }
}