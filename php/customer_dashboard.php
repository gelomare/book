<?php
global $conn;
include('db_connect.php');
include_once('functions.php');

session_start();
if ($_SESSION['role'] != 'customer') {
    header('Location: ../index.php');
    exit;
}

// Получаем параметры фильтрации из GET-запроса
$category = $_GET['category'] ?? null;
$author = $_GET['author'] ?? null;
$year = $_GET['year'] ?? null;

$books = getBooks($category, $author, $year);
?>


<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Клиентский кабинет - Книжный магазин</title>
	<!-- Include Bootstrap CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
	      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link rel="stylesheet" href="/css/styles.css">
</head>
<body>
<?php include('../components/navbar.php'); ?>


<div class="container mt-5">
	<h1>Каталог книг</h1>

	<div class="card mb-4">
		<div class="card-body">
			<form method="GET" action="customer_dashboard.php">
				<div class="row g-3">
					<div class="col-md-3">
						<input
								type="text"
								name="category"
								class="form-control"
								placeholder="Категория"
								value="<?= htmlspecialchars($_GET['category'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
						>
					</div>
					<div class="col-md-3">
						<input
								type="text"
								name="author"
								class="form-control"
								placeholder="Автор"
								value="<?= htmlspecialchars($_GET['author'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
						>
					</div>
					<div class="col-md-3">
						<input
								type="number"
								name="year"
								class="form-control"
								placeholder="Год публикации"
								value="<?= htmlspecialchars($_GET['year'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
						>
					</div>
					<div class="col-md-3 d-grid">
						<button type="submit" class="btn btn-primary">Искать</button>
					</div>
				</div>
			</form>
		</div>
	</div>
    <?php
    function render_book_button($user_id, $book_id, $conn)
    {
        $stmt = $conn->prepare("SELECT * FROM bookstore_db.rentals WHERE user_id = ? AND book_id = ?");
        $stmt->bind_param("ii", $user_id, $book_id);
        $stmt->execute();
        $purchase_result = $stmt->get_result();

        if ($purchase_result->num_rows > 0) {
            return '<a href="read_book.php?id=' . $book_id . '" class="btn btn-secondary">Читать</a>';
        }

        $stmt = $conn->prepare("SELECT * FROM rentals WHERE user_id = ? AND book_id = ? AND rental_end >= CURDATE()");
        $stmt->bind_param("ii", $user_id, $book_id);
        $stmt->execute();
        $rental_result = $stmt->get_result();

        if ($rental_result->num_rows > 0) {
            return '<a href="read_book.php?id=' . $book_id . '" class="btn btn-secondary">Читать</a>';
        }

        return '';
    }


    ?>

	<div class="container mt-5">
		<div class="row">
            <?php foreach ($books as $book): ?>
				<div class="col-md-4 d-flex align-items-stretch mb-3 text-center">
					<div class="card mb-4" style="height: 100%;">
						<img src="/covers/<?= htmlspecialchars($book['cover'], ENT_QUOTES, 'UTF-8') ?>"
						     class="card-img-top" alt="<?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8') ?>">
						<div class="card-body d-flex flex-column">
							<h5 class="card-title"><?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8') ?></h5>
							<p class="card-text">Автор:
								<strong><?= htmlspecialchars($book['author'], ENT_QUOTES, 'UTF-8') ?></strong></p>
							<p class="card-text">Год:
								<strong><?= htmlspecialchars($book['year'], ENT_QUOTES, 'UTF-8') ?></strong></p>
							<p class="card-text">Категория:
								<strong><?= htmlspecialchars($book['category'], ENT_QUOTES, 'UTF-8') ?></strong></p>
							<p class="card-text">Цена:
								<strong><?= htmlspecialchars($book['price'], ENT_QUOTES, 'UTF-8') ?> ₽</strong></p>
							<p class="card-text">В наличии:
								<strong><?= htmlspecialchars($book['stock'], ENT_QUOTES, 'UTF-8') ?> шт.</strong></p>
							<div class="mt-auto">
								<form method="POST" action="/php/rent_book.php">
									<div class="form-group mb-2">
										<label for="rental_period">Период аренды</label>
										<select class="form-control" id="rental_period" name="rental_period" required>
											<option value="14">2 недели</option>
											<option value="30">1 месяц</option>
											<option value="90">3 месяца</option>
										</select>
									</div>
									<input type="hidden" name="book_id"
									       value="<?= htmlspecialchars($book['id'], ENT_QUOTES, 'UTF-8') ?>">
									<button type="submit" class="btn btn-primary">Арендовать</button>
                                    <?= render_book_button($_SESSION['user_id'], $book['id'], $conn); ?>
								</form>
							</div>
						</div>
					</div>
				</div>
            <?php endforeach; ?>
		</div>
	</div>

	<!-- Include Bootstrap JS and dependencies -->
	<script
			src="https://code.jquery.com/jquery-3.7.1.min.js"
			integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
			crossorigin="anonymous" defer></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
	        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"
	        defer></script>
</body>
</html>