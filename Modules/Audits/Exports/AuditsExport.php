<?php

namespace Modules\Audits\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Modules\Audits\Entities\Audits;
use Modules\Orders\Entities\Order;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AuditsExport implements FromArray, WithHeadings, WithMapping, WithStyles
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
        $description = '';

        $sheet = \DB::table('dealing_sheets')->find($data->auditable_id);
        if (! empty($sheet)) {
            $customer = \DB::table('users')->find($sheet->client_id);
            $description = $customer->name.', contract note '.$sheet->uid;
        }

        return [
            ucwords($data->user->name ?? '') ?? '',
            $data->event ?? '',
            $data->tags,
            $data->ip_address,
            $data->created_at,
            $data->user_agent,
            $data->old_values,
            $data->new_values,
            $data->url,
            $data->auditable_type,
            $data->auditable_id,
            $data->user_id,
            $description,
        ];
    }

    public function headings(): array
    {
        return [
            'USER',
            'EVENT',
            'TAG',
            'IP ADDRESS',
            'TIME STAMP',
            'DEVICE',
            'OLD VALUE',
            'NEW VALUE',
            'URL',
            'TYPE',
            'ID',
            'USER ID',
            'DESCRIPTION',
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
        $list = Audits::orderBy('created_at', 'desc')
            ->whereDate('created_at', '>=', $this->from)
            ->whereDate('created_at', '<=', $this->end)
            ->get()->toArray();

        $this->rows = count($list) + 1;

        return $list;
    }
}
