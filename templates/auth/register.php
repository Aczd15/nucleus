<section class="form-wrap">
    <h1>Регистрация</h1>
    <?php foreach ($errors as $error): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endforeach; ?>

    <form method="post" action="<?= htmlspecialchars(url('/register')) ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

        <label>Имя и фамилия
            <input type="text" name="name" required minlength="2" placeholder="Иван Петров">
        </label>
        <label>Email
            <input type="email" name="email" required placeholder="you@example.com">
        </label>
        <label>Телефон
            <input type="text" name="phone" placeholder="+7 (999) 123-45-67">
        </label>
        <label>Город
            <input type="text" name="city" placeholder="Москва">
        </label>
        <label>Дата рождения
            <input type="date" name="birth_date">
        </label>
        <label>Пароль
            <input type="password" name="password" required minlength="8">
        </label>
        <label>Подтвердите пароль
            <input type="password" name="confirm_password" required minlength="8">
        </label>

        <div class="captcha-box">
            <small>Проверка, что вы не робот:</small>
            <strong><?= htmlspecialchars($_SESSION['captcha_label'] ?? 'Сколько будет 2 + 2?') ?></strong>
            <input type="number" name="captcha" required placeholder="Введите ответ">
        </div>

        <button type="submit">Создать аккаунт</button>
    </form>
</section>
