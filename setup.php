<?php

session_start();

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/lib/model.php';

use Dotenv\Dotenv;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$loader = new FilesystemLoader(__DIR__ . '/lib/views/web');
$twig = new Environment($loader);
$model = new Model();

$index = $twig->load('index.html.twig');
$show_price = $twig->load('show_price.twig');
$login = $twig->load('login.twig');
$registration = $twig->load('registration.twig');
$error = $twig->load('error_page.twig');

$user_id = $model->setUserId();

