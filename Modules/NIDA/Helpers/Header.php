<?php

namespace Modules\NIDA\Helpers;

class Header
{
//      private $ClientNameOrIP,$Id;

    public function setClientNameOrIP($clientNameOrIp)
    {
        $this->ClientNameOrIP = $clientNameOrIp;

    }

    public function setId($id)
    {
        $this->Id = $id;

    }

    public function setTimestamp($timestamp)
    {
        $this->TimeStamp = $timestamp;

    }

    public function setUserId($userId)
    {
        $this->UserId = $userId;
    }

}
