<?php

namespace xtype\Eos;

use Elliptic\EC;

class Ecc
{
    /**
     * Wif private key To private hex
     * @param $privateKey
     * @return bool|string
     * @throws \Exception
     */
    public static function wifPrivateToPrivateHex($privateKey)
    {
        return substr(Utils::checkDecode($privateKey), 2);
    }

    /**
     * Private hex To  wif private key
     * @param $privateHex
     * @return string
     * @throws \Exception
     */
    public static function privateHexToWifPrivate($privateHex)
    {
        return Utils::checkEncode(hex2bin('80' . $privateHex));
    }

    /**
     * 私钥转公钥
     * @param $privateKey
     * @param string $prefix
     * @return string
     * @throws \Exception
     */
    public static function privateToPublic($privateKey, $prefix = 'EOS')
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
     * @param bool $wif
     * @return string|null
     * @throws \Exception
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
     * @param $seed
     * @param bool $wif
     * @return string
     * @throws \Exception
     */
    public static function seedPrivate($seed, $wif = true)
    {
        $secret = hash('sha256', $seed);
        if ($wif) {
            return self::privateHexToWifPrivate($secret);
        }
        return $secret;
    }

    /**
     * 是否是合法公钥
     * @param string $public
     * @param string $prefix
     * @return bool
     */
    public static function isValidPublic($public, $prefix = 'EOS')
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
     * @param string $privateKey
     * @return bool
     */
    public static function isValidPrivate($privateKey)
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
     * @param string $data
     * @param string $privateKey
     * @return string
     * @throws \Exception
     */
    public static function sign($data, $privateKey)
    {
        $dataSha256 = hash('sha256', hex2bin($data));
        return self::signHash($dataSha256, $privateKey);
    }

    /**
     * 对hash进行签名
     * @param string $dataSha256
     * @param string $privateKey
     * @return string
     * @throws \Exception
     */
    public static function signHash($dataSha256, $privateKey)
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

        return 'SIG_K1_' . Utils::checkEncode(hex2bin($i . str_pad($r, 64, '0', STR_PAD_LEFT) . str_pad($s, 64, '0', STR_PAD_LEFT)), 'K1');
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
     * sha256 hash
     * @param $data
     * @param string $encoding
     */
    public static function sha256($data, $encoding = 'hex')
    {
        // TODO::
        // You can to use hash('sha256') of php;
    }
}
