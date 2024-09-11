<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
	<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
		<div class="container">
			<a class="navbar-brand" href="/">Книжный магазин</a>
			<button
					class="navbar-toggler"
					type="button"
					data-bs-toggle="collapse"
					data-bs-target="#navbarNav"
					aria-controls="navbarNav"
					aria-expanded="false"
					aria-label="Toggle navigation"
			>
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav ms-auto">
					<li class="nav-item">
						<a class="nav-link active" href="/index.php">Главная</a>
					</li>
					<!-- Проверка роли пользователя -->
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'customer'): ?>
						<li class="nav-item">
							<a class="nav-link" href="/php/customer_dashboard.php">Клиентский кабинет</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="/php/logout.php">Выйти</a>
						</li>
                    <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
						<li class="nav-item">
							<a class="nav-link" href="/php/admin_dashboard.php">Администратор</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="/php/logout.php">Выйти</a>
						</li>
                    <?php else: ?>
						<li class="nav-item">
							<a class="nav-link" href="/register.php">Регистрация</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="/login.php">Вход</a>
						</li>
                    <?php endif; ?>
				</ul>
			</div>
		</div>
	</nav>
</header>