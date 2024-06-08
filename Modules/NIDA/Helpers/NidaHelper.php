<?php
namespace Modules\NIDA\Helpers;
error_reporting(E_ALL ^E_DEPRECATED);

require public_path("/nida/vendor/autoload.php");

use App\Nida\CryptoInfo;
use Exception;
use phpseclib3\Crypt\Rijndael;
use phpseclib3\Crypt\RSA;
use phpseclib3\File\X509;
use SoapClient;
use SoapHeader;

class NidaHelper
{
    public string $nin;
    public string $nidaPublicKey;
    public string $clientPublicKey;
    public string $clientPrivateKey;

    public function setNin($nin): string
    {
        $this->nin = $nin;
        return $this->nin;
    }


    public function getNinDetails()
    {
        $payloadStr = "<Payload><NIN>$this->nin</NIN></Payload>";
        $result = $this->sendToNida($payloadStr);
        $respXml= $this->processNidaResponse($result);
        $xml = simplexml_load_string($respXml);
        $json = json_encode($xml);
        return json_decode($json, TRUE);
    }

    private function sendToNida($payloadString)
    {
        $key = pack('H*', "bcb04b7e103a0cd8b54763051cef08bc55abe029fdebae5e1d417e2ffb2a00a3");

        $encryption = new Encryption();
        $encrypted = $encryption->encrypt($payloadString);
        $encryptedPayload = $encrypted['encrypted'];

        $irqBody = new Body();
        $signature = $this->signDigitally($encryptedPayload);

        $irqBody->setSignature(base64_encode($signature));
        $irqBody->setPayload($encryptedPayload);

        $options = array(
            'uri' => 'http://schemas.xmlsoap.org/soap/envelope/',
            'style' => 1,
            'use' => 1,
            'soap_version' => 2,
            'cache_wsdl' => 0,
            'connection_timeout' => 15,
            'trace' => true,
            'encoding' => 'UTF-8',
            'exceptions' => true,
        );


        $url = "https://nacer01/TZ_CIG/GatewayService.svc?singleWsdl";

        try {
            $client = new SoapClient($url, $options);
        } catch (\SoapFault $e) {
            return $e->getMessage();
        }

        $actionHeader = new SoapHeader('http://www.w3.org/2005/08/addressing',
            'Action',
            'http://tempuri.org/IGatewayService/QueryFullDemographic');

        $toHeader = new SoapHeader('http://www.w3.org/2005/08/addressing', 'To', 'https://nacer01/TZ_CIG/GatewayService.svc');
        $headers = [$toHeader, $actionHeader];
        $client->__setSoapHeaders($headers);

        $irqHeader = new Header;
        $irqHeader->setClientNameOrIp("");
        $irqHeader->setId("1");
        $irqHeader->setUserId("AG");
        $irqHeader->setTimestamp(date("Y/m/d H:i:s"));

        $params = array(
            'iRequest' => new iRequest($irqHeader, $irqBody));
        return $client->QueryFullDemographic($params);

    }

    private function processNidaResponse($result): string
    {
        try {
            $responseProc = new NidaResponseProcessor($result, $this->nidaPublicKey, $this->clientPrivateKey);
            $responseProc->verifySignature();
            return $responseProc->getPayload();
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}
