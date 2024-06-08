<?php

namespace Modules\MarketData\Imports;

use App\Helpers\Helper;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Modules\MarketData\Entities\MarketData;

class MarketDataImport implements ToModel, WithChunkReading, WithHeadingRow, WithValidation
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
        $security = \DB::table('securities')->where('name', $row['symbol'])->first();
        $time = Helper::systemDateTime();

        $marketData = new \stdClass();

        if (empty($row['change'])) {
            $latest = \DB::table('market_data')->where('company_id', $security->id ?? '')->orderBy('date', 'desc')->first();
            if (! empty($latest)) {
                $change = round(((($latest->close - $row['close']) * 100) / $latest->close), 2);
                $marketData->change = $change;
                if ($change > 0) {
                    $marketData->change = '+'.$change;
                }
            } else {
                $marketData->change = 2;
            }
        } else {
            $marketData->change = $row['change'];
        }
        $marketData->company_id = $security->id ?? '';
        $marketData->system_date = $time['today'];
        $marketData->date = $date;
        $marketData->symbol = str_ireplace("'", '', $row['symbol']);
        $marketData->open = str_ireplace("'", '', $row['open']);
        $marketData->prev_close = str_ireplace("'", '', $row['prev_close']);
        $marketData->close = str_ireplace("'", '', $row['close']);
        $marketData->high = str_ireplace("'", '', $row['high']);
        $marketData->low = str_ireplace("'", '', $row['low']);
        $marketData->turn_over = str_ireplace("'", '', $row['turn_over']);
        $marketData->deals = str_ireplace("'", '', $row['deals']);
        $marketData->out_standing_bid = str_ireplace("'", '', $row['out_standing_bid']);
        $marketData->out_standing_offer = str_ireplace("'", '', $row['out_standing_offer']);
        $marketData->volume = str_ireplace("'", '', $row['volume']);
        $marketData->mcap = str_ireplace("'", '', $row['mcap_tzs_b']);

        return new MarketData((array) $marketData);
    }

    public function rules(): array
    {
        return [];
    }
}
