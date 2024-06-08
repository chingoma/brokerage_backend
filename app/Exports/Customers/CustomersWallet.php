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
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersWallet implements FromQuery, WithHeadings, WithMapping, WithStyles
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

        $custodianId = CustomerCustodian::where('status', 'active')->where('user_id', $row->id)->first();
        $custodian = Custodian::find($custodianId->custodian_id ?? '');
        $balance = $data->wallet_balance;
        $available = $data->wallet_available;

        return [
            $data->uid,
            $data->flex_acc_no ?? '',
            strtoupper($data->name ?? '') ?? '',
            strtoupper($gender ?? ''),
            strtoupper($category->name ?? '') ?? '',
            strtoupper($custodian->name ?? '') ?? '',
            strtoupper($data->type ?? '') ?? '',
            $balance,
            $data->wallet_status,
            $available,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Flex Acc No',
            'Name',
            'Sex',
            'Category',
            'Custodian',
            'Type',
            'Wallet',
            'Status',
            'Available',
        ];
    }

    //    public function columnFormats(): array
    //    {
    //        return [
    //            'I' => NumberFormat::FORMAT_NUMBER_0,
    //        ];
    //    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];

    }
}
