<?php

namespace Modules\Reports\Exports\Portfolio;

use App\Helpers\Clients\Profile;
use App\Helpers\Helper;
use App\Models\DealingSheet;
use App\Models\Security;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MasterPortfolioReportExport implements FromArray, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use Exportable;

    public $rows = 0;

    protected $companyTitles = [];

    protected $companies = [];

    protected $companyIds = [];

    public function __construct()
    {
        $this->companyIds = DB::table('dealing_sheets')->groupBy('security_id')->pluck('security_id');
        $this->companies = Security::select(['name'])->whereIn('id', $this->companyIds)->get()->pluck('name')->toArray();
        if (! empty($this->companies)) {
            foreach ($this->companies as $key => $company) {
                $this->companyTitles[$key] = strtoupper($company);
            }
        }
    }

    /**
     * @param $row
     * @return array
     */
    public function map($row): array
    {

        $result = [];

        if (! empty($this->companyIds)) {
            foreach ($this->companyIds as $j => $id) {
                $result[$j] = Helper::customerCompanyShares($id, $row['client_id']);
            }
        }
        array_unshift($result, $row['custodian']);
        array_unshift($result, $row['client']);

        return $result;
    }

    public function headings(): array
    {
        array_unshift($this->companyTitles, 'CUSTODIAN');
        array_unshift($this->companyTitles, 'CLIENT');

        return $this->companyTitles;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];

    }

    public function array(): array
    {
        $data = [];
        $clients = User::customers()->get();
        if (! empty($clients)) {
            foreach ($clients as $i => $client) {
                $profile = new Profile($client->id);
                $data[$i]['client_id'] = $profile->id;
                $data[$i]['client'] = $profile->name;
                $data[$i]['custodian'] = (strtolower($profile->has_custodian == 'yes')) ? 'YES' : 'NO';
            }
        }

        $this->rows = count($data) + 1;

        return $data;
    }

    public function title(): string
    {
        return 'Equities';
    }
}
