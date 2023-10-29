<?php

require_once __DIR__ . '/setup.php';

if ( !$model->validateInputArgs($argv) ){
   exit();
}

if ($argc == 1) {
    $view->printHelpText();
    exit();
}
switch ( strtolower($argv[1]) ) {
    case 'help':
        echo "Help text\n";
        break;
    case 'list_crypto':
        try {
            $model->listCryptoCurrencies();
        } catch  (Exception $e) {
            echo $e->getMessage();
            exit();
        }
        // Get entered values from user input
        if ( $model->markFavourites() ) {
            echo "Enter number before the currency you wish to favourite: \n";
            $user_input = trim( fgets(STDIN) );
            $entered_values = $model->getEnteredValues($user_input);
        } else {
            exit();
        }
        // Store currencies in array and insert into favourites table
        try {
            $favourites = $model->saveFavourites($model->getCryptoData(), $entered_values); 
            foreach ($favourites as $key => $value) {
                $model->insertIntoFavourites($value, 'crypto');
            }
         } catch (Exception $e) {
                echo $e->getMessage() . "\n";
            }        
        break;
    case 'list_fiat':
        try {
            $model->listFiatCurrencies();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        break;
    case 'list_favourites':
        if ($argc == 3 && filter_var($argv[2], FILTER_VALIDATE_INT) !== false) {
            $user_id = $argv[2];
        }
        $model->selectFromFavourites($user_id ?? 0);
        break;
    case 'price':
        if ($argc == 4) {
            $crypto = $argv[2];
            $fiat = $argv[3];
            try {
                $exchange_rate = $model->getExchangeRate($crypto, $fiat);
                echo "{$crypto} = {$exchange_rate} {$fiat}\n";
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        } else {
            echo "Error: Missing arguments.\n";
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
                echo "{$credit} {$fiat} = {$amount_of_coins} {$crypto}\n";
            } catch (Exception $e) {
                echo $e->getMessage . "\n";
            }
        }
        break;
    case 'add_user':
        if ($argc == 4) {
            $model->addUser($argv[2], $argv[3]);
        } else {
            exit();
        }
        break;
    default: 
        $error_message = "'{$argv[1]}' is not a valid input argument - See Help Text.";
        echo $error_message;   
        break; 
}