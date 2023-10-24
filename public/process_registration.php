<?php

require_once __DIR__ . '/../setup.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!$model->isUserRegistered($email)) {
        if (!$model->isEmailValid($email) || !$model->isPasswordValid($password)) {
            if (!$model->isEmailValid($email)) {
                header('Location: ' . '/registration.php?error=invalid_email');
            } elseif (!$model->isPasswordValid($password)) {
                header('Location: ' . '/registration.php?error=invalid_password');
            } 
        } else {                
                $model->addUser($email, $password);
                header('Location: ' . '/index.php');
            }
    } else {
        header('Location: ' . '/registration.php?error=email_taken');
    }
} else {
    header('Location: ' . '/index.php');
}
