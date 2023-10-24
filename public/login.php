<?php

require_once __DIR__ . '/../setup.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET)) {
    if (!$model->verifyParameters($_GET)) {
        header('Location: ' . 'index.php');
    } else {
        if ($_GET['error'] === 'invalid_email') {
            $email_error = 'Submitted email address is not registered.';
        } elseif ($_GET['error'] === 'invalid_password') {
            $password_error = 'Submitted password is invalid.';
        }  
    }       
}

echo $login->render([
    'email_error' => $email_error ?? null,
    'password_error' => $password_error ?? null
]);
