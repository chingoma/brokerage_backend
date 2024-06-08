<?php

namespace Modules\Bonds\Imports;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Modules\Bonds\Entities\BondAuction;

class AuctionsImport implements ToModel, WithChunkReading, WithHeadingRow, WithValidation
{
    use Importable;

    public string $date;

    public function __construct(string $date)
    {
        $this->date = $date;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function model(array $row)
    {
        $date = date('Y-m-d', strtotime($this->date));
//      $security = \DB::table('bonds')->where('auction_number', $row['auction_number'])->first();

        $dateM = ($row['maturity_date'] - 25569) * 86400;
        $dateM = date('Y-m-d', $dateM);

//        $dateA = ($row['auction_date'] - 25569) * 86400;
//        $dateA = date('Y-m-d', $dateA);

        $marketData = new \stdClass();
        $marketData->coupon_frequency = $row['coupon_frequency']??"";
        $marketData->auction_number = $row['auction_number']??"";
        $marketData->auction_title = $row['auction_title']??"";
        $marketData->coupon = $row['coupon'] * 100;
        $marketData->auction_date = $row['auction_date']??"";
        $marketData->maturity_date = $dateM??"";
        $marketData->price = $row['price']??"";
        $marketData->highest_bid = $row['highest_bid']??"";
        $marketData->lowest_bid = $row['lowest_bid']??"";
        $marketData->yield = $row['yield']??"";
        $marketData->calculated_yield = $row['calculated_yield']??"";
        $marketData->yield_differential = $row['yield_differential']??"";
        $marketData->calculated_price = $row['calculated_price']??"";
        $marketData->price_differential = $row['price_differential']??"";
        $marketData->bond_id = "";
        $marketData->date = $date;

        return new BondAuction((array) $marketData);
    }

    public function rules(): array
    {
        return [];
    }
}
