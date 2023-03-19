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

$exchange_rate = getExchangeRate($crypto, $fiat);
echo sprintf( "%s = %s %s\n", strtoupper($crypto), $exchange_rate, strtoupper($fiat) );
