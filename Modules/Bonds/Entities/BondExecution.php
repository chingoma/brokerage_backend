<?php

namespace Modules\Bonds\Entities;

use App\Helpers\Clients\Profile;
use App\Helpers\Clients\SimpleProfile;
use App\Models\MasterModel;

class BondExecution extends MasterModel implements \OwenIt\Auditing\Contracts\Auditable
{
    use \App\Traits\UuidForKey;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [];

    public function generateTags(): array
    {
        return [
            'admin, bond-execution',
        ];
    }

    protected $appends = ['client', 'bond', 'order'];

    public function getClientAttribute(): ?SimpleProfile
    {
        return new SimpleProfile($this->getAttribute('client_id'));
    }

    public function getBondAttribute()
    {
        return \DB::table("bonds")->find($this->getAttribute('bond_id'));
    }

    public function getOrderAttribute()
    {
        return \DB::table("bond_orders")->find($this->getAttribute('order_id'));
    }

    public function getPayoutAttribute($value)
    {
        return $this->truncate_number(round(floatval($value), 3));
    }

    public function getTotalFeesAttribute($value)
    {
        return $this->truncate_number(round(floatval($value), 3));
    }

    public function getDseAttribute($value)
    {
        return $this->truncate_number(round(floatval($value), 3));
    }

    public function getCdsAttribute($value)
    {
        return $this->truncate_number(round(floatval($value), 3));
    }

    public function getFidelityAttribute($value)
    {
        return $this->truncate_number(round(floatval($value), 3));
    }

    public function getCmsaAttribute($value)
    {
        return $this->truncate_number(round(floatval($value), 3));
    }

    public function getVatAttribute($value)
    {
        return $this->truncate_number(round(floatval($value), 3));
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
