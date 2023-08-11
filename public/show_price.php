<?php

$main_folder = dirname(__DIR__);

require_once $main_folder . '/vendor1/autoload.php';
require_once $main_folder . '/lib/model.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$loader = new FilesystemLoader($main_folder . '/lib/views/web');
$twig = new Environment($loader);
$model = new Model();

$show_price = $twig->load('show_price.twig');
$index = $twig->load('index.html.twig');
$error_page = $twig->load('print_error_message.twig');

$date = date('d.m.Y');
$time = date('H:i:s');

$crypto_star_button = 'star-button-empty.png';
$fiat_star_button = 'star-button-empty.png';
$is_crypto_favourite = false;
$is_fiat_favourite = false;

try {
    $server = "172.18.0.3";
    $dbname = "root";
    $db = $model->pdoConnect($server, $dbname);
    $crypto_data = $model->getCryptoData();
    $fiat_data = $model->getFiatData();
} catch (Exception $e) {
    $error_message = $e->getMessage();
    echo $error_page->render(['message' => $error_message]);
}

$crypto_favourites = $model->getFavouritesArray($db, $crypto_data, 'crypto');
$fiat_favourites = $model->getFavouritesArray($db, $fiat_data, 'fiat');
$crypto_data_rearranged = $model->pushFavouritesOnTop($crypto_data, $crypto_favourites);
$fiat_data_rearranged = $model->pushFavouritesOnTop($fiat_data, $fiat_favourites);

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $selected_crypto = trim($_GET['crypto']);
        $selected_fiat = trim($_GET['fiat']);

        if ( $model->isCurrencyFavourite($db, $selected_crypto) ) {
            $crypto_star_button = 'star-button-coloured.png';
            $is_crypto_favourite = true;
        }
        if ( $model->isCurrencyFavourite($db, $selected_fiat) ) {
            $fiat_star_button = 'star-button-coloured.png';
            $is_fiat_favourite = true;
        }

        try {
            $exchange_rate = $model->getExchangeRate($selected_crypto, $selected_fiat);
            echo $show_price->render([
                'crypto_data' => $crypto_data_rearranged,
                'crypto_favourites' => $crypto_favourites,
                'fiat_data' => $fiat_data_rearranged,
                'fiat_favourites' => $fiat_favourites,
                'exchange_rate' => $exchange_rate,
                'crypto' => $selected_crypto,
                'fiat' => $selected_fiat,
                'date' => $date,
                'time' => $time,
                'getParams' => $_GET,
                'crypto_star_button' => $crypto_star_button,
                'fiat_star_button' => $fiat_star_button,
                'is_crypto_favourite' => $is_crypto_favourite,
                'is_fiat_favourite' => $is_fiat_favourite
            ]);
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            echo $error_page->render(['message' => $error_message]);
        }
        break;
    case 'POST':
        $selected_crypto = trim($_POST['crypto']);
        $selected_fiat = trim($_POST['fiat']);

        if ( $model->isCurrencyFavourite($db, $selected_crypto) ) {
            $crypto_star_button = 'star-button-coloured.png';
            $is_crypto_favourite = true;
        } else {
            $crypto_star_button = 'star-button-empty.png';
            $is_crypto_favourite = false;
        }
        if ( $model->isCurrencyFavourite($db, $selected_fiat) ) {
            $fiat_star_button = 'star-button-coloured.png';
            $is_fiat_favourite = true;
        } else {
            $fiat_star_button = 'star-button-empty.png';
            $is_fiat_favourite = false;
        }

        if ( isset($_POST['favourite_add_crypto']) ) {
            $crypto_currency = $model->getCurrencyFromName($selected_crypto, 'crypto');
            $model->insertIntoFavourites($db, $crypto_currency, 'crypto');
            $crypto_star_button = 'star-button-coloured.png';
        } elseif ( isset($_POST['favourite_remove_crypto']) ) {
            $model->removeFromFavourites($db, $selected_crypto);
            $crypto_star_button = 'star-button-empty.png';
            $is_fiat_favourite = false;
        } elseif ( isset($_POST['favourite_add_fiat']) ) {
            $fiat_currency = $model->getCurrencyFromName($selected_fiat, 'fiat');
            $model->insertIntoFavourites($db, $fiat_currency, 'fiat');
            $fiat_star_button = 'star-button-coloured.png';
        } elseif ( isset($_POST['favourite_remove_fiat']) ) {
            $model->removeFromFavourites($db, $selected_fiat);
            $fiat_star_button = 'star-button-empty.png';
            $is_fiat_favourite = false;
        }

        try {
            $exchange_rate = $model->getExchangeRate($selected_crypto, $selected_fiat);
            echo $show_price->render([
                'crypto_data' => $crypto_data_rearranged,
                'crypto_favourites' => $crypto_favourites,
                'fiat_data' => $fiat_data_rearranged,
                'fiat_favourites' => $fiat_favourites,
                'exchange_rate' => $exchange_rate,
                'crypto' => $selected_crypto,
                'fiat' => $selected_fiat,
                'date' => $date,
                'time' => $time,
                'getParams' => $_GET,
                'crypto_star_button' => $crypto_star_button,
                'fiat_star_button' => $fiat_star_button,
                'is_crypto_favourite' => $is_crypto_favourite,
                'is_fiat_favourite' => $is_fiat_favourite
            ]);
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            echo $error_page->render(['message' => $error_message]);
        }

        break;
    default:
        $error_message = "Oops! Something went wrong";
        echo $error_page->render(['message' => $error_message]);
}
