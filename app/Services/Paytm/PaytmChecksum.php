<?php

class PaytmChecksum
{
    private static $iv = "@@@@&&&&####$$$$";

    public static function generateSignature($params, $key)
    {
        if (is_array($params)) {
            ksort($params);
            $params = implode('|', array_values($params));
        }
        return self::generateSignatureByString($params, $key);
    }

    public static function verifySignature($params, $key, $checksum)
    {
        if (isset($params['CHECKSUMHASH'])) {
            unset($params['CHECKSUMHASH']);
        }
        ksort($params);
        $params = implode('|', array_values($params));
        return self::verifySignatureByString($params, $key, $checksum);
    }

    private static function generateSignatureByString($params, $key)
    {
        $salt = self::generateRandomString(4);
        $hash = hash("sha256", $params . '|' . $salt) . $salt;
        return self::encrypt($hash, $key);
    }

    private static function verifySignatureByString($params, $key, $checksum)
    {
        $decrypted = self::decrypt($checksum, $key);
        $salt = substr($decrypted, -4);
        $hash = hash("sha256", $params . '|' . $salt) . $salt;
        return $decrypted === $hash;
    }

    private static function encrypt($data, $key)
    {
        return openssl_encrypt($data, "AES-128-CBC", $key, 0, self::$iv);
    }

    private static function decrypt($data, $key)
    {
        return openssl_decrypt($data, "AES-128-CBC", $key, 0, self::$iv);
    }

    private static function generateRandomString($length)
    {
        $chars = "9876543210ZYXWVUTSRQPONMLKJIHGFEDCBAabcdefghijklmnopqrstuvwxyz!@#$&_";
        $random = '';
        for ($i = 0; $i < $length; $i++) {
            $random .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $random;
    }
}
