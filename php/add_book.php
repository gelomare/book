<?php
include('db_connect.php');
include('functions.php');

// Константы для директорий и максимального размера файла
define('COVERS_DIR', '../covers/');
define('BOOKS_DIR', '../books/');
define('MAX_FILE_SIZE', 8000000); // Максимальный размер файла 8 МБ

/**
 * Функция для загрузки файла
 *
 * @param array $file Массив с информацией о загружаемом файле
 * @param array $allowedTypes Допустимые типы файлов
 * @param string $targetDir Директория для сохранения файла
 * @return array Результат загрузки
 */
function uploadFile($file, $allowedTypes, $targetDir)
{
    // Проверка на наличие ошибок загрузки файла
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ["error" => "Ошибка загрузки файла: " . $file['error']];
    }

    $targetFile = $targetDir . basename($file["name"]);
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Проверка допустимых форматов файла
    if (!in_array($fileType, $allowedTypes)) {
        return ["error" => "Недопустимый формат файла: $fileType"];
    }

    // Проверка размера файла
    if ($file["size"] > MAX_FILE_SIZE) {
        return ["error" => "Файл слишком большой. Максимальный размер: " . (MAX_FILE_SIZE / 1000000) . " МБ"];
    }

    // Попытка загрузки файла
    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return ["path" => $targetFile];
    }
    return ["error" => "Ошибка при загрузке файла"];
}

/**
 * Функция добавления книги в базу данных
 *
 * @param string $title Заголовок книги
 * @param string $author Автор книги
 * @param array $filePath Массив с данными о файле книги
 * @param array $coverImage Массив с данными об обложке
 * @param int $year Год издания
 * @param string $category Категория книги
 * @param float $price Цена книги
 * @param int $stock Количество на складе
 * @return array Результат добавления книги
 */
function addBook($title, $author, $filePath, $coverImage, $year, $category, $price, $stock)
{
    global $conn;

    // Загружаем обложку
    $coverUploadResult = uploadFile($coverImage, ["jpg", "jpeg", "png", "gif"], COVERS_DIR);
    if (isset($coverUploadResult["error"])) {
        return ["error" => "Ошибка загрузки обложки: " . $coverUploadResult["error"]];
    }

    // Загружаем файл книги
    $bookFileUploadResult = uploadFile($filePath, ["epub", "fb2"], BOOKS_DIR);
    if (isset($bookFileUploadResult["error"])) {
        return ["error" => "Ошибка загрузки книги: " . $bookFileUploadResult["error"]];
    }

    // Подготовка и выполнение SQL запроса
    $sql = "INSERT INTO books (title, author, year, category, price, stock, file_path, cover) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return ["error" => "Ошибка подготовки запроса: " . $conn->error];
    }

    $stmt->bind_param("ssisiiss", $title, $author, $year, $category, $price, $stock, $bookFileUploadResult["path"], $coverUploadResult["path"]);
    if ($stmt->execute()) {
        return ["success" => "Книга успешно добавлена"];
    }
    return ["error" => "Ошибка выполнения запроса: " . $stmt->error];
}

// Обработка данных из формы
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Проверка наличия полей формы
    $requiredFields = ['title', 'author', 'year', 'category', 'price', 'stock', 'file_path', 'cover_image'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field]) && !isset($_FILES[$field])) {
            echo "Ошибка: Поле $field обязательно для заполнения.";
            exit;
        }
    }

    // Извлечение данных из формы
    $title = trim($_POST["title"]);
    $author = trim($_POST["author"]);
    $year = intval($_POST["year"]); // Приведение к целому числу
    $category = trim($_POST["category"]);
    $price = floatval($_POST["price"]); // Приведение к числу с плавающей точкой
    $stock = intval($_POST["stock"]); // Приведение к целому числу
    $filePath = $_FILES["file_path"];
    $coverImage = $_FILES["cover_image"];

    // Добавление книги в базу данных
    $result = addBook($title, $author, $filePath, $coverImage, $year, $category, $price, $stock);

    // Вывод результата
    if (isset($result["error"])) {
        echo "Ошибка: " . $result["error"];
    } else {
        header('Location: admin_dashboard.php');
        exit; // Завершаем скрипт после перенаправления
    }
}
?>