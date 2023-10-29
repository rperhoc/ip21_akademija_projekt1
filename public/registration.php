<?php

require_once __DIR__ . '/../setup.php';

if ( !empty($_GET)) {
    if (!$model->verifyParameters($_GET)) {
        header('Location: ' . 'index.php');
    } else {
        if ($_GET['error'] === 'email_taken') {
            $is_email_registered = true;
        } elseif ($_GET['error'] === 'invalid_email') {
            $is_email_valid = false;
        } elseif ($_GET['error'] === 'invalid_password') {
            $is_password_valid = false;
        } 
    }       
}

echo $registration->render([
    'is_email_registered' => $is_email_registered ?? false,
    'is_email_valid' => $is_email_valid ?? true, 
    'is_password_valid' => $is_password_valid ?? true
]);
