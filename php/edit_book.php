<?php
global $conn;
session_start();
include('db_connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit;
}

// Проверка наличия параметра id в запросе
if (!isset($_GET['id'])) {
    echo "ID книги не указан.";
    exit;
}

$id = (int)$_GET['id'];

// Запрос для получения данных книги
$sql = "SELECT * FROM books WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Книга не найдена.";
    exit;
}

$row = $result->fetch_assoc();
$title = $row['title'];
$author = $row['author'];
$year = $row['year'];
$category = $row['category'];
$price = $row['price'];
$stock = $row['stock'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $year = $_POST['year'];
    $category = $_POST['category'];
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];

    $coverImagePath = null;
    $bookFilePath = null;

    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['size'] > 0) {
        $coverImagePath = '../covers/' . basename($_FILES['cover_image']['name']);
        move_uploaded_file($_FILES['cover_image']['tmp_name'], $coverImagePath);
    }

    if (isset($_FILES['book_file']) && $_FILES['book_file']['size'] > 0) {
        $bookFilePath = '../books/' . basename($_FILES['book_file']['name']);
        move_uploaded_file($_FILES['book_file']['tmp_name'], $bookFilePath);
    }

    // Обновление информации о книге
    $query = $conn->prepare("
        UPDATE books 
        SET title = ?, author = ?, year = ?, category = ?, price = ?, stock = ?
        WHERE id = ?
    ");
    $query->bind_param("ssisdii", $title, $author, $year, $category, $price, $stock, $id);
    $query->execute();

    // Обновление путей файлов, если они были загружены
    if ($coverImagePath) {
        $query = $conn->prepare("UPDATE books SET cover_image = ? WHERE id = ?");
        $query->bind_param("si", $coverImagePath, $id);
        $query->execute();
    }

    if ($bookFilePath) {
        $query = $conn->prepare("UPDATE books SET book_file = ? WHERE id = ?");
        $query->bind_param("si", $bookFilePath, $id);
        $query->execute();
    }

    header('Location: admin_dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Редактировать книгу</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
	      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link rel="stylesheet" href="/css/styles.css">
</head>
<body>
<?php include('../components/navbar.php'); ?>
<div class="container mt-5">
	<h1 class="mb-4">Редактировать книгу</h1>
	<form action="edit_book.php?id=<?php echo $id; ?>" method="post" enctype="multipart/form-data">
		<input type="hidden" name="id" value="<?php echo $id; ?>">

		<div class="mb-3">
			<label for="title" class="form-label">Название:</label>
			<input type="text" class="form-control" id="title" name="title"
			       value="<?php echo htmlspecialchars($title); ?>" required>
		</div>

		<div class="mb-3">
			<label for="author" class="form-label">Автор:</label>
			<input type="text" class="form-control" id="author" name="author"
			       value="<?php echo htmlspecialchars($author); ?>" required>
		</div>

		<div class="mb-3">
			<label for="year" class="form-label">Год издания:</label>
			<input type="number" class="form-control" id="year" name="year"
			       value="<?php echo htmlspecialchars($year); ?>" required>
		</div>

		<div class="mb-3">
			<label for="category" class="form-label">Категория:</label>
			<input type="text" class="form-control" id="category" name="category"
			       value="<?php echo htmlspecialchars($category); ?>" required>
		</div>

		<div class="mb-3">
			<label for="price" class="form-label">Цена:</label>
			<input type="number" step="0.01" class="form-control" id="price" name="price"
			       value="<?php echo htmlspecialchars($price); ?>" required>
		</div>

		<div class="mb-3">
			<label for="stock" class="form-label">Количество на складе:</label>
			<input type="number" class="form-control" id="stock" name="stock"
			       value="<?php echo htmlspecialchars($stock); ?>" required>
		</div>

		<div class="mb-3">
			<label for="cover_image" class="form-label">Изображение обложки:</label>
			<input type="file" class="form-control" id="cover_image" name="cover_image">
		</div>

		<div class="mb-3">
			<label for="book_file" class="form-label">Файл книги:</label>
			<input type="file" class="form-control" id="book_file" name="book_file">
		</div>

		<button type="submit" class="btn btn-primary">Сохранить изменения</button>
	</form>
	<a href="admin_dashboard.php" class="btn btn-secondary mt-3">Назад к панели управления</a>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous" defer></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"
        defer></script>
</body>
</html>