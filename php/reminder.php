<?php
global $conn;
include('db_connect.php');

$query = "SELECT rentals.*, users.email, books.title FROM rentals 
          JOIN users ON rentals.user_id = users.id 
          JOIN books ON rentals.book_id = books.id 
          WHERE rental_end = CURDATE() + INTERVAL 1 DAY";

$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $to = $row['email'];
    $subject = "Rental Period Ending Soon";
    $body = "Dear user, your rental period for the book " . $row['title'] . " is ending soon. Please return/renew it.";
    $headers = "From: bookstore@example.com";

    mail($to, $subject, $body, $headers);
}
?>