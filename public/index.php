<?php

$main_folder = dirname(__DIR__);

require_once $main_folder . '/vendor1/autoload.php';
require_once $main_folder . '/lib/model.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$loader = new FilesystemLoader($main_folder . '/lib/views/web');
$twig = new Environment($loader);
$model = new Model();

$index = $twig->load('index.html.twig');
$error_page = $twig->load('print_error_message.twig');

$crypto_star_button = 'star-button-empty.png';
$fiat_star_button = 'star-button-empty.png';

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

echo $index->render([
    'crypto_data' => $crypto_data_rearranged,
    'fiat_data' => $fiat_data_rearranged,
    'getParams' => $_GET,
    'crypto' => '',
    'fiat' => '',
    'crypto_star_button' => $crypto_star_button,
    'fiat_star_button' => $fiat_star_button
]);
