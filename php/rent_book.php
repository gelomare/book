<?php
global $conn, $conn;
include('db_connect.php');
include('functions.php');

session_start();
if ($_SESSION['role'] != 'customer') {
    header('Location: ../index.php');
    exit;
}

function render_book_button($user_id, $book_id, $conn)
{
    // Проверяем куплена ли книга
    $stmt = $conn->prepare("SELECT * FROM purchases WHERE user_id = ? AND book_id = ?");
    $stmt->bind_param("ii", $user_id, $book_id);
    $stmt->execute();
    $purchase_result = $stmt->get_result();

    if ($purchase_result->num_rows > 0) {
        echo '<button>Читать</button>';
        return;
    }

    // Проверяем арендована ли книга
    $stmt = $conn->prepare("SELECT * FROM rentals WHERE user_id = ? AND book_id = ? AND rental_end >= CURDATE()");
    $stmt->bind_param("ii", $user_id, $book_id);
    $stmt->execute();
    $rental_result = $stmt->get_result();

    if ($rental_result->num_rows > 0) {
        echo '<button>Читать</button>';
    }
}

// Проверяем, была ли передана книга для аренды
if (isset($_POST['book_id']) && isset($_POST['rental_period'])) {
    $book_id = intval($_POST['book_id']);
    $rental_period_days = intval($_POST['rental_period']);
    $user_id = $_SESSION['user_id'];

    // Проверяем, доступна ли книга для аренды
    $stmt = $conn->prepare("SELECT stock FROM books WHERE id=?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $book = $result->fetch_assoc();
        if ($book['stock'] > 0) {
            // Определяем даты начала и окончания аренды
            $rental_start = date('Y-m-d');
            $rental_end = date('Y-m-d', strtotime("+$rental_period_days days"));

            // Вставляем данные об аренде в таблицу rentals
            $stmt = $conn->prepare("INSERT INTO rentals (user_id, book_id, rental_start, rental_end) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $user_id, $book_id, $rental_start, $rental_end);
            if ($stmt->execute()) {
                // Обновляем количество доступных книг
                $stmt = $conn->prepare("UPDATE books SET stock = stock - 1 WHERE id=?");
                $stmt->bind_param("i", $book_id);
                $stmt->execute();
                header('Location: customer_dashboard.php');
                exit;
            } else {
                echo "Произошла ошибка при добавлении аренды. Попробуйте снова.";
            }
        } else {
            echo "Извините, этой книги нет в наличии.";
        }
    } else {
        echo "Книга не найдена.";
    }
} else {
    echo "Неверные данные.";
}

?>