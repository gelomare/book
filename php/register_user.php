<?php

include('db_connect.php');
include('functions.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = 'customer';

    if (registerUser($username, $email, $password, $role)) {
        header('Location: ../login.php');
    } else {
        echo "Ошибка регистрации. Попробуйте снова.";
    }
}
