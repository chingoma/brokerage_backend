<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Calendar\Entities\Calendar;

class HolidaysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return false
     */
    public function run()
    {
        $this->truncateLaratrustTables();

    }

    /**
     * Truncates  table
     *
     * @return void
     */
    public function truncateLaratrustTables()
    {
        try {
            Schema::disableForeignKeyConstraints();
            DB::table('calendars')->truncate();
            DB::beginTransaction();
            $holidays[0]['2021'] = ['HWHWWWWWWHWHWWWWHWWWWWWHWWWWWWH', 'WWWWWWHWWWWWWHWWWWWWHWWWWWWH', 'WWWWWWHWWWWWWHWWWWWWHWWWWWWHWWW', 'WHWHHWHWWWHWWWWWWHWWWWWWHHWWWW', 'HHWWWWWWHWWWHHWHWWWWWWHWWWWWWHW', 'WWWWWHWWWWWWHWWWWWWHWWWWWWHWWW', 'WWWHWWHWWWHWWWWWWHWHHWWWHWWWWWW', 'HWWWWWWHWWWWWWHWWWWWWHWWWWWWHWW', 'WWWWHWWWWWWHWWWWWWHWWWWWWHWWWW', 'WWHWWWWWWHWWWHWWHHHWWWWHWWWWWWH', 'WWWWWWHWWWWWWHWWWWWWHWWWWWWHWW', 'WWWWHWWWHWWHWWWWWWHWWWWWHHWWWWW'];
            $holidays[1]['2022'] = ['HHWWWWWWHWWHWWWHWWWWWWHWWWWWWHW', 'WWWWWHWWWWWWHWWWWWWHWWWWWWHW', 'WWWWWHWWWWWWHWWWWWWHWWWWWWHWWWW', 'WWHWWWWWWHWWWWHWHHWWWWWHWHWWWW', 'HWHHWWWHWWWWWWHWWWWWWHWWWWWWHWW', 'WWWWHWWWWWWHWWWWWWHWWWWWWHWWWW', 'WWWWWWWWWHWWWWWWHWWWWWWHWWWWWWH', 'WWWWWWHHWWWWWHWWWWWWHWWWWWWHWWW', 'WWWHWWWWWWHWWWWWWHWWWWWWHWWWWW', 'WHWWWWWWHWWWWWWHWWWWWWHWWWWWWHW', 'WWWWWHWWWWWWHWWWWWWHWWWWWWHWWW', 'WWWHWWWWHWHWWWWWWHWWWWWWHHWWWWW'];
            $holidays[2]['2023'] = ['HWWWWWWHWWWHWWHWWWWWWHWWWWWWHWW', 'WWWWHWWWWWWHWWWWWWHWWWWWWHWW', 'WWWWHWWWWWWHWWWWWWHWWWWWWHWWWWW', 'WHWWWWHWHHWWWWWHWWWWWHHWWHWWWH', 'HWWWWWHWWWWWWHWWWWWWHWWWWWWHWWW', 'WWWHWWWWWWHWWWWWWHWWWWWWHWWWWW', 'WHWWWWHWHWWWWWWHWWWWWWHWWWWWWHW', 'WWWWWHWHWWWWHWWWWWWHWWWWWWHWWWW', 'WWHWWWWWWHWWWWWWHWWWWWWHWWWHWW', 'HWWWWWWHWWWWWHHWWWWWWHWWWWWWHWW', 'WWWWHWWWWWWHWWWWWWHWWWWWWHWWWW', 'WWHWWWWWHHWWWWWWHWWWWWWHHHWWWWH'];
            $holidays[3]['2024'] = ['HWWWWWHWWWWWWHWWWWWWHWWWWWWHWWW', 'WWWHWWWWWWHWWWWWWHWWWWWWHWWWW', 'WWHWWWWWWHWWWWWWHWWWWWWHWWWWWWH', 'WWWWWWHWWWWWWHWWWWWWHWWWWWWHWW', 'WWWWHWWWWWWHWWWWWWHWWWWWWHWWWWW', 'WHWWWWWWHWWWWWWHWWWWWWHWWWWWWH', 'WWWWWWHWWWWWWHWWWWWWHWWWWWWHWWW', 'WWWHWWWWWWHWWWWWWHWWWWWWHWWWWWW', 'HWWWWWWHWWWWWWHWWWWWWHWWWWWWHW', 'WWWWWHWWWWWWHWWWWWWHWWWWWWHWWWW', 'WWHWWWWWWHWWWWWWHWWWWWWHWWWWWW', 'HWWWWWWHWWWWWWHWWWWWWHWWWWWWHWW'];
            $holidays[4]['2025'] = ['WWWWHWWWWWWHWWWWWWHWWWWWWHWWWWW', 'WHWWWWWWHWWWWWWHWWWWWWHWWWWW', 'WHWWWWWWHWWWWWWHWWWWWWHWWWWWWHW', 'WWWWWHWWWWWWHWWWWWWHWWWWWWHWWW', 'WWWHWWWWWWHWWWWWWHWWWWWWHWWWWWW', 'WWWWWWWHWWWWWWHWWWWWWHWWWWWWHW', 'WWWWWHWWWWWWHWWWWWWHWWWWWWHWWWW', 'WWHWWWWWWHWWWWWWHWWWWWWHWWWWWWH', 'WWWWWWHWWWWWWHWWWWWWHWWWWWWHWW', 'WWWWHWWWWWWHWWWWWWHWWWWWWHWWWWW', 'WHWWWWWWHWWWWWWHWWWWWWHWWWWWWH', 'WWWWWWHWWWWWWHWWWWWWHWWWWWWHWWW'];
            foreach ($holidays as $years) {
                foreach ($years as $year => $months) {
                    if (! empty($months)) {
                        foreach ($months as $month => $days) {
                            $list = str_split($days);
                            if (! empty($list)) {
                                $month = $month + 1;
                                foreach ($list as $i => $item) {
                                    $day = $i + 1;
                                    $date = $year.'-'.$month.'-'.$day;
                                    $now = Carbon::createFromFormat('Y-m-d', $date);

                                    $dateCheck = date('Y-m-d', strtotime($date));
                                    $status = Calendar::where('today', $dateCheck)->first();
                                    if (empty($status)) {
                                        $calendar = new Calendar();
                                        $calendar->today = date('Y-m-d', strtotime($date));
                                        $calendar->start = date('Y-m-d H:i:s', strtotime($date));
                                        $calendar->end = date('Y-m-d H:i:s', strtotime($date));

                                        $calendar->calendar = 'Business';
                                        $calendar->title = 'Business';
                                        $calendar->weekend = false;

                                        if (strtolower($item) == 'h') {
                                            $calendar->calendar = 'Holiday';
                                            $calendar->title = 'Holiday';
                                        }

                                        if ($now->isSunday()) {
                                            $calendar->calendar = 'Weekend';
                                            $calendar->title = 'Weekend';
                                            $calendar->weekend = true;
                                        }

                                        if ($now->isSaturday()) {
                                            $calendar->calendar = 'Weekend';
                                            $calendar->title = 'Weekend';
                                            $calendar->weekend = true;
                                        }

                                        $today = now(getenv('TIMEZONE'));
                                        $dateCheck = Carbon::createFromFormat('Y-m-d', $date);
                                        $dateCheck->addDay();
                                        if ($dateCheck->lessThan($today)) {
                                            $calendar->closed = true;
                                        } else {
                                            $calendar->closed = false;
                                        }

                                        $calendar->save();
                                    }
                                }
                            }
                        }
                    }

                }

            }
            DB::commit();
            Schema::enableForeignKeyConstraints();
        } catch (\Throwable $throwable) {
            DB::rollBack();
            $this->command->error($throwable->getMessage());
            exit();
        }

    }
}
