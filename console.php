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
            $api_data = $model->getCryptoData();
            if ( is_string($api_data) ) {
                $view->printErrorMessage($api_data);
            } else {
                $view->printCryptoCurrencies($api_data);
            }
            break;
        case 'list_fiat':
            $api_data = $model->getFiatData();
            if ( is_string($api_data) ) {
                $view->printErrorMessage($api_data);
            } else {
                $view->printFiatCurrencies($api_data);
            }
            break;
        case 'price':
            if ($argc == 4) {
                $crypto = $argv[2];
                $fiat = $argv[3];

                $is_fiat_listed = $model->isFiatListed($fiat);
                $is_crypto_listed = $model->isCryptoListed($crypto);
                if ($is_fiat_listed && $is_crypto_listed) {
                    $exchange_rate = $model->getExchangeRate($crypto, $fiat);
                    $view->printExchangeRate($crypto, $fiat, $exchange_rate);
                } else {
                    $error_message = "";
                    if (!$is_crypto_listed) {
                        $error_message .= "\n$crypto is not a listed Crypto currency.";
                    }
                    if (!$is_fiat_listed) {
                        $error_message .= "\n$fiat is not a listed Fiat currency.";
                    }
                    $view->printErrorMessage($error_message);
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
