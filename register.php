<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Регистрация - Книжный магазин</title>
	<!-- Include Bootstrap CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
	      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link rel="stylesheet" href="/css/register.css">
</head>
<body>
<?php include('components/navbar.php'); ?>

<div class="container mt-5">
	<div class="row justify-content-center">
		<div class="col-md-6">
			<div class="card">
				<div class="card-body">
					<h1 class="text-center mb-4">Регистрация</h1>
					<form method="POST" action="/php/register_user.php">
						<div class="form-group mt-1">
							<label for="username">Имя пользователя</label>
							<input type="text" class="form-control" id="username" name="username" required/>
						</div>
						<div class="form-group mt-1">
							<label for="email">Email</label>
							<input type="email" class="form-control" id="email" name="email" required/>
						</div>
						<div class="form-group mt-1">
							<label for="password">Пароль</label>
							<input type="password" class="form-control" id="password" name="password" required/>
						</div>
						<button type="submit" class="btn btn-primary btn-block mt-2">Зарегистрироваться</button>
					</form>
					<p class="text-center mt-3">Уже зарегистрированы? <a href="/login.php">Войти</a></p>
				</div>
			</div>
		</div>
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