<?php

// Code within app\Helpers\Helper.php

namespace App\Helpers;

class Security
{
    public static function createSignature(array $data)
    {

        //create new private and public key
        $new_key_pair = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        openssl_pkey_export($new_key_pair, $private_key_pem);

        $details = openssl_pkey_get_details($new_key_pair);
        $public_key_pem = $details['key'];

        //create signature
        openssl_sign(json_encode($data), $signature, $private_key_pem, OPENSSL_ALGO_SHA256);

        //save for later
        file_put_contents(\App\Helpers\Helper::storageArea('signature').$data['prefix'].'_private_key.pem', $private_key_pem);
        file_put_contents(\App\Helpers\Helper::storageArea('signature').$data['prefix'].'_public_key.pem', $public_key_pem);
        file_put_contents(\App\Helpers\Helper::storageArea('signature').$data['prefix'].'_signature.dat', $signature);

        return var_dump(file_get_contents(\App\Helpers\Helper::storageArea('signature').$data['prefix'].'_public_key.pem'));
    }

    public static function verifySignature(array $data, $public_key_pem): bool
    {
        //        $public_key_pem = file_get_contents(\App\Helpers\Helper::storageArea("signature").$data["prefix"].'_public_key.pem');
        $signature = file_get_contents(\App\Helpers\Helper::storageArea('signature').$data['prefix'].'_signature.dat');

        return openssl_verify(json_encode($data), $signature, $public_key_pem, 'sha256WithRSAEncryption');
    }
}
