<?php

namespace xtype\Eos;

use BN\BN;
use Elliptic\EC;
use Elliptic\Curve\PresetCurve;
use Elliptic\EC\KeyPair;
use Elliptic\EC\Signature as ECSignature;
use Elliptic\HmacDRBG;

class Ecc
{
    /**
     * 私钥
     */
    public function __construct()
    {

    }

    /**
     * Wif private key To private hex
     */
    public static function wifPrivateToPrivateHex(string $privateKey)
    {
        return substr(Utils::checkDecode($privateKey), 2);
    }

    /**
     * Private hex To  wif private key
     */
    public static function privateHexToWifPrivate(string $privateHex)
    {
        return Utils::checkEncode(hex2bin('80' . $privateHex));
    }

    /**
     * privateKey to Public
     * @param $privateKey string
     */
    public static function privateToPublic(string $privateKey, string $prefix = 'EOS')
    {
        // wif private
        $privateHex = self::wifPrivateToPrivateHex($privateKey);
        // var_dump($privateHex);
        $ec = new EC('secp256k1');
        $key = $ec->keyFromPrivate($privateHex);
        return $prefix . Utils::checkEncode(hex2bin($key->getPublic(true, 'hex')), null);
    }

    /**
     * 随机生成私钥
     * Random private key
     */
    public static function randomKey($wif = true)
    {
        $ec = new EC('secp256k1');
        $kp = $ec->genKeyPair();
        if ($wif) {
            return self::privateHexToWifPrivate($kp->getPrivate('hex'));
        }
        return $kp->getPrivate('hex');
    }

    /**
    * 根据种子生产私钥
    */
    public static function seedPrivate(string $seed, $wif = true)
    {
        $secret = hash('sha256', $seed);
        if ($wif) {
            return self::privateHexToWifPrivate($secret);
        }
        return $secret;
    }

    /**
     * 是否是合法公钥
     * @return boolean
     */
    public static function isValidPublic(string $public, string $prefix = 'EOS')
    {
        if (strtoupper(substr($public, 0, 3)) == strtoupper($prefix)) {
            try {
                Utils::checkDecode(substr($public, 3), null);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * 是否是合法wif私钥
     * @return boolean
     */
    public static function isValidPrivate(string $privateKey)
    {
        try {
            self::wifPrivateToPrivateHex($privateKey);
            return true;
        } catch(\Exception $e) {
            return false;
        }
    }

    /**
     * 签名
     * @param $data string
     * @param $privateKey wifi私钥
     */
    public static function sign(string $data, string $privateKey)
    {
        $dataSha256 = hash('sha256', hex2bin($data));
        return self::signHash($dataSha256, $privateKey);
    }

    /**
     * 对hash进行签名
     * @param $dataSha256 sha256
     * @param $privateKey wifi私钥
     */
    public static function signHash(string $dataSha256, string $privateKey)
    {
        $privHex = self::wifPrivateToPrivateHex($privateKey);
        $ecdsa = new Signature();

        $nonce = 0;
        while (true) {
            // Sign message (can be hex sequence or array)
            $signature = $ecdsa->sign($dataSha256, $privHex, $nonce);
            // der
            $der = $signature->toDER('hex');
            // Switch der
            $lenR = hexdec(substr($der, 6, 2));
            $lenS = hexdec(substr($der, (5 + $lenR) * 2, 2));
            // Need 32
            if ($lenR == 32 && $lenS == 32) {
                $r = $signature->r->toString('hex');
                $s = $signature->s->toString('hex');
                $i = dechex($signature->recoveryParam + 4 + 27);
                break;
            }

            $nonce++;
            if ($nonce % 10 == 0) {
                throw new \Exception('签名失败', 1);
            }
        }

        return 'SIG_K1_' . Utils::checkEncode(hex2bin($i . $r . $s), 'K1');
    }

    /**
     * Verify signed data.
     */
    public static function verify()
    {
        // TODO::
    }

    /**
     * Recover the public key used to create the signature.
     */
    public static function recover()
    {
        // TODO::
    }

    /**
     * Recover hash
     */
    public static function recoverHash()
    {
        // TODO::
    }

    /**
     * Recover hash
     */
    public static function sha256(string $data, $encoding = 'hex')
    {
        // TODO::
        // You can to use hash('sha256') of php;
    }
}
