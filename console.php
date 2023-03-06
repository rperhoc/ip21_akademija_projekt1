<?php

if ($argc == 1 or strtolower($argv[1]) == 'help') {
    echo "Help Text";
    exit();
} elseif ($argc == 3) {
    $crypto = $argv[1];
    $fiat = $argv[2];
} else {
    echo "Invalid number of arguments.";
    exit();
}

$api_url = "https://api.coinbase.com/v2/prices/{$crypto}-{$fiat}/spot";
$data_json = file_get_contents($api_url);
$data_array = json_decode($data_json, true);

echo "{$data_array['data']['base']} = {$data_array['data']['amount']} {$data_array['data']['currency']}\n";

?>
