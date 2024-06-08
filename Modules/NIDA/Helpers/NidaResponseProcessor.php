<?php
namespace Modules\NIDA\Helpers;


use Exception;
use phpseclib3\Crypt\Rijndael;

class NidaResponseProcessor
{

    /**
     * @throws Exception
     */
    public function __construct($result, $msgPubKey, $myPrivKey)
    {
        $this->result = $result;
        $this->msgPubKey = $msgPubKey;
        $this->myPrivKey = $myPrivKey;

        $this->body = $result->QueryFullDemographicResult->Body;
        if ($this->body) {
            $this->header = $result->QueryFullDemographicResult->Header;
            $body = $this->body;
            $this->responseCode = $result->QueryFullDemographicResult->Code;
            $this->encPayloadBin = base64_decode($body->Payload);
            $this->signatureBin = base64_decode($body->Signature);
            $this->cryptoInfo = $body->CryptoInfo;
            $this->encIVBin = base64_decode($body->CryptoInfo->EncryptedCryptoIV);
            $this->encKeyBin = base64_decode($body->CryptoInfo->EncryptedCryptoKey);
        } else {
            throw new Exception("Body is null", 1);
        }
    }


    //      function encryptByRijdael256($data,$key,$iv){
    //  //Encrypt payload using Raijdael 256
    // $rijndael = new Rijndael();// could use Rijndael::MODE_CBC
    // $rijndael->setBlockLength(256);
    // $rijndael->setKey($key);
    // // the IV defaults to all-NULLs if not explicitly defined
    // $rijndael->setIV($iv);
    // return $rijndael->encrypt($data);

    // }


    function decryptCryptoInfo()
    {
        //base64_decode($body->CryptoInfo->EncryptedCryptoKey);
        $this->myPrivKey->setEncryptionMode(2);

//              echo $this->cryptoInfo->EncryptedCryptoIV."   IV NI HID \n\n";
//              echo $this->cryptoInfo->EncryptedCryptoKey." ***** Enc Key hiyoooo \n\n";

        $this->cryptoInfo->iv = $this->myPrivKey->decrypt(base64_decode($this->cryptoInfo->EncryptedCryptoIV));
        $this->cryptoInfo->key = $this->myPrivKey->decrypt(base64_decode($this->cryptoInfo->EncryptedCryptoKey));
//              echo "\n\n*********** IV ".$this->cryptoInfo->iv." key ".$this->cryptoInfo->key;
//              echo "Base 64 encoded na keyyyy ni hyoo &&&& ".base64_encode($this->cryptoInfo->key);
    }


    public function getPayload()
    {
        $this->decryptCryptoInfo();
        //Encrypt payload using Raijdael 256

        $rijndael = new Rijndael(Rijndael::MODE_CBC);// could use Rijndael::MODE_CBC
        $rijndael->setBlockLength(256);
        $rijndael->setKey($this->cryptoInfo->key);
        // the IV defaults to all-NULLs if not explicitly defined
        $rijndael->setIV($this->cryptoInfo->iv);
        return $rijndael->decrypt($this->encPayloadBin);

    }

    function verifySignature()
    {
        $this->msgPubKey->setSignatureMode(2);
        return $this->msgPubKey->verify($this->encPayloadBin, $this->signatureBin);
    }


}
