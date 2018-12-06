# eos-client
eos client offline signature for PHP

针对PHP的EOS RPC客户端，另外提供EOS-ECC方法和离线交易。

# Install

composer.json

```json
{
    "require": {
        "myxtype/eos-client": "dev-master"
    }
}
```

然后`composer update`即可。

> 或者直接 `composer require myxtype/eos-client:dev-master`

# Initialization

```php
use xtype\Eos\Client as EosClient;

$client = new EosClient('http://api-kylin.eosasia.one');
```

GuzzleHttp Options.
```php
$client = new EosClient([
    'base_uri' => 'http://api-kylin.eosasia.one',
    'timeout' => 20,
]);
```

# RPC
You can visit https://developers.eos.io/eosio-nodeos/v1.4.0/reference  View all RPC Method.


- set version（设置版本）
```php
// set version
$client->version(1);
// or
$client->version(1)->chain();
// or
$client->version('v1')->chain();
```

- chain
```php
$chain = $client->chain();
// You can do this
// will visit http://api-kylin.eosasia.one/v1/chain/get_info
var_dump($chain->getInfo());
// or
var_dump($chain->get_info());
// or
var_dump($client->chain()->get_info()->chain_id);
// string(64) "5fff1dae8dc8e2fc4d5b23b2c7665c97f9e9d8edf2b6485a86ba311c25639191"

// get_block
var_dump($chain->getBlock(['block_num_or_id' => 5]));
```

- history
```php
$history = $client->history();
var_dump($history->getTransaction([
    'id' => '5fff1dae8dc8e2fc4d5b23b2c7665c97f9e9d8edf2b6485a86ba311c25639191'
]));
```

- net
```php
$net = $client->net();
var_dump($net->status());
```

- producer
```php
$producer = $client->producer();
```

- wallet
```php
$wallet = $client->wallet();
// Signature transaction if you have opened your wallet RPC
$wallet->sign_transaction([
    'txn' => '',
    'keys' => '',
    'id' => '',
]);
```
- Error（错误信息）

在使用RPC过程中，你可能需要获取错误信息

```php
try {
    // get_block
    var_dump($client->chain()->getBlock(['id' => 5]));
} catch(\Exception $e) {
    var_dump($client->getError());
}
```

如果有错误信息，将返回一个类似下面的对象。

``` php
object(stdClass)#21 (3) {
  ["code"]=>
  int(500)
  ["message"]=>
  string(22) "Internal Service Error"
  ["error"]=>
  object(stdClass)#35 (4) {
    ["code"]=>
    int(3010008)
    ["name"]=>
    string(23) "block_id_type_exception"
    ["what"]=>
    string(16) "Invalid block ID"
    ["details"]=>
    array(1) {
      [0]=>
      object(stdClass)#18 (4) {
        ["message"]=>
        string(78) "Invalid Block number or ID, must be greater than 0 and less than 64 characters"
        ["file"]=>
        string(16) "chain_plugin.cpp"
        ["line_number"]=>
        int(1396)
        ["method"]=>
        string(9) "get_block"
      }
    }
  }
}
```

- notice（说明）

Interface parameters need to be filled in according to the official [EOS document](https://developers.eos.io/eosio-nodeos/v1.4.0/reference). These methods are not fixed. If EOS officially updates the interface, you can modify it directly according to the above, without updating the code of EosClient.

接口参数需要按照[EOS官方文档](https://developers.eos.io/eosio-nodeos/v1.4.0/reference)来填写。这些方法都不是固定的，如果EOS官方更新了接口，你直接按照上面修改就行，不必更新EosClient的代码。

# ECC

https://github.com/EOSIO/eosjs-ecc

- privateToPublic
```php
use xtype\Eos\Ecc;

$privateWif = '5**********';
$public = Ecc::privateToPublic($privateWif);
var_dump($public);
// EOS7nCpUfHCPqAhu2qkTXSPQYmFLAt58gsmdFRtGCD2CNYcnWdRd3
```

- randomKey
```php
use xtype\Eos\Ecc;

// 随机生成私钥
$randomKey = Ecc::randomKey();
var_dump($randomKey);
// 5KBRW5yz1syzQcJCFUmnDeoxBX6JoZ3UpwQk5r6uKKFfGajM8SA
```

- seedPrivate
```php
use xtype\Eos\Ecc;

$privateWif = Ecc::seedPrivate('secret');
var_dump($privateWif);
// 5J9YKiVU3AWNkCa2zfQpj1f2NAeMQhLsYU51N8NM28J1bMnmrEQ
```

- isValidPublic
- isValidPrivate
- sign
- signHash

# Offline transaction
Offline Signature and Transaction

- Send EOS or other（发送代币）
```php
use xtype\Eos\Client;

$client = new Client('http://api-kylin.eosasia.one');
// set your private key
// 在这里设置你的私钥
$client->addPrivateKeys([
    '5JC6gzzaKU4L6dP7AkmRPXJMcYqJxJ8iNB9tNwd2g4VbpRf5CPC'
]);

$tx = $client->transaction([
    'actions' => [
        [
            'account' => 'eosio.token',
            'name' => 'transfer',
            'authorization' => [[
                'actor' => 'xtypextypext',
                'permission' => 'active',
            ]],
            'data' => [
                'from' => 'xtypextypext',
                'to' => 'mysuperpower',
                'quantity' => '0.1000 EOS',
                'memo' => '',
            ],
        ]
    ]
]);
echo "Transaction ID: {$tx->transaction_id}";
// Transaction ID: 15ece6b6f0028e36919f9f208b47ae24233e5ae67a8f15319ad317d3e8be1a2a
```

- Buy Ram（购买内存）
```php
// 你甚至可以使用PHP链式调用对象方方法
$tx = $client->addPrivateKeys(['5JC6gzzaKU4L6dP7AkmRPXJMcYqJxJ8iNB9tNwd2g4VbpRf5CPC'])->transaction([
    'actions' => [
        [
            'account' => 'eosio',
            'name' => 'buyram',
            'authorization' => [[
                'actor' => 'xtypextypext',
                'permission' => 'active',
            ]],
            'data' => [
                'payer' => 'xtypextypext',
                'receiver' => 'mysuperpower',
                'quant' => '0.1000 EOS',
            ],
        ]
    ]
]);
echo "Transaction ID: {$tx->transaction_id}";
// Transaction ID: 15ece6b6f0028e36919f9f208b47ae24233e5ae67a8f15319ad317d3e8be1a2a
```

- delegatebw（抵押来得到CPU和NET）
```php
$tx = $client->addPrivateKeys(['5JC6gzzaKU4L6dP7AkmRPXJMcYqJxJ8iNB9tNwd2g4VbpRf5CPC'])->transaction([
    'actions' => [
        [
            'account' => 'eosio',
            'name' => 'delegatebw',
            'authorization' => [[
                'actor' => 'xtypextypext',
                'permission' => 'active',
            ]],
            'data' => [
                'from' => 'xtypextypext',
                'receiver' => 'mysuperpower',
                'stake_net_quantity' => '0.1000 EOS',
                'stake_cpu_quantity' => '0.1000 EOS',
                'transfer' => false,
            ],
        ]
    ]
]);
echo "Transaction ID: {$tx->transaction_id}";
```

- New Account (新建账户)
```php
// 新建账号
$newAccount = 'ashnbjuihgt1';
// randomKey 随机生成KEY
$activePublicKey = Ecc::privateToPublic(Ecc::randomKey());
$ownerPublicKey = Ecc::privateToPublic(Ecc::randomKey());
var_dump($newAccount, $activePublicKey, $ownerPublicKey);

$tx = $client->addPrivateKeys(['5JC6gzzaKU4L6dP7AkmRPXJMcYqJxJ8iNB9tNwd2g4VbpRf5CPC'])->transaction([
    'actions' => [
        [
            'account' => 'eosio',
            'name' => 'newaccount',
            'authorization' => [[
                'actor' => 'xtypextypext',
                'permission' => 'active',
            ]],
            'data' => [
                'creator' => 'xtypextypext',
                // Main net key is name
                'newact' => $newAccount,
                'owner' => [
                    'threshold' => 1,
                    'keys' => [
                        ['key' => $ownerPublicKey, 'weight' => 1],
                    ],
                    'accounts' => [],
                    'waits' => [],
                ],
                'active' => [
                    'threshold' => 1,
                    'keys' => [
                        ['key' => $activePublicKey, 'weight' => 1],
                    ],
                    'accounts' => [],
                    'waits' => [],
                ],
            ],
        ],
        [
            'account' => 'eosio',
            'name' => 'buyram',
            'authorization' => [[
                'actor' => 'xtypextypext',
                'permission' => 'active',
            ]],
            'data' => [
                'payer' => 'xtypextypext',
                'receiver' => $newAccount,
                'quant' => '0.2500 EOS',
            ],
        ],
        [
            'account' => 'eosio',
            'name' => 'delegatebw',
            'authorization' => [[
                'actor' => 'xtypextypext',
                'permission' => 'active',
            ]],
            'data' => [
                'from' => 'xtypextypext',
                'receiver' => $newAccount,
                'stake_net_quantity' => '0.3000 EOS',
                'stake_cpu_quantity' => '0.2000 EOS',
                'transfer' => false,
            ],
        ]
    ]
]);
echo "Transaction ID: {$tx->transaction_id}";
// string(12) "ashnbjuihgt1"
// string(53) "EOS4uioRoFXsht5ExeD2v53BNWNE3MoEezMLzF6ZbxqzP1hAhmfdp"
// string(53) "EOS8f3Cc8ex7zcgHH2xWcC1QgYQQTbio5DRRoCksteoX4PEnQyA4o"
// Transaction ID: d556e1abbe108e72d3ae2d1b0e1c9e581b95fa21931dee80e77175fd14322ffb
```

- Maintain（维护）

This project has been optimized and maintained. If you have a better plan, I hope you can join this project.

这个项目一直在优化和维护，如果你有更好的方案，希望你能加入这个开源项目。
