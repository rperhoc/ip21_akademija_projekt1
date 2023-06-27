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

$date = date('d.m.Y');
$time = date('H:i:s');

if ( isset($_GET['crypto_currency']) && isset($_GET['fiat_currency']) ) {
    $selected_crypto = trim($_GET['crypto_currency']);
    $selected_fiat = trim($_GET['fiat_currency']);
}

try {
    $crypto_data = $model->getCryptoData();
    $fiat_data = $model->getFiatData();
} catch (Exception $e) {
    $error_message = $e->getMessage();
    echo $error_page->render(['message' => $error_message]);
    exit();
}

try {
    $exchange_rate = $model->getExchangeRate($selected_crypto, $selected_fiat);
    echo $show_price->render([
        'crypto_data' => $crypto_data,
        'fiat_data' => $fiat_data,
        'exchange_rate' => $exchange_rate,
        'crypto' => $selected_crypto,
        'fiat' => $selected_fiat,
        'date' => $date,
        'time' => $time
    ]);
} catch (Exception $e) {
    echo "napaka\n";
    echo $e->getMessage() . "\n";
}
