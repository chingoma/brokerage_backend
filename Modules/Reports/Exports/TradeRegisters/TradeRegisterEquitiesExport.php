<?php

namespace Modules\Reports\Exports\TradeRegisters;

use App\Helpers\Clients\Profile;
use App\Models\DealingSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\CRM\Entities\CustomerCustodian;
use Modules\Custodians\Entities\Custodian;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TradeRegisterEquitiesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle
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

    /**
     * @var DealingSheet
     */
    public function map($data): array
    {
        $profile = new Profile($data->client_id);
        $custodianId = CustomerCustodian::where('status', 'active')->where('user_id', $data->client_id)->first();
        $custodian = Custodian::find($custodianId->custodian_id ?? '');

        return [
            str_ireplace('/', '', strrchr($data->reference, '/')),
            $data->reference,
            $data->trade_date,
            $data->settlement_date,
            strtoupper(ucwords($profile->name) ?? ''),
            strtoupper(ucwords($data->type) ?? ''),
            strtoupper(ucwords($data->security->name)),
            $data->executed,
            $data->price,
            $data->executed * $data->price,
            $data->brokerage / ($data->executed * $data->price),
            $data->brokerage,
            $data->vat,
            $data->cmsa,
            $data->dse,
            $data->fidelity,
            $data->cds,
            $data->total_fees,
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
            'TRADE DATE',
            'SETTLEMENT DATE',
            'CLIENT NAME',
            'TRANSACTION',
            'SECURITY',
            'ORDER QUANTITY',
            'AVERAGE PRICE',
            'GROSS CONSIDERATION',
            'COMMISSION PERCENTAGE',
            'BROKERAGE COMMISSION',
            'VAT',
            'CMSA',
            'DSE',
            'FIDELITY',
            'CDS',
            'TOTAL CHARGES',
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
            ->whereDate('trade_date', '>=', $this->from)
            ->whereDate('trade_date', '<=', $this->end);

        $this->rows = $query->count() + 1;

        return $query;
    }

    public function title(): string
    {
        return 'Equities';
    }
}