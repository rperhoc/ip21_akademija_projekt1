<?php

$btc_json = file_get_contents('https://api.coinbase.com/v2/prices/BTC-USD/spot');
$btc_array = json_decode($btc_json, true);

echo "Currency: " . $btc_array['data']['base'] . "\n";
echo "USD value: " . $btc_array['data']['amount'] . "\n";
