<?php

namespace App\Exports\Income;

use App\Models\Accounting\Transaction;
use App\Models\DealingSheet;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\Orders\Entities\Order;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IncomeCustomExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use Exportable;

    public string $from;

    public string $end;

    public int $rows = 0;

    public function __construct(string $from, string $end)
    {
        $this->from = $from;
        $this->end = $end;
    }

    public function from(string $from): static
    {
        $this->from = $from;

        return $this;
    }

    public function end(string $end): static
    {
        $this->end = $end;

        return $this;
    }

    public function query(): Builder
    {
        $data = Transaction::query()
            ->orderBy('transaction_date', 'asc')->whereDate('transaction_date', '<=', $this->end)->whereDate('transaction_date', '>=', $this->from)->where('status', 'approved')->whereIn('class_id', [10]);

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
            $data->id,
            ucwords($data->client->name) ?? '',
            $data->transaction_date,
            $data->reference,
            $data->title,
            $quantity,
            $price,
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
            'Quantity',
            'Price',
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
        return $this->from.' to '.$this->end;
    }
}
