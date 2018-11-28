<?php
require_once __DIR__ . '/../vendor/autoload.php';

use xtype\Eos\Client;

$client = new Client('http://api-kylin.eosasia.one');
//
// 1. set your private key
$client->addPrivateKeys([
    '5JC6gzzaKU4L6dP7AkmRPXJMcYqJxJ8iNB9tNwd2g4VbpRf5CPC'
]);

// 2. build your transaction
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
