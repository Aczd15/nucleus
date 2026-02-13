<section class="hero">
    <h1>Добро пожаловать в Nucleus</h1>
    <p>Современные смартфоны, умные гаджеты и качественный сервис для ваших ежедневных задач.</p>
</section>

<section>
    <h2>Новости компании</h2>
    <div class="grid">
        <?php foreach ($news as $item): ?>
            <article class="card">
                <h3><?= htmlspecialchars($item['title']) ?></h3>
                <p><?= htmlspecialchars($item['text']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section>
    <h2>Популярные телефоны</h2>
    <div class="grid phones">
        <?php foreach ($phones as $phone): ?>
            <article class="card">
                <img src="<?= htmlspecialchars($phone['image'] ?: 'https://placehold.co/480x320?text=Nucleus') ?>" alt="<?= htmlspecialchars($phone['name']) ?>">
                <h3><?= htmlspecialchars($phone['name']) ?></h3>
                <p><?= htmlspecialchars($phone['description']) ?></p>
                <strong><?= number_format((float)$phone['price'], 0, '.', ' ') ?> ₽</strong>
            </article>
        <?php endforeach; ?>
    </div>
</section>
