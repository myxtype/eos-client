<?php
require_once __DIR__ . '/../vendor/autoload.php';

use xtype\Eos\Ecc;

// 随机生成私钥
$randomKey = Ecc::randomKey();
var_dump($randomKey);

// 将wif 私钥转为公钥
$public = Ecc::privateToPublic($randomKey);
var_dump($public);

// 用字符串种子生产私钥
$privateWif = Ecc::seedPrivate('secret');
var_dump($privateWif);
