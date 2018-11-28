<?php

namespace xtype\Eos;

use Elliptic\Curve\PresetCurve;
use Elliptic\EC\KeyPair;
use Elliptic\EC\Signature as ECSignature;
use Elliptic\HmacDRBG;
use BN\BN;
use Elliptic\EC;

class Signature
{
    public $ec;

    public function __construct()
    {
        $this->ec = new EC('secp256k1');
    }

    public function sign($data, $key, $i)
    {
        $key = $this->ec->keyFromPrivate($key, []);
        if ($i) {
            $msg = hash('sha256', hex2bin($data . str_pad('', $i * 2, '0')));
        } else {
            $msg = $data;
        }
        $msg = $this->_truncateToN(new BN($msg, 16));
        $data = $this->_truncateToN(new BN($data, 16));

        // Zero-extend key to provide enough entropy
        $bytes = $this->ec->n->byteLength();
        $bkey = $key->getPrivate()->toArray("be", $bytes);

        // Zero-extend nonce to have the same byte size as N
        $nonce = $msg->toArray("be", $bytes);

        $kFunc = null;
        if( isset($options["k"]) )
            $kFunc = $options["k"];
        else
        {
            // Instatiate HmacDRBG
            $drbg = new HmacDRBG(array(
                "hash" => $this->ec->hash,
                "entropy" => $bkey,
                "nonce" => $nonce,
                "pers" => "",
                "persEnc" => false
            ));

            $kFunc = function($iter) use ($drbg, $bytes) {
                return new BN($drbg->generate($bytes));
            };
        }

        // Number of bytes to generate
        $ns1 = $this->ec->n->sub(new BN(1));

        $canonical = true;
        for($iter = 0; true; $iter++)
        {
            $k = $kFunc($iter);
            $k = $this->_truncateToN($k, true);
            // var_dump($k);
            if( $k->cmpn(1) <= 0 || $k->cmp($ns1) >= 0 )
                continue;

            $kp = $this->ec->g->mul($k);
            if( $kp->isInfinity() )
                continue;

            $kpX = $kp->getX();
            $r = $kpX->umod($this->ec->n);
            if( $r->isZero() )
                continue;

            $s = $k->invm($this->ec->n)->mul($r->mul($key->getPrivate())->iadd($data));
            $s = $s->umod($this->ec->n);
            if( $s->isZero() )
                continue;

            $recoveryParam = ($kp->getY()->isOdd() ? 1 : 0) | ($kpX->cmp($r) !== 0 ? 2 : 0);

            // Use complement of `s`, if it is > `n / 2`
            if( $canonical && $s->cmp($this->ec->nh) > 0 )
            {
                $s = $this->ec->n->sub($s);
                $recoveryParam ^= 1;
            }

            return new ECSignature(array(
                "r" => $r,
                "s" => $s,
                "recoveryParam" => $recoveryParam
            ));
        }
    }

    private function _truncateToN($msg, $truncOnly = false)
    {
        $delta = intval(($msg->byteLength() * 8) - $this->ec->n->bitLength());
        if( $delta > 0 ) {
            $msg = $msg->ushrn($delta);
        }
        if( $truncOnly || $msg->cmp($this->ec->n) < 0 )
            return $msg;

        return $msg->sub($this->ec->n);
    }
}
