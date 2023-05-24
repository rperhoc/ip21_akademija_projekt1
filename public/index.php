<?php

$main_folder = dirname(__DIR__);
require_once $main_folder . '\vendor1\autoload.php';
require_once $main_folder . '\lib\model.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$loader = new FilesystemLoader($main_folder . '\lib\views\web');
$twig = new Environment($loader);
$model = new Model();

$error_page = $twig->load('printErrorMessage.twig');
$template = $twig->load('list.html.twig');

try {
    $crypto_data = $model->getCryptoData();
    $fiat_data = $model->getFiatData();
} catch (Exception $e) {
    $error_message = $e->getMessage();
    echo $error_page->render(['message' => $error_message]);
    exit();
}

if ( isset($crypto_data) && isset($fiat_data) ) {
    echo $template->render([
        'crypto_data' => $crypto_data,
        'fiat_data' => $fiat_data]);
    }
