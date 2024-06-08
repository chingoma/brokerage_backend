<?php

namespace App\Models;

use App\Traits\UuidForKey;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;

class DealingSheet extends MasterModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use UuidForKey;

    /**
     * {@inheritdoc}
     */
    public function generateTags(): array
    {
        return [
            'admin, contractNote',
        ];
    }

    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
        'settlement_date' => 'datetime:Y-m-d',
    ];

    protected $appends = [
        'client',
        'security',
        'order_number',
        'order_total',
        'order_balance',
        'is_migration',
    ];

    public function getIsMigrationAttribute(): bool
    {
        if ($this->getAttribute('price') == 0 && $this->getAttribute('order_id') == '') {
            return true;
        } else {
            return false;
        }
    }

    public function getClientAttribute()
    {
        return DB::table('users')->select(['id', 'name'])->find($this->getAttribute('client_id'));
    }

    public function getOrderNumberAttribute()
    {
        return DB::table('orders')->find($this->getAttribute('order_id'))->uid ?? 0;
    }

    public function getOrderTotalAttribute()
    {
        return DB::table('orders')->find($this->getAttribute('order_id'))->volume ?? 0;
    }

    public function getOrderBalanceAttribute()
    {
        return DB::table('orders')->find($this->getAttribute('order_id'))->balance ?? 0;
    }

    public function getPayoutAttribute($value): float
    {
        return $this->truncate_number(round(floatval($value), 3));
    }

    public function getTotalFeesAttribute($value): float
    {
        return $this->truncate_number(round(floatval($value), 3));
    }

    public function getAmountAttribute($value): float
    {
        return $this->truncate_number(round(floatval($value), 3));
    }

    public function getDseAttribute($value): float
    {
        return $this->truncate_number(round(floatval($value), 3));
    }

    public function getCdsAttribute($value): float
    {
        return $this->truncate_number(round(floatval($value), 3));
    }

    public function getFidelityAttribute($value): float
    {
        return $this->truncate_number(round(floatval($value), 3));
    }

    public function getCmsaAttribute($value): float
    {
        return $this->truncate_number(round(floatval($value), 3));
    }

    public function getVatAttribute($value): float
    {
        return $this->truncate_number(round(floatval($value), 3));
    }

    public function getSecurityAttribute()
    {
        return DB::table('securities')->select(['name', 'id'])->find($this->getAttribute('security_id'));
    }


    function truncate_number($number, $precision = 2): float|int
    {

        $cParts = explode('.', $number);

        if(!empty($cParts[1])) {
            if(strlen($cParts[1]) == 2){
                return $number;
            }
        }
        
        // Zero causes issues, and no need to truncate
        if (0 == (int)$number) {
            return $number;
        }

        // Are we negative?
        $negative = $number / abs($number);

        // Cast the number to a positive to solve rounding
        $number = abs($number);

        // Calculate precision number for dividing / multiplying
        $precision = pow(10, $precision);

        // Run the math, re-applying the negative value to ensure
        // returns correctly negative / positive
        return floor( $number * $precision ) / $precision * $negative;
    }

}
