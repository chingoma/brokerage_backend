<?php

namespace Modules\NIDA\Helpers;

use Dotenv\Validator;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SoapClient;
use SoapHeader;
use stdClass;

class NIDAFunctions
{

    public static function getDetails(string $nidaNumber): JsonResponse
    {
        try {

            if (strlen($nidaNumber) < 20) {
                $error = new stdClass();
                $error->code = 0000;
                $error->message = 'NIDA number is not valid';

                return response()->json($error);
            }

            $payloadRaw = self::signedPayload($nidaNumber);
            $irqBody = new Body();
            $irqBody->setSignature($payloadRaw->signature);
            $irqBody->setPayload($payloadRaw->payload);

            $options = array(
                'uri' => 'http://schemas.xmlsoap.org/soap/envelope/',
                'style' => 1,
                'use' => 1,
                'soap_version' => 1.1,
                'cache_wsdl' => 0,
                'connection_timeout' => 15,
                'trace' => true,
                'encoding' => 'UTF-8',
                'exceptions' => true,
            );

            $url = self::baseUrl();

            try {
              return  self::soap([]);
            } catch (\SoapFault $e) {
                return response()->json($e->getMessage());
            }
//
            $actionHeader = new SoapHeader('http://www.w3.org/2005/08/addressing',
                'Action',
                'http://tempuri.org/IGatewayService/QueryFullDemographic');

            try {
                $client = new SoapClient(resource_path('service.xml'),$options);
            } catch (\SoapFault $e) {
                return response()->json($e->getMessage());
            }

            $toHeader = new SoapHeader('http://www.w3.org/2005/08/addressing', 'To', 'https://nacer01/TZ_CIG/GatewayService.svc');
            $headers = [$toHeader, $actionHeader];
            $client->__setSoapHeaders($headers);

            $irqHeader = new Header;
            $irqHeader->setClientNameOrIp("");
            $irqHeader->setId("1");
            $irqHeader->setUserId("AG");
            $irqHeader->setTimestamp(date("Y/m/d H:i:s"));

            $params = array('iRequest' => new iRequest($irqHeader, $irqBody));

            return $client->QueryFullDemographic($params);

            $settings = new stdClass();
            $settings->timeout = 180000;
            $settings->access_token = "";

            $response = self::myClient($settings)
                ->withBody($payload)
                ->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            return  json_decode([]);

        } catch (\Exception $exception) {
            return response()->json($exception->getMessage());
        }
    }

    private static function myClient($settings): PendingRequest
    {
        return Http::withHeaders(['Authorization' => 'Bearer '.$settings->access_token])
            ->acceptJson()
            ->contentType('application/json')
            ->timeout($settings->timeout);
    }

    private static function baseUrl(): string
    {
        return "http://192.168.1.40:8443/nida/TZ_CIG/GatewayService.svc?wsdl";
    }

    public static function getSignature(string $data): string
    {

        // Load the .p12 file and extract the private key
        $pfxPath = resource_path('keys/NIDA/IMAAN.p12');
        $password = 'imaan@2023'; // Password to access the .p12 file

        // Load the .pfx file
        if (!openssl_pkcs12_read(file_get_contents($pfxPath), $certs, $password)) {
            die(json_encode([
                    "status" => false,
                    "message" => 'Failed to read .p12 file',
                    "errors"  => ['Failed to read .p12 file']
                ])
            );
        }

        // Extract the private key
        $privateKey = $certs['pkey'];

        // Sign the data using SHA256withRSA
        if (!openssl_sign($data, $signature, $privateKey, "sha256WithRSAEncryption")) {
            die(json_encode([
                "status" => false,
                "message" => 'Failed to sign data',
                "errors"  => ['Failed to sign data']
                ]));
        }

        // Encrypt the signature using Base64 encoding
        return base64_encode($signature);

    }

    public static function signedPayload($data): stdClass
    {
        $signature = self::getSignature(json_encode($data));
        $payloadRaw = new stdClass();
        $payloadRaw->signature = $signature;
        $payloadRaw->payload = $data;

        return $payloadRaw;
    }



    public static function soap($param)
    {
        $endpoint = "https://192.168.1.40:8443/nida/TZ_CIG/GatewayService.svc?singleWsdl";
        $soapAction = "http://tempuri.org/IGatewayService/QueryFullDemographic";
        $xml = '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:tem="http://tempuri.org/" xmlns:nid="http://schemas.datacontract.org/2004/07/NID_API">
                   <soap:Header xmlns:wsa="http://www.w3.org/2005/08/addressing">
                    <wsa:To>https://192.168.1.40:8443/nida/TZ_CIG/GatewayService.svc</wsa:To>
                   </soap:Header>
                    <soap:Header xmlns:wsa="http://www.w3.org/2005/08/addressing">
                    <wsa:Action>http://tempuri.org/IGatewayService/QueryFullDemographic</wsa:Action>
                   </soap:Header>
                   <soap:Body>
                      <tem:QueryFullDemographic>
                         <tem:iRequest>
                            <!--Optional:-->
                            <nid:Body>
                               <!--Optional:-->
                               <nid:Payload>19870427141240000127</nid:Payload>
                               <!--Optional:-->
                               <nid:Signature>358348530985u509385093480934809648906409640943690490</nid:Signature>
                            </nid:Body>
                         </tem:iRequest>
                      </tem:QueryFullDemographic>
                   </soap:Body>
                </soap:Envelope>';
        try {
            Log::error($xml);
            $response = Http::withHeaders([
                "Content-Type" => "application/soap+xml",
                "SOAPAction" => $soapAction
            ])
                ->withoutVerifying()
                ->send("POST", $endpoint, [
                "body" => $xml
            ]);

            Log::error($response->body());
            return response()->json($response->body());
            return self::prepareXmlResponse($response);
        } catch (\SoapFault $exception) {
            report($exception);
            return $exception->getMessage();
        }
    }

    static function prepareXmlResponse($xml)
    {


        Log::error($xml);
        // Remove namespaces
        $noNamespaces = preg_replace('/xmlns[^=]*="[^"]*"/i', '',  $xml);

        // Remove leading s: , Envelop and Body
        $noS = str_replace("s:", "", $noNamespaces);

        // Remove Envelop
        $noEnvelop = str_replace("<Envelop>", "", $noS);
        $noEnvelop = str_replace("</Envelop>", "", $noEnvelop);

        // Remove Body
        $noBody = str_replace("<Body>", "", $noEnvelop);
        $noBody = str_replace("</Body>", "", $noBody);
        $result = simplexml_load_string($noBody, "SimpleXMLElement", LIBXML_NOCDATA);

        $json = json_encode($result);
        $result = json_decode($json, TRUE);

        if (!empty($result['Fault'])) {
            $response['status'] = false;
            $response['code'] = $result['Fault']['faultcode'];
            $response['message'] = $result['Fault']['faultstring'];
            return  $response;
        } else {
            return  self::getResponse($result);
        }
    }

   static function getResponse($response)
    {
        return $response['QueryResponse']['QueryResult']['ResponseXml']['response']['connector']['data']['response'];
    }
}