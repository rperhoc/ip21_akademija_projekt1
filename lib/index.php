<?php

require_once 'C:\programiranje\projekt 1\ip21_akademija_projekt1\vendor\autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$loader = new FilesystemLoader('C:\programiranje\projekt 1\ip21_akademija_projekt1\lib\views\console');
$twig = new Environment($loader);

require_once 'C:\programiranje\projekt 1\ip21_akademija_projekt1\lib\model.php';
$model = new Model();

$crypto_data = $model->getCryptoData();
$fiat_data = $model->getFiatData();
$template = $twig->load('list.html.twig');
echo $template->render([
    'crypto_data' => $crypto_data,
    'fiat_data' => $fiat_data]);
