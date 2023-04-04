<?php

require_once 'lib/model.php';
require_once 'lib/views/consoleView.php';

$view = new ConsoleView();
$model = new Model();

if ( !$model->validateInputArgs($argv) ){
    exit();
}

if ($argc == 1) {
    $view->printHelpText();
    exit();
} else {
    switch ( strtolower($argv[1]) ) {
        case 'help':
            $view->printHelpText();
            break;
        case 'list_crypto':
            $crypto_data = $model->getCryptoData();
            $view->listCryptoCurrencies($crypto_data);
            break;
        case 'list_fiat':
            $fiat_data = $model->getFiatData();
            $view->listFiatCurrencies($fiat_data);
            break;
        case 'price':
            if ($argc == 4) {
                $crypto = $argv[2];
                $fiat = $argv[3];

                if ( $model->fiatListed($fiat) && $model->cryptoListed($crypto) ) {
                    $exchange_rate = $model->getExchangeRate($crypto, $fiat);
                    $view->printExchangeRate($crypto, $fiat, $exchange_rate);
                } else {
                    echo "ERROR: Crypto and/or Fiat currency is not listed.";
                }
            } else {
                echo "ERROR: Missing arguments 2 (crypto currency) and 3 (fiat currency).";
            }
            break;
        case 'quantity':
            if ($argc == 5) {
                $crypto = strtoupper($argv[2]);
                $fiat = strtoupper($argv[3]);
                $credit = floatval($argv[4]);
                $exchange_rate = floatval( $model->getExchangeRate($crypto, $fiat) );
                $amount_of_coins = $model->calculateAmountOfCoins($credit, $exchange_rate);
                $view->printAmountOfCoins($crypto, $fiat, $credit, $amount_of_coins);
            }
            break;
        default:
            echo "'{$argv[1]}' is not a valid input argument - See Help Text.";
            break;
    }
}
