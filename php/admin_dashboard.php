<?php
global $conn;
session_start();
include('db_connect.php');
include('functions.php');

// Проверка роли пользователя
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$books = getBooks();

// Проверка на добавление или редактирование книги
$isEdit = isset($_GET['edit']);
$bookData = [
    'title' => '',
    'author' => '',
    'year' => '',
    'category' => '',
    'price' => '',
    'stock' => '',
    'cover' => '',
    'file_path' => ''
];

if ($isEdit && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $bookData = $result->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Bookstore</title>
	<!-- Include Bootstrap CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
	      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link rel="stylesheet" href="/css/styles.css">
</head>
<body>
<?php include('../components/navbar.php'); ?>

<div class="container mt-5">
	<h1>Административная панель</h1>

	<form method="POST" action="/php/<?= $isEdit ? 'edit_book.php' : 'add_book.php' ?>" enctype="multipart/form-data"
	      class="mb-4">
		<!-- Поле для ввода названия книги -->
		<div class="form-group mt-3">
			<label for="title">Название</label>
			<input type="text" class="form-control" id="title" name="title"
			       value="<?= htmlspecialchars($bookData['title'], ENT_QUOTES, 'UTF-8') ?>" required/>
		</div>

		<!-- Поле для загрузки обложки -->
		<div class="form-group mt-3">
			<label for="cover_image">Обложка книги (jpg, jpeg, png, gif)</label>
			<input type="file" class="form-control" id="cover_image" name="cover_image"
                <?= $isEdit ? '' : 'required' ?> />
            <?php if ($isEdit && !empty($bookData['cover'])): ?>
				<img src="<?= htmlspecialchars($bookData['cover'], ENT_QUOTES, 'UTF-8') ?>" alt="Обложка книги"
				     class="mt-2" style="max-width: 100px; max-height: 100px;"/>
            <?php endif; ?>
		</div>

		<!-- Поле для загрузки файла книги -->
		<div class="form-group mt-3">
			<label for="file_path">Файл книги (epub, fb2)</label>
			<input type="file" class="form-control" id="file_path" name="file_path"
                <?= $isEdit ? '' : 'required' ?> />
		</div>

		<!-- Поле ввода автора -->
		<div class="form-group mt-3">
			<label for="author">Автор</label>
			<input type="text" class="form-control" id="author" name="author"
			       value="<?= htmlspecialchars($bookData['author'], ENT_QUOTES, 'UTF-8') ?>" required/>
		</div>

		<!-- Поле для выбора даты публикации -->
		<div class="form-group mt-3">
			<label for="year">Дата публикации</label>
			<input type="date" class="form-control" id="year" name="year"
			       value="<?= htmlspecialchars($bookData['year'], ENT_QUOTES, 'UTF-8') ?>"/>
		</div>

		<!-- Поле ввода категории -->
		<div class="form-group mt-3">
			<label for="category">Категория</label>
			<input type="text" class="form-control" id="category" name="category"
			       value="<?= htmlspecialchars($bookData['category'], ENT_QUOTES, 'UTF-8') ?>"/>
		</div>

		<!-- Поле ввода цены -->
		<div class="form-group mt-3">
			<label for="price">Цена</label>
			<input type="number" step="0.01" class="form-control" id="price" name="price"
			       value="<?= htmlspecialchars($bookData['price'], ENT_QUOTES, 'UTF-8') ?>"/>
		</div>

		<!-- Поле ввода количества в наличии -->
		<div class="form-group mt-3">
			<label for="stock">Количество в наличии</label>
			<input type="number" class="form-control" id="stock" name="stock"
			       value="<?= htmlspecialchars($bookData['stock'], ENT_QUOTES, 'UTF-8') ?>" required/>
		</div>

		<!-- Кнопка отправки формы -->
		<button type="submit" class="btn btn-primary mt-4">
            <?= $isEdit ? 'Сохранить изменения' : 'Добавить книгу' ?>
		</button>
	</form>

	<h2 class="my-4">Доступные книги</h2>
	<table class="table table-hover text-center">
		<thead class="thead-dark">
		<tr>
			<th scope="col">Обложка</th>
			<th scope="col">Название</th>
			<th scope="col">Автор</th>
			<th scope="col">Дата</th>
			<th scope="col">Категория</th>
			<th scope="col">Цена</th>
			<th scope="col">Наличие</th>
			<th scope="col">Действие</th>
		</tr>
		</thead>
		<tbody>
        <?php foreach ($books as $book): ?>
			<tr>
				<td>
					<img src="<?= htmlspecialchars($book['cover'], ENT_QUOTES, 'UTF-8') ?>"
					     alt="Обложка книги" class="img-fluid" style="max-height: 150px;">
				</td>
				<td><?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8') ?></td>
				<td><?= htmlspecialchars($book['author'], ENT_QUOTES, 'UTF-8') ?></td>
				<td><?= htmlspecialchars($book['year'], ENT_QUOTES, 'UTF-8') ?></td>
				<td><?= htmlspecialchars($book['category'], ENT_QUOTES, 'UTF-8') ?></td>
				<td><?= htmlspecialchars($book['price'], ENT_QUOTES, 'UTF-8') ?></td>
				<td><?= htmlspecialchars($book['stock'], ENT_QUOTES, 'UTF-8') ?></td>
				<td class="d-grid gap-2">
					<a href="/php/read_book.php?id=<?= htmlspecialchars($book['id'], ENT_QUOTES, 'UTF-8') ?>"
					   class="btn btn-secondary btn-sm">Читать</a>
					<a href="/php/edit_book.php?id=<?= htmlspecialchars($book['id'], ENT_QUOTES, 'UTF-8') ?>"
					   class="btn btn-warning btn-sm">Изменить</a>
					<a href="<?= htmlspecialchars($book['file_path'], ENT_QUOTES, 'UTF-8') ?>"
					   class="btn btn-info btn-sm">Скачать</a>
					<a href="/php/delete_book.php?id=<?= htmlspecialchars($book['id'], ENT_QUOTES, 'UTF-8') ?>"
					   class="btn btn-danger btn-sm">Удалить</a>
				</td>
			</tr>
        <?php endforeach; ?>
		</tbody>
	</table>

	<script src="https://code.jquery.com/jquery-3.7.1.min.js"
	        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous" defer></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
	        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"
	        defer></script>
</body>
</html>