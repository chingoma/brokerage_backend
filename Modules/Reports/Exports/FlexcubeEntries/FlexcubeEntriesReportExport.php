<?php

namespace Modules\Reports\Exports\FlexcubeEntries;

use App\Helpers\Clients\Profile;
use App\Models\DealingSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Modules\Bonds\Entities\BondExecution;
use Modules\CRM\Entities\CustomerCustodian;
use Modules\Custodians\Entities\Custodian;
use Modules\FlexcubeAPI\Entities\FlexcubeTransactions;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FlexcubeEntriesReportExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle
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


    public function map($data): array
    {
        $user =  \DB::table('users')->where("flex_acc_no",$data->ac_no)->first();
        if(!empty($user)){
            $desc = $user->name;
        }else{
           $fl_gl = \DB::table('flexcube_chart_accounts')->where("gl_code",$data->ac_no)->first();
            $desc = $fl_gl->gl_desc??"";
        }
        return [
            $data->batch_no,
            $data->trn_ref_no,
            $data->syncled_at,
            $data->value_dt,
            $data->ac_ccy,
            $data->ac_branch,
            $data->ac_no,
            $desc,
            $data->addl_text,
            $data->event,
            $data->module,
            $data->instrument_code,
            $data->debits,
            $data->credits
        ];
    }

    public function headings(): array
    {
        return [
            'BATCH_NO',
            'TRN_REF_NO',
            'TRN_DT',
            'VALUE_DT',
            'AC_CCY',
            'AC_BRANCH',
            'AC_NO',
            'AC_GL_DESC',
            'ADDL_TEXT',
            'EVENT',
            'MODULE',
            'INSTRUMENT_CODE',
            'DEBITS',
            'CREDITS',
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
        $query = FlexcubeTransactions::query()
            ->orderBy('datetime', 'desc');
//        $query = FlexcubeTransactions::query()
//            ->orderBy('value_dt', 'desc')
//            ->whereDate('value_dt', '>=', $this->from)
//            ->whereDate('value_dt', '<=', $this->end);

        $this->rows = $query->count() + 1;

        return $query;
    }

    public function title(): string
    {
        return 'Flexcube Entries';
    }
}
