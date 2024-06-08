<?php

namespace Modules\MarketData\Imports;

use App\Helpers\Helper;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Modules\MarketData\Entities\InvestorsData;
use Modules\MarketData\Entities\MarketData;

class FundInvestorsDataImport implements ToModel, WithChunkReading, WithHeadingRow, WithValidation
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
        $date = ($row['date'] - 25569) * 86400;
        $date = date('Y-m-d', $date);

        $marketData = new \stdClass();
        $marketData->date = $date;
        $marketData->client_code = $row['client_code'];
        $marketData->fund_name = $row['fund_name'];
        $marketData->investor_name = $row['investor_name'];
        $marketData->investment = $row['investment'];
        $marketData->nav = $row['nav'];
        $marketData->units = $row['units'];
        $marketData->redemptions = $row['redemptions'];
        $marketData->redemption_nav = $row['redemption_nav'];
        $marketData->redemption_units = $row['redemption_units'];
        $marketData->net_investment = $row['net_investment'];
        $marketData->net_units = $row['net_units'];
        $marketData->fund_nav = $row['fund_nav'];
        $marketData->valuation = $row['valuation'];
        $marketData->gain_or_loss = $row['gain_or_loss'];

        return new InvestorsData((array) $marketData);
    }

    public function rules(): array
    {
        return [];
    }
}
