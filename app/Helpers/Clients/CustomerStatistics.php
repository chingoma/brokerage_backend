<?php

namespace App\Helpers\Clients;

use App\Models\User;
use stdClass;

class CustomerStatistics
{
    public float $issues;

    public float $total;

    public float $pending;

    public float $new;

    public function __construct()
    {
        $this->total = User::customers()->count();
        $this->new = User::customers()->where('status', 'new')->count();
        $this->issues = User::customers()->whereNull('dse_account')->count();
        $this->pending = User::customers()->where('status', 'pending')->count();
        $this->kyc = User::customers()
            ->where('status', "active")
            ->where('onboard_status',"!=", "finished")
            ->count();
    }

    public function stats(): stdClass
    {

        $stdClass = new stdClass();
        $stdClass->issues = $this->issues;
        $stdClass->total = $this->total;
        $stdClass->new = $this->new;
        $stdClass->pending = $this->pending;
        $stdClass->kyc = $this->kyc;

        return $stdClass;
    }
}
