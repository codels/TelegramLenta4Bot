<?php

abstract class Encryption
{
    public static function encrypt($encrypt, $key)
    {
        $encrypt = serialize($encrypt);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
        $key = pack('H*', sprintf('%u', CRC32($key)));
        //костыль
        while (strlen($key) != 16) {
            $key = $key . "\0";
        }
        //конец костыля
        $mac = hash_hmac('sha256', $encrypt, substr(bin2hex($key), -32));
        $passCrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $encrypt . $mac, MCRYPT_MODE_CBC, $iv);
        $encoded = base64_encode($passCrypt) . '|' . base64_encode($iv);
        return $encoded;
    }

    public static function decrypt($decrypt, $key)
    {
        $decrypt = explode('|', $decrypt . '|');
        $decoded = base64_decode($decrypt[0]);
        $iv = base64_decode($decrypt[1]);
        if (strlen($iv) !== mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)) {
            return false;
        }
        $key = pack('H*', sprintf('%u', CRC32($key)));
        //костыль
        while (strlen($key) != 16) {
            $key = $key . "\0";
        }
        //конец костыля
        $decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $decoded, MCRYPT_MODE_CBC, $iv));
        $mac = substr($decrypted, -64);
        $decrypted = substr($decrypted, 0, -64);
        $calcMac = hash_hmac('sha256', $decrypted, substr(bin2hex($key), -32));
        if ($calcMac !== $mac) {
            return false;
        }
        $decrypted = unserialize($decrypted);
        return $decrypted;
    }
}
