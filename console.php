<?php

require_once 'lib/model.php';
require_once 'lib/views/consoleView.php';

if ( !validateInputArgs($argv) ){
    exit();
}

if ($argc == 1) {
    printHelpText();
    exit();
} else {
    switch ( strtolower($argv[1]) ) {
        case 'help':
            printHelpText();
            break;
        case 'list_crypto':
            $crypto_data = getCryptoData();
            listCryptoCurrencies($crypto_data);
            break;
        case 'list_fiat':
            $fiat_data = getFiatData();
            listFiatCurrencies($fiat_data);
            break;
        case 'price':
            if ($argc == 4) {
                $crypto = $argv[2];
                $fiat = $argv[3];
                $exchange_rate = getExchangeRate($crypto, $fiat);
                printExchangeRate($crypto, $fiat, $exchange_rate);
            } else {
                echo "ERROR: Missing arguments 2 (crypto currency) and 3 (fiat currency).";
            }
            break;
        case 'quantity':
            if ($argc == 5) {
                $crypto = strtoupper($argv[2]);
                $fiat = strtoupper($argv[3]);
                $credit = floatval($argv[4]);
                $exchange_rate = floatval( getExchangeRate($crypto, $fiat) );
                $amount_of_coins = calculateAmountOfCoins($credit, $exchange_rate);
                printAmountOfCoins($crypto, $fiat, $credit, $amount_of_coins);
            }
            break;
        default:
            echo "'{$argv[1]}' is not a valid input argument - See Help Text.";
            break;
    }
}
