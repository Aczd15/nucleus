<section class="hero">
    <h1>Акции и спецпредложения</h1>
    <p>Товары с актуальными скидками, которые вы можете купить уже сегодня.</p>
</section>

<section>
    <div class="grid phones">
        <?php if (!$salePhones): ?>
            <article class="card"><p>Сейчас активных акций нет. Загляните позже.</p></article>
        <?php endif; ?>
        <?php foreach ($salePhones as $phone): ?>
            <article class="card">
                <div class="product-image-frame"><img class="product-image" src="<?= htmlspecialchars($phone['image'] ?: 'https://placehold.co/640x480?text=Phone') ?>" alt="<?= htmlspecialchars($phone['name']) ?>"></div>
                <h3><?= htmlspecialchars($phone['name']) ?></h3>
                <p><?= htmlspecialchars($phone['description']) ?></p>
                <p>
                    <span class="muted" style="text-decoration:line-through;"><?= number_format((float)($phone['old_price'] ?? 0), 0, '.', ' ') ?> ₽</span>
                    <strong><?= number_format((float)$phone['price'], 0, '.', ' ') ?> ₽</strong>
                </p>
                <a class="nav-link nav-link-accent" href="<?= htmlspecialchars(url_with_query('/product', ['id' => (int)$phone['id']])) ?>">Смотреть товар</a>
            </article>
        <?php endforeach; ?>
    </div>
</section>
