<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nucleus Store</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(asset_url('style.css')) ?>">
</head>
<body>
<header class="site-header">
    <a class="logo" href="<?= htmlspecialchars(url('/')) ?>">Nucleus</a>
    <nav>
        <a href="<?= htmlspecialchars(url('/catalog')) ?>">Каталог</a>
        <a href="<?= htmlspecialchars(url('/cart')) ?>">Корзина</a>
        <?php if (current_user()): ?>
            <?php if (is_admin()): ?><a href="<?= htmlspecialchars(url('/admin')) ?>">Админ</a><?php endif; ?>
            <span class="muted">Привет, <?= htmlspecialchars(current_user()['name']) ?></span>
            <a href="<?= htmlspecialchars(url('/logout')) ?>">Выйти</a>
        <?php else: ?>
            <a href="<?= htmlspecialchars(url('/login')) ?>">Войти</a>
            <a href="<?= htmlspecialchars(url('/register')) ?>">Регистрация</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
    <?php if ($msg = flash('success')): ?><div class="alert success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($msg = flash('error')): ?><div class="alert error"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
