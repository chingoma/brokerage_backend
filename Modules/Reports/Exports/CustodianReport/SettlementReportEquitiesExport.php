<?php

namespace Modules\Reports\Exports\CustodianReport;

use App\Helpers\Clients\Profile;
use App\Models\DealingSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\Custodians\Entities\Custodian;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SettlementReportEquitiesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use Exportable;

    public $custodian_id;

    public $from;

    public $end;

    public function __construct(string $custodian_id, ?string $from = null, ?string $end = null)
    {
        $this->custodian_id = $custodian_id;
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

    /**
     * @var DealingSheet
     */
    public function map($data): array
    {
        $profile = new Profile($data->client_id);
        $custodian = Custodian::find($data->custodian_id);

        return [
            str_ireplace('/', '', strrchr($data->reference, '/')),
            $data->reference,
            $data->settlement_date,
            strtoupper(ucwords($profile->name) ?? ''),
            strtoupper(ucwords($data->type) ?? ''),
            strtoupper(ucwords($data->security->name)),
            $data->payout,
            strtoupper(ucwords($custodian->name ?? '') ?? ''),
            strtoupper($data->status),
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'REFERENCE',
            'SETTLEMENT DATE',
            'CLIENT NAME',
            'TRANSACTION',
            'SECURITY',
            'NET PAYABLE / RECEIVABLE',
            'CUSTODIAN',
            'STATUS',
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
        $query = DealingSheet::query()->orderBy('trade_date', 'desc')
            ->where('uid', '!=', '')
            ->where('custodian_id', $this->custodian_id)
            ->whereDate('settlement_date', '>=', $this->from)
            ->whereDate('settlement_date', '<=', $this->end);

        $this->rows = $query->count() + 1;

        return $query;
    }

    public function title(): string
    {
        return 'Equities';
    }
}