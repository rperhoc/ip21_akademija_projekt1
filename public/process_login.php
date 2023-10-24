<?php

require_once __DIR__ . '/../setup.php';

$max_login_attempts = 5;
$login_timeout = 600;

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 1;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    if ($_SESSION['login_attempts'] <= $max_login_attempts) {
        if (!$model->isUserRegistered($email)) {
            header('Location: ' . '/login.php?error=invalid_email');
        } else {
            if (!$model->verifyLogin($email, $password)) {
                $_SESSION['login_attempts'] += 1;
                header('Location: ' . '/login.php?error=invalid_password');
            } else {
                $_SESSION['user_id'] = $model->getUserId($email);
                $_SESSION['logged_in_as'] = $email;
                unset($_SESSION['login_attempts']);
                header('Location: ' . '/index.php');
            }
        } 
    } else {
        if (time() - $_SESSION['last_login_attempt'] >= $login_timeout) {
            $_SESSION['login_attempts'] = 1;
            header('Location: ' . '/login.php');
        } else {
            echo "<p>Exceeded login attempts</p>";
        }
    }   
} else {
    header('Location: ' . '/index.php');
}
