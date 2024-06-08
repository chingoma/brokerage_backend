<?php

namespace Modules\DSE\Helpers;

use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\DSE\DTOs\BuyShareDTO;
use Modules\DSE\DTOs\DSEPayloadDTO;
use Modules\DSE\DTOs\InvestorAccountDetailsDTO;
use Modules\DSE\DTOs\IPOBuyOrderDTO;
use Modules\DSE\DTOs\PledgeTransactionsDTO;
use Modules\DSE\DTOs\PullBuyOrderDTO;
use Modules\DSE\DTOs\PullSellOrderDTO;
use Modules\DSE\DTOs\SellShareDTO;
use Modules\DSE\Entities\DSESettings;
use Modules\Orders\Entities\Order;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;
use phpseclib3\File\X509;
use stdClass;

class DSEHelper
{
    public static function getIPOCompanies(): mixed
    {
        try {
            $settings = DSESettings::first();
            $endpoint = $settings->base_url.'/stakeholders/ipo';
            $response = self::myClient($settings)
                ->send('GET', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            $body = (object) json_decode($response->body());

            if ($body->code == 9000) {
                return $body->data;
            } else {
                return self::errorMapper($body->code);
            }

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function createIPOBuyOrder(IPOBuyOrderDTO $inputData): string|bool|stdClass
    {
        try {
            $settings = DSESettings::first();
            $endpoint = $settings->base_url.'/stakeholders/ipo/buy-shares';
            $payloadRaw = self::generatePayload();
            $payloadRaw->payload = $inputData;

            $payload = json_encode($payloadRaw);

            $response = self::myClient($settings)
                ->withBody($payload)->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            $body = (object) json_decode($response->body());

            if ($body->code == 9000) {
                return true;
            } else {
                return self::errorMapper($body->code);
            }

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function syncAccount(InvestorAccountDetailsDTO $accountData): string|bool|stdClass
    {
        try {
            $settings = DSESettings::first();
            $endpoint = $settings->base_url.'/stakeholders/accounts';
            $payloadRaw = self::generatePayload();
            $payloadRaw->payload = $accountData;
            $payloadRaw->payload->brokerRef = $settings->broker_reference;

            $payload = json_encode($payloadRaw);

            $response = self::myClient($settings)
                ->withBody($payload)->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            $body = (object) json_decode($response->body());

            if (! empty($body->code)) {

                if ($body->code == 9050) {
                    $user = User::find($accountData->requestId);
                    if (! empty($user)) {
                        $user->dse_account = $body->data->csdAccount;
                        $user->save();
                    }
                }

                if ($body->code == 9000) {
                    return true;
                } else {
                    $error = self::errorMapper($body->code);
                    $user = User::find($accountData->requestId);
                    if (! empty($user)) {
                        $user->dse_status_message = $error->message;
                        $user->save();
                    }

                    return $error;
                }

            } else {

                return 'failed with unknown error code';
            }

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function createAccount(InvestorAccountDetailsDTO $accountData): string|bool|stdClass
    {
        try {

            if (strlen($accountData->nidaNumber) < 20) {
                $error = new stdClass();
                $error->code = 0000;
                $error->message = 'NIDA number is not valid';

                return $error;
            }

            $settings = DSESettings::first();
            $endpoint = $settings->base_url.'/stakeholders/accounts';
            $payloadRaw = self::signedPayload($accountData,$settings);

            $payload = json_encode($payloadRaw);

            $response = self::myClient($settings)
                ->withBody($payload)->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            $body = (object) json_decode($response->body());

            if (! empty($body->code)) {

                if ($body->code == 9050) {
                    $user = User::find($accountData->requestId);
                    if (! empty($user)) {
                        $user->dse_account = $body->data->csdAccount;
                        $user->dse_synced = 'yes';
                        $user->dse_status_message = 'yes';
                        $user->save();
                    }
                }

                if ($body->code == 9000) {
                    return true;
                } else {

                    $error = self::errorMapper($body->code);
                    $user = User::find($accountData->requestId);

                    if (! empty($user)) {
                        $user->dse_status_message = $error->message;
                        $user->save();
                    }

                    return $error;
                }

            } else {
                return 'failed with unknown error code';
            }

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function createToken(): bool|string|stdClass
    {
        try {
            $settings = DSESettings::first();
            $endpoint = $settings->base_url.'/oauth/token';
            $payload = json_encode([
                'username' => $settings->username,
                'password' => $settings->password,
                'grant_type' => $settings->grant_type,
            ]);
            $response = self::myClient($settings)
                ->withBody($payload)->send('POST', $endpoint)
                ->onError(function ($error) {
                    return false;
                });

            $body = (object) json_decode($response->body());

            if (! empty($body->access_token)) {
                $settings->access_token = $body->access_token;
                $settings->refresh_token = $body->refresh_token;
                $settings->expires_in = $body->expires_in;
                $settings->save();

                return true;
            } else {
                return self::errorMapper($body->code);
            }

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function buyShares(BuyShareDTO $inputData): string|bool|stdClass
    {
        try {
            $settings = DSESettings::first();
            $endpoint = $settings->base_url.'/stakeholders/investors/buy-shares';
            $data = new stdClass();
            $data->price = $inputData->price;
            $data->shares = $inputData->shares;
            $data->nidaNumber = $inputData->nidaNumber;
            $data->securityReference = $inputData->securityReference;

            $payload = json_encode(self::signedPayload($data,$settings));

            $response = self::myClient($settings)
                ->withBody($payload)->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            $body = (object) json_decode($response->body());

            if (empty($body->code)) {
                return self::errorMapper(000,$response->body());
            }

            if ($body->code == 9000) {
                $order = Order::find($inputData->orderId);
                $order->dse_reference = $body->rderReference;
                $order->save();

                return true;
            } else {
                return self::errorMapper($body->code);
            }

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function sellShares(SellShareDTO $inputData): string|bool|stdClass
    {
        try {
            $settings = DSESettings::first();
            $endpoint = $settings->base_url.'/stakeholders/investors/sell-shares';

            $payload = self::signedPayload($inputData,$settings);

            $response = self::myClient($settings)
                ->withBody(json_encode($payload))->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

           return self::processResponse($response);

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function pledge_transactions(PledgeTransactionsDTO $inputData): string|bool|stdClass
    {
        try {
            $settings = DSESettings::first();
            $endpoint = $settings->base_url.'/stakeholders/investors/pledge-transaction';
            $payloadRaw = self::generatePayload();
            $payloadRaw->payload = $inputData;
            $payloadRaw->brokerRef = $settings->broker_reference;

            $payload = json_encode($payloadRaw);

            $response = self::myClient($settings)
                ->withBody($payload)->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            $body = (object) json_decode($response->body());

            if ($body->code == 9000) {
                return true;
            } else {
                return self::errorMapper($body->code);
            }

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function releaseTransaction(PledgeTransactionsDTO $inputData): string|bool|stdClass
    {
        try {
            $settings = DSESettings::first();
            $endpoint = $settings->base_url.'/stakeholders/investors/release-transaction';
            $payloadRaw = self::generatePayload();
            $payloadRaw->payload = $inputData;
            $payloadRaw->brokerReference = $settings->broker_reference;

            $payload = json_encode($payloadRaw);

            $response = self::myClient($settings)
                ->withBody($payload)->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            $body = (object) json_decode($response->body());

            if ($body->code == 9000) {
                return true;
            } else {
                return self::errorMapper($body->code);
            }

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function verifyAccount(DSEPayloadDTO $inputData): void
    {
        try {
            $settings = DSESettings::first();
            $endpoint = $settings->base_url.'/stakeholders/accounts/verify';
            $payloadRaw = self::signedPayload($inputData,$settings);
            $payload = json_encode($payloadRaw);

            $response = self::myClient($settings)
                ->withBody($payload)->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            $body = (object) json_decode($response->body());

            if ($body->code == 9000) {

                $user = User::where('dse_account', $inputData->csdAccount)->first();
                if (! empty($user)) {
                    $user->dse_account_verified = 'verified';
                    $user->save();
                }
            }

        } catch (\Exception $exception) {
            report($exception->getMessage());
        }
    }

    public static function verifyLinkage(DSEPayloadDTO $inputData): void
    {
        try {
            $settings = DSESettings::first();
            $endpoint = $settings->base_url.'/stakeholders/accounts/verifyLinkage';
            $payloadRaw = self::signedPayload($inputData,$settings);
            $payloadRaw->payload = $inputData;

            $payload = json_encode($payloadRaw);

            $response = self::myClient($settings)
                ->withBody($payload)->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            $body = (object) json_decode($response->body());

            if ($body->code == 9000) {
                $user = User::where('dse_account', $inputData->csdAccount)->first();
                if (! empty($user)) {
                    $user->dse_account_linkage = 'linked';
                    $user->save();
                }
            }


        } catch (\Exception $exception) {
            report($exception->getMessage());
        }
    }

    public static function getBrokers(): mixed
    {
        try {
            $settings = DSESettings::first();
            $endpoint = $settings->base_url.'/stakeholders/external/brokers';
            $response = self::myClient($settings)
                ->send('GET', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            return self::processResponse($response);

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function getBuyOrders(PullBuyOrderDTO $inputData): mixed
    {
        try {
            $settings = DSESettings::first();
            $endpoint = $settings->base_url.'/stakeholders/investors/get-buy-orders';

            $payload = json_encode(self::signedPayload($inputData,$settings));

            $response = self::myClient($settings)
                ->withBody($payload)
                ->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            return self::processResponse($response);

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function getSellOrders(PullSellOrderDTO $inputData): mixed
    {
        try {
            $settings = DSESettings::first();
            $endpoint = $settings->base_url.'/stakeholders/investors/get-sell-orders';

            $payload = json_encode(self::signedPayload($inputData,$settings));

            $response = self::myClient($settings)
                ->withBody($payload)
                ->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            return self::processResponse($response);

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function getBuyOrderDetails(string $nida, string $reference): mixed
    {
        try {
            $settings = DSESettings::first();
            $endpoint = $settings->base_url.'/stakeholders/investors/get-buy-order-details';

            $payloadRaw = self::generatePayload();
            $payloadRaw->payload->nidaNumber = $nida;
            $payloadRaw->payload->orderReference = $reference;
            $payload = json_encode(self::signedPayload($payloadRaw,$settings));
            $response = self::myClient($settings)
                ->withBody($payload)
                ->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            $body = (object) json_decode($response->body());

            if ($body->code == 9000) {
                return $body->data;
            } else {
                return self::errorMapper($body->code);
            }

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function getSellOrderDetails(string $nida, string $reference): mixed
    {
        try {
            $settings = DSESettings::first();
            $endpoint = $settings->base_url.'/stakeholders/investors/get-sell-order-details';

            $payloadRaw = self::generatePayload();
            $payloadRaw->payload->nidaNumber = $nida;
            $payloadRaw->payload->orderReference = $reference;
            $payload = json_encode($payloadRaw);
            $response = self::myClient($settings)
                ->withBody($payload)
                ->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            $body = (object) json_decode($response->body());

            if ($body->code == 9000) {
                return $body->data;
            } else {
                return self::errorMapper($body->code);
            }

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function marketData(): mixed
    {
        try {
            $settings = DSESettings::first();
            $endpoint = $settings->base_url.'/stakeholders/investors/get-market-data';

            $payloadRaw = self::generatePayload();
            $payloadRaw->payload->nidaNumber = '12345678903421567892';
            $payload = json_encode($payloadRaw);
            $response = self::myClient($settings)
                ->withBody($payload)
                ->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            $body = (object) json_decode($response->body());

            if ($body->code == 9000) {
                return $body->data;
            } else {
                return self::errorMapper($body->code);
            }

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function marketDataStatistics(DSEPayloadDTO $inputData): mixed
    {
        try {
            $settings = DSESettings::first();
            $endpoint = $settings->base_url.'/stakeholders/investors/get-market-data-statistics';

            $payloadRaw = self::generatePayload();
            $payloadRaw->payload = $inputData;
            $payload = json_encode($payloadRaw);
            $response = self::myClient($settings)
                ->withBody($payload)
                ->send('POST', $endpoint)
                ->onError(function ($error) {
                    return $error;
                });

            $body = (object) json_decode($response->body());

            if ($body->code == 9000) {
                return $body->data;
            } else {
                return self::errorMapper($body->code);
            }

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function accountDetails(DSEPayloadDTO $inputData): mixed
    {
        try {
            $settings = DSESettings::first();
            $endpoint = $settings->base_url.'/stakeholders/accounts/details';
            $payloadRaw = self::generatePayload();
            $payloadRaw->payload = $inputData;
            $payload = json_encode($payloadRaw);
            $response = self::myClient($settings)
                ->withBody($payload)
                ->send('POST', $endpoint);

            $body = (object) json_decode($response->body());

            if ($body->code == 9000) {
                return $body->data;
            } else {
                return self::errorMapper($body->code);
            }

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function investorHoldings(DSEPayloadDTO $inputData): mixed
    {
        try {
            $settings = DSESettings::first();
            $endpoint = $settings->base_url.'stakeholders/investors/get-investor-holdings';
            $payloadRaw = self::generatePayload();
            $payloadRaw->payload = $inputData;
            $payload = json_encode($payloadRaw);
            $response = self::myClient($settings)
                ->withBody($payload)
                ->send('POST', $endpoint)
                ->onError(function ($error) {
                    return false;
                });

            $body = (object) json_decode($response->body());

            if ($body->code == 9000) {
                return $body->data;
            } else {
                return self::errorMapper($body->code);
            }

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function getSignature(string $data): string
    {

        // Load the .pfx file and extract the private key
        $pfxPath = resource_path('keys/DSE/itrust-dse-private.pfx');
        $password = 'iTrust@1990%'; // Password to access the .pfx file

        // Load the .pfx file
        if (!openssl_pkcs12_read(file_get_contents($pfxPath), $certs, $password)) {
            die('Failed to read .pfx file');
        }

        // Extract the private key
        $privateKey = $certs['pkey'];

        // Sign the data using SHA256withRSA
        if (!openssl_sign($data, $signature, $privateKey, "sha256WithRSAEncryption")) {
            die('Failed to sign data');
        }

        // Encrypt the signature using Base64 encoding
        return base64_encode($signature);

    }

    public static function errorMapper($errorCode, $errorMessage = ""): stdClass
    {
        $message = match ($errorCode) {
            000 => $errorMessage,
            9000 => 'Request processed successfully',
            9002 => 'Internal server error,it indicates unhandled exception has occurred',
            9050 => 'Investor has CSD account but not exists in MTP',
            9051 => 'Investor has no account',
            9052 => 'Broker reference provided does not exists',
            9053 => 'Investor has CSD account already,so can not be registered',
            9056 => 'Security reference provided is invalid',
            9057 => 'Invalid signature provided',
            9058 => 'National Identification Number (NIN) exists in MTP but has CSD account',
            9059 => 'Number of shares is not in multiple of [multiple value]',
            9060 => 'Share price is out of market limits [minimum, maximum]',
            9034 => 'Phone number already exist',
            9021 => 'Out of market limit',
            9025 => 'Client trade code not available',
            default => 'Unknown error',
        };

        $response = new stdClass();
        $response->code = $errorCode;
        $response->message = $message;

        return $response;
    }

    private static function myClient($settings): PendingRequest
    {
        return Http::withHeaders(['Authorization' => 'Bearer '.$settings->access_token])
            ->acceptJson()
            ->contentType('application/json')
            ->timeout($settings->timeout);
    }


    public static function signedPayload($data,$setting): stdClass
    {
        $data->brokerRef = $setting->broker_reference;
        $signature = self::getSignature(json_encode($data));
        $payloadRaw = new stdClass();
        $payloadRaw->signature = $signature;
        $payloadRaw->payload = $data;

        return $payloadRaw;
    }

    private static function processResponse(\GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response $response): bool|stdClass
    {
        $body = (object) json_decode($response->body());

        if (empty($body->code)) {
            return self::errorMapper(000,$response->body());
        }

        if ($body->code == 9000) {
            return true;
        } else {
            return self::errorMapper($body->code);
        }
    }


}
