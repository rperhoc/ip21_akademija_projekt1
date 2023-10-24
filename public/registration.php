<?php

require_once __DIR__ . '/../setup.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET)) {
    if (!$model->verifyParameters($_GET)) {
        header('Location: ' . 'index.php');
    } else {
        if ($_GET['error'] === 'email_taken') {
            $email_error = 'Email is already registered.';
        } elseif ($_GET['error'] === 'invalid_email') {
            $email_error = 'Submitted email address is not valid.';
        } elseif ($_GET['error'] === 'invalid_password') {
            $password_error = 'Submitted password is invalid.';
        } 
    }       
}

echo $registration->render([
    'password_error' => $password_error ?? null,
    'email_error' => $email_error ?? null,
]);
