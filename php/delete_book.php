<?php
global $conn;
include('db_connect.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM books WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header('Location: admin_dashboard.php');
}
?>