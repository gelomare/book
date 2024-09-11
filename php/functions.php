<?php
include('db_connect.php');

/**
 * Получение списка книг с возможностью фильтрации
 *
 * @param string|null $category Категория книги
 * @param string|null $author Автор книги
 * @param int|null $year Год издания
 * @return array Список книг
 */
function getBooks($category = null, $author = null, $year = null)
{
    global $conn;

    // Создаем базовый SQL-запрос
    $query = "SELECT * FROM books WHERE 1=1";
    $params = [];
    $types = "";

    // Добавляем фильтры
    if ($category) {
        $query .= " AND category=?";
        $params[] = $category;
        $types .= "s";
    }
    if ($author) {
        $query .= " AND author=?";
        $params[] = $author;
        $types .= "s";
    }
    if ($year) {
        $query .= " AND year=?";
        $params[] = $year;
        $types .= "i"; // Год - это целое число
    }

    // Подготовка и выполнение запроса
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Failed to prepare statement for getting books: " . $conn->error);
        return [];
    }

    // Связываем параметры, если есть
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $books = $result->fetch_all(MYSQLI_ASSOC); // Получаем все записи в виде ассоциативного массива
    $stmt->close();

    return $books;
}

/**
 * Регистрация нового пользователя
 *
 * @param string $username Имя пользователя
 * @param string $email Email пользователя
 * @param string $password Пароль пользователя
 * @param string $role Роль пользователя
 * @return bool Результат операции
 */
function registerUser($username, $email, $password, $role)
{
    global $conn;

    // Проверка обязательных полей
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        return false;
    }

    // Хешируем пароль
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    if ($stmt === false) {
        error_log("Failed to prepare statement for registration: " . $conn->error);
        return false;
    }

    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $role);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

/**
 * Авторизация пользователя
 *
 * @param string $username Имя пользователя
 * @param string $password Пароль пользователя
 * @return bool Результат операции
 */
function loginUser($username, $password)
{
    global $conn;

    // Проверка обязательных полей
    if (empty($username) || empty($password)) {
        return false;
    }

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    if ($stmt === false) {
        error_log("Failed to prepare statement for login: " . $conn->error);
        return false;
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            $stmt->close();
            return true;
        }
    }

    $stmt->close();
    return false;
}

/**
 * Выход пользователя из системы
 */
function logoutUser()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]);
    }

    session_destroy();
}

?>