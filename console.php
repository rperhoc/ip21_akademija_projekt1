<?php

$btc_json = file_get_contents('https://api.coinbase.com/v2/prices/BTC-USD/spot');
$btc_array = json_decode($btc_json, true);

echo $btc_array['data']['base'] . ": {$btc_array['data']['currency']} " . $btc_array['data']['amount'];
