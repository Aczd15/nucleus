<section class="form-wrap">
    <h1>Регистрация</h1>
    <?php foreach ($errors as $error): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endforeach; ?>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <label>Имя
            <input type="text" name="name" required minlength="2">
        </label>
        <label>Email
            <input type="email" name="email" required>
        </label>
        <label>Пароль
            <input type="password" name="password" required minlength="8">
        </label>
        <label>Подтверждение пароля
            <input type="password" name="confirm_password" required minlength="8">
        </label>
        <label>Капча: введите число <?= (int) ($_SESSION['captcha_answer'] ?? 0) ?>
            <input type="text" name="captcha" required>
        </label>
        <button type="submit">Создать аккаунт</button>
    </form>
</section>
