<?php

require 'functions.php';

validateInputArgs($argv);

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

$api_url = getRateUrl($crypto, $fiat);
if ( responseValid($api_url) ) {
    $data_array = getApiData($api_url);
    $base = $data_array['data']['base'];
    $amount = $data_array['data']['amount'];
    $currency = $data_array['data']['currency'];
    echo sprintf("%s = %s %s", $base, $amount, $currency);
} else {
    echo "Could not retrieve data from API. Make sure you entered valid crypto and fiat currencies.\n";
    exit();
}
