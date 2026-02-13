<section>
    <h1>Личный кабинет</h1>
    <p>Здесь можно обновить персональные данные и перейти к заказам.</p>

    <form method="post" action="<?= htmlspecialchars(url('/profile')) ?>" class="form-wrap">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

        <label>Имя
            <input type="text" name="name" required value="<?= htmlspecialchars($profile['name'] ?? '') ?>">
        </label>
        <label>Email
            <input type="email" value="<?= htmlspecialchars($profile['email'] ?? '') ?>" disabled>
        </label>
        <label>Телефон
            <input type="text" name="phone" value="<?= htmlspecialchars((string) ($profile['phone'] ?? '')) ?>">
        </label>
        <label>Город
            <input type="text" name="city" value="<?= htmlspecialchars((string) ($profile['city'] ?? '')) ?>">
        </label>
        <label>Дата рождения
            <input type="date" name="birth_date" value="<?= htmlspecialchars((string) ($profile['birth_date'] ?? '')) ?>">
        </label>

        <button type="submit">Сохранить изменения</button>
    </form>

    <p style="margin-top:1rem;">
        <a class="nav-link nav-link-accent" href="<?= htmlspecialchars(url('/orders')) ?>">Мои заказы</a>
    </p>
</section>
