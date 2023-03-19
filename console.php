<?php

require 'functions.php';

validateInputArgs($argv);

if ($argc == 1) {
    echo "Help Text";
    exit();
} else {
    switch ( strtolower($argv[1]) ) {
        case 'help':
            echo "Help Text";
            break;
        case 'list':
            listCurrencies();
            break;
        case 'price':
            if ($argc == 4) {
                $crypto = $argv[2];
                $fiat = $argv[3];
                $exchange_rate = getExchangeRate($crypto, $fiat);
                echo sprintf( "%s = %s %s\n", strtoupper($crypto), $exchange_rate, strtoupper($fiat) );
            } else {
                echo "ERROR: Missing arguments 2 (crypto currency) and 3 (fiat currency).";
            }
            break;
        default:
            echo "'{$argv[1]}' is not a valid input argument - See Help Text.";
            break;
    }
}
