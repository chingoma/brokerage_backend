<?php

namespace Modules\Bonds\Entities;

use App\Helpers\Clients\Profile;
use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;

class BondOrder extends MasterModel implements \OwenIt\Auditing\Contracts\Auditable
{
    use \App\Traits\UuidForKey;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use UuidForKey;

    public function generateTags(): array
    {
        return [
            'admin, bond-order',
        ];
    }

    protected $fillable = [];

    protected $appends = ['client', 'bond', 'balance'];

    public function getClientAttribute(): ?Profile
    {
        return new Profile($this->getAttribute('client_id'));
    }

    public function getBondAttribute(): ?Bond
    {
        return Bond::find($this->getAttribute('bond_id'));
    }

    public function getBalanceAttribute($value)
    {
        $executed = BondExecution::where('status', 'approved')->where('order_id', $this->getAttribute('id'))->sum('executed');

        return $this->getAttribute('face_value') - $executed;
    }

    public function getExecutedAttribute($value)
    {
        return BondExecution::where('status', 'approved')->where('order_id', $this->getAttribute('id'))->sum('executed');
    }

    public function getPayoutAttribute($value)
    {
        return round(floatval($value), 4);
    }

    public function getTotalFeesAttribute($value)
    {
        return round(floatval($value), 4);
    }

    public function getDseAttribute($value)
    {
        return round(floatval($value), 4);
    }

    public function getCdsAttribute($value)
    {
        return round(floatval($value), 4);
    }

    public function getFidelityAttribute($value)
    {
        return round(floatval($value), 4);
    }

    public function getCmsaAttribute($value)
    {
        return round(floatval($value), 4);
    }

    public function getVatAttribute($value)
    {
        return round(floatval($value), 4);
    }
}
