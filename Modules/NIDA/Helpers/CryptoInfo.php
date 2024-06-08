<?php

namespace App\Nida;

class CryptoInfo
{
    public function setEncryptedIV($encryptedIV){
        $this->EncryptedCryptoIV=$encryptedIV;

    }

    public function setEncryptedCryptoKey($encryptedCryptoKey){
        $this->EncryptedCryptoKey=$encryptedCryptoKey;

    }
}
