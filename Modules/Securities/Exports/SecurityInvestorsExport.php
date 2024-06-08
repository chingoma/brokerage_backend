<?php

namespace Modules\Securities\Exports;

use App\Helpers\Clients\Profile;
use App\Helpers\Helper;
use App\Models\DealingSheet;
use App\Models\Security;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SecurityInvestorsExport implements FromArray, WithHeadings, WithMapping,WithStyles, WithTitle
{
    use Exportable;

    public int $rows = 0;

    protected string $id = "";


    public function __construct($id)
    {
        $this->id = $id;
    }


    public function headings(): array
    {
        return ["ID","NAME","MOBILE","EMAIL","CDS","TYPE","VOLUME"];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];

    }

    public function array(): array
    {
        $query = DB::table('dealing_sheets')
            ->select([
                'securities.id',
                'users.name',
                'users.dse_account',
                'users.type',
                'users.mobile',
                'users.email',
                'dealing_sheets.client_id',
            ])
            ->groupBy(['dealing_sheets.security_id','dealing_sheets.client_id'])
            ->selectRaw("sum(IF(dealing_sheets.type='buy',dealing_sheets.executed,0)) - sum(IF(dealing_sheets.type='sell',dealing_sheets.executed,0)) as volume")
            ->selectRaw("users.flex_acc_no as uid")
            ->where("dealing_sheets.security_id",$this->id)
            ->where("dealing_sheets.status","approved")
            ->where("users.type","!=","admin")
            ->whereNull('securities.deleted_at')
            ->whereNull('dealing_sheets.deleted_at')
            ->leftJoin('securities', 'dealing_sheets.security_id', '=', 'securities.id')
            ->leftJoin('users', 'dealing_sheets.client_id', '=', 'users.id');

        $investors = $query->get()->toArray();

        $this->rows = count($investors) + 1;

        return $investors;
    }

    public function map($row): array
    {
        $data = (object) $row;
        return [
            $data->uid,
            strtoupper($data->name ?? '') ?? '',
            strtoupper($data->mobile ?? '') ?? '',
            strtoupper($data->email ?? '') ?? '',
            strtoupper($data->dse_account ?? '') ?? '',
            strtoupper($data->type ?? '') ?? '',
            $data->volume
        ];
    }

    public function title(): string
    {
        return 'Investors';
    }
}
