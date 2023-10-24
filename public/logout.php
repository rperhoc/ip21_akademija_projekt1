<?php

require_once __DIR__ . '/../setup.php';

$_SESSION = array();
session_destroy();
header('Location: ' . 'index.php');