<?php
require_once __DIR__ . '/../vendor/autoload.php';

use xtype\Eos\Client;

$client = new Client('http://api-kylin.eosasia.one');
// 购买内存 Buy Ram
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
