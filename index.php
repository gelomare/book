<?php
include('php/db_connect.php');
include('php/functions.php');
global $conn;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$books = getBooks();

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Книжный магазин</title>
	<!-- Include Bootstrap CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
	      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link rel="stylesheet" href="/css/styles.css">
</head>
<body>
<?php include('components/navbar.php'); ?>
<div class="container mt-5 text-center">
	<h1>Добро пожаловать в наш Книжный магазин</h1>
	<p>Здесь вы можете арендовать и прочитать книги.</p>
</div>

<div class="container mt-5">
	<div class="row">
        <?php foreach ($books as $book): ?>
			<div class="col-md-4 d-flex align-items-stretch mb-3 text-center">
				<div class="card mb-4" style="height: 100%;">
					<img src="/covers/<?= htmlspecialchars($book['cover'], ENT_QUOTES, 'UTF-8') ?>" class="card-img-top"
					     alt="<?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8') ?>">
					<div class="card-body d-flex flex-column">
						<h5 class="card-title"><?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8') ?></h5>
						<p class="card-text">Автор:
							<strong><?= htmlspecialchars($book['author'], ENT_QUOTES, 'UTF-8') ?></strong></p>
						<p class="card-text">Год:
							<strong><?= htmlspecialchars($book['year'], ENT_QUOTES, 'UTF-8') ?></strong></p>
						<p class="card-text">Категория:
							<strong><?= htmlspecialchars($book['category'], ENT_QUOTES, 'UTF-8') ?></strong></p>
						<p class="card-text">Цена: <strong><?= htmlspecialchars($book['price'], ENT_QUOTES, 'UTF-8') ?>
								₽</strong></p>
						<p class="card-text">В наличии:
							<strong><?= htmlspecialchars($book['stock'], ENT_QUOTES, 'UTF-8') ?> шт.</strong></p>
					</div>
				</div>
			</div>
        <?php endforeach; ?>
	</div>
</div>


<!-- Include Bootstrap JS and dependencies-->
<script
		src="https://code.jquery.com/jquery-3.7.1.min.js"
		integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
		crossorigin="anonymous" defer></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"
        defer></script>
</body>
</html>