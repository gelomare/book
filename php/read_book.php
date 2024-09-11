<?php
global $conn;
session_start();
include('db_connect.php');
include('functions.php');
include 'fb2parser.php';

// Проверка роли пользователя
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'customer' && $_SESSION['role'] !== 'admin')) {
    header('Location: ../login.php');
    exit;
}

/**
 * Получение данных о файле книги
 *
 * @param mysqli $conn Соединение с базой данных
 * @param int $fileId ID книги
 * @return array|null Данные о книге или null
 */
function getFileData($conn, $fileId)
{
    $stmt = $conn->prepare("SELECT * FROM bookstore_db.books WHERE id = ?");
    $stmt->bind_param("i", $fileId);
    $stmt->execute();
    $result = $stmt->get_result();
    $file = $result->fetch_assoc();
    $stmt->close();
    return $file;
}

// Основная логика обработки запроса
$content = '';
if (isset($_GET['id'])) {
    $fileId = intval($_GET['id']);
    $file = getFileData($conn, $fileId);

    if ($file) {
        $filepath = $file['file_path'];
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));

        // Парсинг файла в зависимости от формата
        if ($extension === 'fb2') {
            $content = parseFB2File($filepath);
        } elseif ($extension === 'epub') {
            $content = parseEPUBFile($filepath);
        } else {
            echo "Недопустимый формат файла.";
            exit;
        }

        if ($content === false) {
            echo "Ошибка чтения файла.";
            exit;
        }
    } else {
        echo "Файл не найден.";
        exit;
    }
} else {
    echo "ID файла не указан.";
    exit;
}
?>

	<!DOCTYPE html>
	<html lang="ru">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Чтение книги - Книжный магазин</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
		      crossorigin="anonymous">
		<link rel="stylesheet" href="/css/styles.css">
		<link href="../css/read.css" rel="stylesheet">
	</head>
	<body>
    <?php include('../components/navbar.php'); ?>

	<div class="content reader">
        <?php if ($content): ?>
			<h1 class="text-center fw-bold mt-3"><?= htmlspecialchars($file['title']) ?></h1>
			<div class="book-content text-center">
				<!-- Показать содержание -->
                <?= parseFB2Content($content) ?>
			</div>
        <?php else: ?>
			<p class="text-danger text-center">Ошибка при загрузке контента.</p>
        <?php endif; ?>
	</div>

	<!-- Подключение Bootstrap JS и зависимостей -->
	<script src="https://code.jquery.com/jquery-3.7.1.min.js"
	        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous" defer></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
	        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"
	        defer></script>
	</body>
	</html>

<?php

/**
 * Преобразование контента FB2 в HTML
 *
 * @param string $content Содержимое в формате FB2
 * @return string Отформатированный HTML
 */
function parseFB2Content($content)
{
    // Преобразование тегов FB2 в HTML
    $htmlContent = $content;

    // Обработка заголовков (h2, h3 и т. д.)
    $htmlContent = preg_replace('/<h2>(.*?)<\/h2>/', '<h2 class="fw-bold mt-4">$1</h2>', $htmlContent);
    $htmlContent = preg_replace('/<h3>(.*?)<\/h3>/', '<h3 class="fw-bold mt-3">$1</h3>', $htmlContent);

    // Обработка параграфов
    $htmlContent = preg_replace('/<p>(.*?)<\/p>/', '<p class="mb-3">$1</p>', $htmlContent);

    // Обработка изображений
    $htmlContent = preg_replace('/<img src="data:image\/.*?;base64,(.*?)" \/>/', '<img src="data:image/*;base64,$1" class="img-fluid mt-3 mb-3" />', $htmlContent);

    // Обработка курсивного текста
    $htmlContent = preg_replace('/<em>(.*?)<\/em>/', '<em>$1</em>', $htmlContent);

    // Обработка жирного текста
    $htmlContent = preg_replace('/<strong>(.*?)<\/strong>/', '<strong>$1</strong>', $htmlContent);

    // Обработка текста с зачеркиванием
    $htmlContent = preg_replace('/<del>(.*?)<\/del>/', '<del>$1</del>', $htmlContent);

    // Обработка цитат
    $htmlContent = preg_replace('/<blockquote>(.*?)<\/blockquote>/', '<blockquote class="blockquote">$1</blockquote>', $htmlContent);

    // Обработка таблиц
    $htmlContent = preg_replace('/<table>(.*?)<\/table>/', '<table class="table">$1</table>', $htmlContent);

    // Обработка стихотворений
    $htmlContent = preg_replace('/<div class="poem">(.*?)<\/div>/', '<div class="poem text-center">$1</div>', $htmlContent);

    // Обработка пустых строк
    $htmlContent = preg_replace('/<empty-line\/>/', '<br/>', $htmlContent);

    // Возвращаем готовый HTML контент
    return $htmlContent;
}

?>