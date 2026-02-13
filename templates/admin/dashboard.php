<section>
    <h1>Админ-панель</h1>
    <div class="grid">
        <article class="card">
            <h3>Пользователи</h3>
            <p><?= (int) $usersCount ?></p>
            <a href="<?= htmlspecialchars(url('/admin/users')) ?>">Управлять</a>
        </article>
        <article class="card">
            <h3>Телефоны</h3>
            <p><?= (int) $phonesCount ?></p>
            <a href="<?= htmlspecialchars(url('/admin/phones')) ?>">Управлять</a>
        </article>
    </div>
</section>
