<?php
require_once __DIR__ . '/../vendor/autoload.php';

use xtype\Eos\Client;

$client = new Client('http://api-kylin.eosasia.one');
// stake
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
