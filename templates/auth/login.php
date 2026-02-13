<section class="form-wrap">
    <h1>Авторизация</h1>
    <?php foreach ($errors as $error): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endforeach; ?>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <label>Email
            <input type="email" name="email" required>
        </label>
        <label>Пароль
            <input type="password" name="password" required minlength="8">
        </label>
        <button type="submit">Войти</button>
    </form>
</section>
