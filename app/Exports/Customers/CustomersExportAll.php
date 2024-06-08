<?php

namespace App\Exports\Customers;

use App\Helpers\Clients\Profile;
use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Modules\CRM\Entities\CustomerCategory;
use Modules\CRM\Entities\CustomerCustodian;
use Modules\Custodians\Entities\Custodian;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersExportAll implements FromQuery, WithHeadings, WithMapping, WithStyles
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
        $data = User::query()->customers();

        $this->rows = $data->count() + 1;

        return $data;
    }

    public function map($row): array
    {

        $data = new Profile($row->id);
        $category = CustomerCategory::find($data->category_id);
        $custodianId = CustomerCustodian::where('status', 'active')->where('user_id', $row->id)->first();
        $custodian = Custodian::find($custodianId->custodian_id ?? '');
        $country = \DB::table('countries')->find($data->country_id);
        $gender = '';
        switch (strtolower($data->gender)) {
            case 'm':
                $gender = 'Male';
                break;
            case 'f':
                $gender = 'FEMALE';
            case 'female':
                $gender = 'FEMALE';
                break;
            case 'male':
                $gender = 'MALE';
                break;
            default:
        }

        return [
            $data->uid,
            $data->flex_acc_no ?? '',
            strtoupper($data->name) ?? '',
            strtoupper($gender ?? ''),
            $data->dob,
            $data->tin,
            $data->identity,
            $country->iso2 ?? '',
            $data->created_at,
            strtoupper($data->address ?? ''),
            $data->dse_account ?? '',
            strtolower($data->email ?? ''),
            $data->mobile,
            strtoupper($data->bank_name ?? ''),
            strtoupper($data->bank_branch ?? ''),
            strtoupper($data->bank_account_name ?? ''),
            $data->bank_account_number,
            strtoupper($category->name ?? ''),
            strtoupper($custodian->name ?? '') ?? '',
            strtoupper($data->type ?? ''),
            strtoupper($data->risk_status ?? ''),
            strtoupper($data->status ?? ''),
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Flex Acc No',
            'Name',
            'Sex',
            'Dob',
            'Tin',
            'Identity',
            'Country',
            'Date',
            'Address',
            'DSE CDS No',
            'Email',
            'mobile',
            'Bank Name',
            'Bank Branch',
            'Bank Account Name',
            'Bank Account Number',
            'Category',
            'Custodian',
            'Type',
            'Risk Status',
            'Status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];

    }
}
