<?php

namespace App\Models;

use Exception;
use Jenssegers\Agent\Agent;

class Token extends MasterModel
{
    protected $table = 'personal_access_tokens';

    protected $appends = [
        'agent',
        'location',
    ];

    public function getAgentAttribute($value)
    {

        $agent = new Agent();
        $agent->setUserAgent($this->getAttribute('name'));

        return [
            'browser' => $agent->browser(),
            'platform' => $agent->platform(),
            'device' => $agent->device(),
        ];
    }

    public function getLocationAttribute(): string
    {
        try {
            $position = geoip()->getLocation($this->getAttribute('ip_address'));
        } catch (Exception $e) {
            $position = '';
        }

        return $position->city.','.$position->country;
    }
}
