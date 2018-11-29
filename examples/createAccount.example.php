<?php
require_once __DIR__ . '/../vendor/autoload.php';

use xtype\Eos\Client;
use xtype\Eos\Ecc;

$client = new Client('http://api-kylin.eosasia.one');

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
