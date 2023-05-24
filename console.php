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
}
switch ( strtolower($argv[1]) ) {
    case 'help':
        $view->printHelpText();
        break;
    case 'list_crypto':
        try {
            $api_data = $model->getCryptoData();
            $view->printCryptoCurrencies($api_data);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        break;
    case 'list_fiat':
        try {
            $api_data = $model->getFiatData();
            $view->printFiatCurrencies($api_data);
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            $view->printErrorMessage($error_message);
        }
        break;
    case 'price':
        if ($argc == 4) {
            $crypto = $argv[2];
            $fiat = $argv[3];
            try {
                $exchange_rate = $model->getExchangeRate($crypto, $fiat);
                $view->printExchangeRate($crypto, $fiat, $exchange_rate)
            } catch (Exception $e) {
                $error_message = $e->getMessage();
                $view->printErrorMessage($error_message);
            }
        } else {
            $error_message = "ERROR: Missing arguments.";
            $view->printErrorMessage($error_message);
        }
        break;
    case 'quantity':
        if ($argc == 5) {
            $crypto = strtoupper($argv[2]);
            $fiat = strtoupper($argv[3]);
            $credit = floatval($argv[4]);
            try {
                $exchange_rate = (float) $model->getExchangeRate($crypto, $fiat);
                $amount_of_coins = $model->calculateAmountOfCoins($credit, $exchange_rate);
                $view->printAmountOfCoins($crypto, $fiat, $credit, $amount_of_coins);
            } catch (Exception $e) {
                $error_message = $e->getMessage();
                $view->printErrorMessage($error_message);
            }
        }
        break;
    default:
        $error_message = "'{$argv[1]}' is not a valid input argument - See Help Text.";
        $view->printErrorMessage($error_message);
        break;
}
