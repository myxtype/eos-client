<?php

/**
 * It's a little bad.
 * Hope to be able to help rectify
 */

namespace xtype\Eos;

class Serialize
{
    public static $types = [
        'expiration' => 'f_uint32',
        'ref_block_num' => 'f_uint16',
        'ref_block_prefix' => 'f_uint32',
        'max_net_usage_words' => 'f_varuint32',
        'max_cpu_usage_ms' => 'f_uint8',
        'delay_sec' => 'f_uint8',
        'actions' => 'f_vector',
        'account' => 'f_name',
        'name' => 'f_name',
        'authorization' => 'f_vector',
        'actor' => 'f_name',
        'permission' => 'f_name',
        'data' => 'f_data',
        'transaction_extensions' => 'f_vector',
        'context_free_actions' => 'f_vector',
    ];

    public static function transtion(array $data)
    {
        return self::encode($data);
    }

    public static function encode($data, $name = '')
    {
        $buffer = '';
        if (is_array($data) && $name === '') {
            foreach ($data as $key => $value) {
                $buffer .= self::encode($value, $key);
            }
        } else {
            $method = self::$types[$name];
            $buffer .= call_user_func([new static , $method], $data);
        }
        return $buffer;
    }

    public static function f_uint8($i)
    {
        $i = is_int($i) ? pack("C", $i) : unpack("C", $i)[1];
        return bin2hex($i);
    }

    public static function f_uint16($i, $endianness = false)
    {
        $f = is_int($i) ? "pack" : "unpack";

        if ($endianness === true) {  // big-endian
            $i = $f("n", $i);
        } else if ($endianness === false) {  // little-endian
            $i = $f("v", $i);
        } else if ($endianness === null) {  // machine byte order
            $i = $f("S", $i);
        }
        $i = is_array($i) ? $i[1] : $i;
        return bin2hex($i);
    }

    public static function f_uint32($i, $endianness = false)
    {
        $f = is_int($i) ? "pack" : "unpack";

        if ($endianness === true) {  // big-endian
            $i = $f("N", $i);
        } else if ($endianness === false) {  // little-endian
            $i = $f("V", $i);
        } else if ($endianness === null) {  // machine byte order
            $i = $f("L", $i);
        }
        return bin2hex($i);
    }

    public static function f_varuint32($i)
    {
        $t = '';
        while (true) {
            if ($i >> 7) {
                $t .= self::f_uint8(0x80 | ($i & 0x7f));
                $i = $i >> 7;
            } else {
                $t .= self::f_uint8($i);
                break;
            }
        }
        return $t;
    }

    public static function f_vector($i)
    {
        $buffer = self::f_varuint32(count($i));
        foreach ($i as $key => $value) {
            $buffer .= self::encode($value);
        }
        return $buffer;
    }

    public static function f_name($s)
    {
        $charToSymbol = function ($c) {
            if ($c >= ord('a') && $c <= ord('z')) {
                return ($c - ord('a')) + 6;
            }
            if ($c >= ord("1") && $c <= ord('5')) {
                return ($c - ord('1')) + 1;
            }
            return 0;
        };
        $a = array_fill(0, 8, 0);
        $bit = 63;
        for ($i = 0; $i < strlen($s); ++$i) {
            $c = $charToSymbol(ord($s[$i]));
            if ($bit < 5) {
                $c = $c << 1;
            }
            for ($j = 4; $j >= 0; --$j) {
                if ($bit >= 0) {
                    $a[floor($bit / 8)] |= (($c >> $j) & 1) << ($bit % 8);
                    --$bit;
                }
            }
        }
        $hex = '';
        foreach ($a as $value) {
            $hex .= self::f_uint8($value);
        }
        return $hex;
    }

    public static function f_data($i)
    {
        return self::f_varuint32(strlen($i) / 2) . $i;
    }
}
