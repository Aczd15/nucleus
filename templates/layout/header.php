<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nucleus Store</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(asset_url('style.css')) ?>">
</head>
<body>
<div class="bg-aurora"></div>
<header class="site-header glass">
    <a class="logo" href="<?= htmlspecialchars(url('/')) ?>">Nucleus</a>

    <details class="menu-dropdown">
        <summary class="menu-trigger">☰ Меню</summary>
        <nav class="menu-panel">
            <a class="nav-link" href="<?= htmlspecialchars(url('/catalog')) ?>">Каталог</a>
            <a class="nav-link" href="<?= htmlspecialchars(url('/about')) ?>">О компании</a>
            <a class="nav-link" href="<?= htmlspecialchars(url('/delivery-payment')) ?>">Доставка/Оплата</a>
            <a class="nav-link" href="<?= htmlspecialchars(url('/wow')) ?>">WOW Space</a>
            <a class="nav-link" href="<?= htmlspecialchars(url('/compare-lab')) ?>">Compare Lab</a>
            <a class="nav-link" href="<?= htmlspecialchars(url('/cart')) ?>">Корзина</a>
            <?php if (current_user()): ?>
                <a class="nav-link" href="<?= htmlspecialchars(url('/profile')) ?>">Кабинет</a>
                <a class="nav-link" href="<?= htmlspecialchars(url('/orders')) ?>">Мои заказы</a>
                <a class="nav-link" href="<?= htmlspecialchars(url('/wishlist')) ?>">Избранное</a>
                <?php if (is_admin()): ?><a class="nav-link" href="<?= htmlspecialchars(url('/admin')) ?>">Админ</a><?php endif; ?>
                <span class="muted menu-user">Привет, <?= htmlspecialchars(current_user()['name']) ?></span>
                <a class="nav-link nav-link-accent" href="<?= htmlspecialchars(url('/logout')) ?>">Выйти</a>
            <?php else: ?>
                <a class="nav-link" href="<?= htmlspecialchars(url('/login')) ?>">Войти</a>
                <a class="nav-link nav-link-accent" href="<?= htmlspecialchars(url('/register')) ?>">Регистрация</a>
            <?php endif; ?>
        </nav>
    </details>
</header>
<main class="container">
    <?php if ($msg = flash('success')): ?><div class="alert success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($msg = flash('error')): ?><div class="alert error"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
