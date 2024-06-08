<?php

namespace Modules\NIDA\Helpers;

use App\Nida\CryptoInfo;

class Body
{

    private CryptoInfo $CryptoInfo;
    private mixed $Signature;
    private mixed $Payload;

    public function setCryptoInfo(CryptoInfo $cryptoInfo): void
    {
        $this->CryptoInfo = $cryptoInfo;
    }

    public function setPayload($payload): void
    {
        $this->Payload = $payload;
    }

    public function setSignature($signature): void
    {
        $this->Signature = $signature;

    }
}
