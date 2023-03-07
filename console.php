<?php

require 'functions.php';
$ARGUMENT_LENGTH = 10;

for ($i = 1; $i < $argc; $i++) {
    if ( !argLenValid($argv[$i], $ARGUMENT_LENGTH) ) {
        echo "ERROR: The maximum length of input arguments is {$ARGUMENT_LENGTH}.";
        exit();
    }
}

if ($argc == 1 || strtolower($argv[1]) == 'help') {
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
if ( urlValid($api_url) ) {
    $data_json = file_get_contents($api_url);
    $data_array = json_decode($data_json, true);
    echo "{$data_array['data']['base']} = {$data_array['data']['amount']} {$data_array['data']['currency']}\n";
} else {
    echo "Could not retrieve data from API. Make sure you entered valid crypto and fiat currencies.";
    exit();
}
