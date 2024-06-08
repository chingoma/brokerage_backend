<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportBills implements FromArray, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    public function __construct()
    {
    }

    public function array(): array
    {
        $list = [
            '1' => [
                [
                    'name' => 'abdul 1',
                    'phone' => '23897523985',
                    'bill' => 'qwioroqwiurioweutoiweieroi',
                    'cont' => '28495729758',
                    'currency' => 'TZ',
                    'amount' => 10000,
                    'status' => 'paid',
                ],
                [
                    'name' => 'abdulY',
                    'phone' => '00007523985',
                    'bill' => 'cccccccccioweutoiweieroi',
                    'cont' => '999995729758',
                    'currency' => 'TZA',
                    'amount' => 80000,
                    'status' => 'paid',
                ],
                [
                    'name' => 'abdulX',
                    'phone' => '00007523985',
                    'bill' => 'first',
                    'cont' => '999995729758',
                    'currency' => 'TZA',
                    'amount' => 80000,
                    'status' => 'paid',
                ],
            ],
            '2' => [
                [
                    'name' => 'abdul 2',
                    'phone' => '23897523985',
                    'bill' => 'qwioroqwiurioweutoiweieroi',
                    'cont' => '28495729758',
                    'currency' => 'TZ',
                    'amount' => 10000,
                    'status' => 'paid',
                ],
                [
                    'name' => 'abdulY',
                    'phone' => '00007523985',
                    'bill' => 'cccccccccioweutoiweieroi',
                    'cont' => '999995729758',
                    'currency' => 'TZA',
                    'amount' => 80000,
                    'status' => 'paid',
                ],
                [
                    'name' => 'abdulX',
                    'phone' => '00007523985',
                    'bill' => 'last',
                    'cont' => '999995729758',
                    'currency' => 'TZA',
                    'amount' => 80000,
                    'status' => 'paid',
                ],
            ],
            '3' => [
                [
                    'name' => 'abdul 3',
                    'phone' => '23897523985',
                    'bill' => 'qwioroqwiurioweutoiweieroi',
                    'cont' => '28495729758',
                    'currency' => 'TZ',
                    'amount' => 10000,
                    'status' => 'paid',
                ],
                [
                    'name' => 'abdulY',
                    'phone' => '00007523985',
                    'bill' => 'cccccccccioweutoiweieroi',
                    'cont' => '999995729758',
                    'currency' => 'TZA',
                    'amount' => 80000,
                    'status' => 'paid',
                ],
                [
                    'name' => 'abdulX',
                    'phone' => '00007523985',
                    'bill' => 'last',
                    'cont' => '999995729758',
                    'currency' => 'TZA',
                    'amount' => 80000,
                    'status' => 'paid',
                ],
            ],
        ];

        $formatted = [];
        $formattedList = [];
        $formattedListLast = [];

        $ic = 0;
        foreach ($list as $i => $data) {
            $ic++;
            foreach ($data as $key => $datum) {

                if ($key == 0) {
                    $formatted[$key]['id'] = $ic;
                    $formatted[$key]['name'] = $datum['name'];
                    $formatted[$key]['phone'] = $datum['phone'];
                    $formatted[$key]['bill'] = $datum['bill'];
                } else {
                    $formatted[$key]['id'] = '';
                    $formatted[$key]['name'] = '';
                    $formatted[$key]['phone'] = '';
                    $formatted[$key]['bill'] = $datum['bill'];
                }

                $formattedList['id'] = $formatted[$key]['id'];
                $formattedList['name'] = $formatted[$key]['name'];
                $formattedList['phone'] = $formatted[$key]['phone'];
                $formattedList['bill'] = $formatted[$key]['bill'];

                $formattedListLast[] = $formattedList;
            }
        }

        $data = [];
        foreach ($formattedListLast as $i => $item) {
            $data[$i]['id'] = $item['id'];
            $data[$i]['name'] = $item['name'];
            $data[$i]['phone'] = $item['phone'];
            $data[$i]['bill'] = $item['bill'];
        }

        $this->rows = count($data) + 1;

        return $data;
    }

    /**
     * @var mixed
     */
    public function map($data): array
    {

        $data = (object) $data;

        return [
            $data->id,
            $data->name,
            $data->phone,
            $data->bill,
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'name',
            'phone',
            'bill',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];

    }
}
