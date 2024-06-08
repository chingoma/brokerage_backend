<?php

namespace App\Exports;

use App\Http\Controllers\Exports\FreightFileRequest;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Modules\Orders\Entities\Order;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FreightExport implements FromQuery, WithHeadings, WithMapping, WithStyles
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

    public function get40F($data)
    {
        $i = 0;
        if (is_array($data)) {
            foreach ($data as $datum) {
                if ($datum->containerType == '40FT') {
                    $i++;
                }
            }
        }

        return $i;
    }

    public function get40FN($data)
    {
        $i = '';
        if (is_array($data)) {
            foreach ($data as $datum) {
                if ($datum->containerType == '40FT') {
                    $i .= $datum->containerNumber.',';
                }
            }
        }

        return $i;
    }

    public function get20F($data)
    {
        $i = 0;
        if (is_array($data)) {
            foreach ($data as $datum) {
                if ($datum->containerType == '20FT') {
                    $i++;
                }
            }
        }

        return $i;
    }

    public function get20Fn($data)
    {
        $i = '';
        if (is_array($data)) {
            foreach ($data as $datum) {
                if ($datum->containerType == '20FT') {
                    $i .= $datum->containerNumber.' ';
                }
            }
        }

        return $i;
    }

    public function query()
    {
        $data = Order::query()
            ->whereDate('created_at', '>=', $this->from)->whereDate('created_at', '<=', $this->end);

        return $data;
    }

    /**
     * @var FreightFileRequest
     */
    public function map($data): array
    {
        return [

            $data->id,
            $data->created_at,
            $data->client->name,
            $data->bol_awb,
            $data->cargo_details,
            $data->vessel,
            $data->estimated_time_arrival,
            $data->actual_time_arrival,
            $data->discharge_date,
            $data->retired,
            $data->category->name,
            $this->get20FN($data->containers),
            $this->get20F($data->containers),
            $this->get40FN($data->containers),
            $this->get40F($data->containers),
            $data->max,
            $data->status,
        ];
    }

    public function headings(): array
    {
        return [
            'File No',
            'Date',
            'Client',
            'BOL/AWB',
            'Cargo Details',
            'Vessel',
            'ETA',
            'ATA',
            'Discharge Date',
            'Invoice Number',
            'Goods Category',
            'Container Number (20FT)',
            '20F Container',
            'Container Number (40FT)',
            '40F Container',
            'Max',
            'File Status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
