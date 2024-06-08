<?php

namespace App\Exports;

use App\Http\Controllers\Exports\FreightFileRequest;
use App\Models\Courier\CourierRequestItem;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CourierRequestExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    public $from;

    public $end;

    public $rows = 0;

    public function __construct(?string $from = null, ?string $end = null)
    {
        $this->from = $from;
        $this->end = $end;
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
        $data = CourierRequestItem::query()
            ->select('courier_request_items.*', 'courier_requests.*', DB::raw('courier_requests.created_at as date'), DB::raw('courier_request_items.id as item_id'), DB::raw('courier_request_items.created_at as date_2'))
            ->LeftJoin('courier_requests', 'courier_request_items.courier_request_id', '=', 'courier_requests.id')
            ->whereDate('courier_requests.created_at', '>=', $this->from)->whereDate('courier_requests.created_at', '<=', $this->end);
        $this->rows = $data->count() + 1;

        return $data;
    }

    /**
     * @var FreightFileRequest
     */
    public function map($data): array
    {
        return [
            $data->id,
            $data->item_id,
            $data->pickup_date,
            'waybill',
            $data->sender_name,
            'Type Of Origin',
            $data->destination,
            'Type of Destination',
            $data->item,
            $data->description,
            $data->quantity,
            'UOM',
            $data->delivered_date,
            'Partner Name',
            $data->receiver_name,
            $data->receiver_phone,
            'Distance',
            'KG',
            $data->status,
            'Charges',
        ];
    }

    public function headings(): array
    {
        return [
            'ORDER NUMBER',
            'ITEM ID',
            'PICKUP DATE',
            'WAYBILL',
            'FROM',
            'TYPE OF ORIGIN',
            'TO',
            'TYPE OF DESTINATION',
            'ITEM',
            'ITEM DESCRIPTION',
            'QTY OF ITEM',
            'UOM',
            'DELIVERY DATE',
            'PARTNER NAME',
            'RECEIVER',
            'RECEIVER TEL NO',
            'DISTANCE',
            'KG',
            'STATUS',
            'CHARGES',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            // Styling an entire column.
            // 'M'  => ['font' => ['size' => 14]],
        ];

        //        $sheet->mergeCells('A2:A'.$this->rows.'');
        //        $sheet->mergeCells('B2:B'.$this->rows.'');
        //        $sheet->mergeCells('C2:C'.$this->rows.'');
        //        $sheet->mergeCells('D2:D'.$this->rows.'');
        //        $sheet->mergeCells('E2:E'.$this->rows.'');
        //        $sheet->mergeCells('F2:F'.$this->rows.'');
        //        $sheet->mergeCells('G2:G'.$this->rows.'');
        //        $sheet->mergeCells('H2:H'.$this->rows.'');
        //        $sheet->mergeCells('I2:I'.$this->rows.'');
        //        $sheet->mergeCells('J2:J'.$this->rows.'');
        //        $sheet->mergeCells('K2:K'.$this->rows.'');
        //        $sheet->mergeCells('L2:L'.$this->rows.'');
    }
}
