<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nucleus</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(asset_url('style.css')) ?>">
</head>
<body>
<header class="site-header">
    <a class="logo" href="<?= htmlspecialchars(url('/')) ?>">Nucleus</a>
</header>
<main class="container">
    <?php if ($msg = flash('success')): ?><div class="alert success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($msg = flash('error')): ?><div class="alert error"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
