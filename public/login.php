<?php

require_once __DIR__ . '/../setup.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET)) {
    if (!$model->verifyParameters($_GET)) {
        header('Location: ' . 'index.php');
    } else {
        if ($_GET['error'] === 'invalid_email') {
            $is_email_registered = false;
        } elseif ($_GET['error'] === 'invalid_password') {
            $is_password_correct = false;
        }  
    }       
}

echo $login->render([
    'is_email_registered' => $is_email_registered ?? true,
    'is_password_correct' => $is_password_correct ?? true    
]);
