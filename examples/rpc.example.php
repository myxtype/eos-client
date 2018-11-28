<?php
require_once __DIR__ . '/../vendor/autoload.php';

use xtype\Eos\Client;

$client = new Client('http://api-kylin.eosasia.one');
//
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

// And you can see all rpc
// https://developers.eos.io/eosio-nodeos/v1.4.0/reference

// set version
$client->version(1)->chain()->get_info();
// $client->version('v1')->chain();
// $client->version('v2')->chain();
