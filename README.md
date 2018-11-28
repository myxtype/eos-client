# eos-client
eos client offline signature for PHP

针对PHP的EOS RPC客户端，另外提供EOS-ECC方法和离线交易。

# Install

composer.json

```
{
    "require": {
        "myxtype/ethereum-client": "dev-master"
    }
}
```

> 或者 `composer require myxtype/ethereum-client`

然后`composer update`即可。

# Initialization

```
use xtype\Eos\Client as EosClient;

$client = new EosClient('http://api-kylin.eosasia.one');

```

GuzzleHttp Options.
```
$client = new EosClient([
    'base_uri' => 'http://api-kylin.eosasia.one',
    'timeout' => 20,
]);
```

# RPC
You can visit https://developers.eos.io/eosio-nodeos/v1.4.0/reference  View all RPC Method.
```
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

# ECC

- privateToPublic
```
use xtype\Eos\Ecc;

$privateWif = '5**********';
$public = Ecc::privateToPublic($privateWif);
var_dump($public);
// EOS7nCpUfHCPqAhu2qkTXSPQYmFLAt58gsmdFRtGCD2CNYcnWdRd3
```

- randomKey
```
use xtype\Eos\Ecc;

// 随机生成私钥
$randomKey = Ecc::randomKey();
var_dump($randomKey);
// 5KBRW5yz1syzQcJCFUmnDeoxBX6JoZ3UpwQk5r6uKKFfGajM8SA
```

- seedPrivate
```
use xtype\Eos\Ecc;

$privateWif = Ecc::seedPrivate('secret')
var_dump($privateWif);
// 5J9YKiVU3AWNkCa2zfQpj1f2NAeMQhLsYU51N8NM28J1bMnmrEQ
```

- isValidPublic
- isValidPrivate
- sign
- signHash

# transtion
Offline Signature and Transaction

```
use xtype\Eos\Client;

$client = new Client('http://api-kylin.eosasia.one');
//
// 1. set your private key
$client->addPrivateKeys([
    '5JC6gzzaKU4L6dP7AkmRPXJMcYqJxJ8iNB9tNwd2g4VbpRf5CPC'
]);

// 2. build your transtion
$tx = $client->transtion([
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
