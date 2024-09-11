<?php
include('db_connect.php');
include('functions.php');

session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (loginUser($username, $password)) {
        if ($_SESSION['role'] == 'admin') {
            header('Location: ../index.php');
        } else {
            header('Location: ../index.php');
        }
    } else {
        echo "Неправильный логин или пароль.";
    }
}
?>