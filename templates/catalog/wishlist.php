<section>
    <h1>Избранное</h1>

    <?php if (!$phones): ?>
        <p>В избранном пока ничего нет. Перейдите в <a href="<?= htmlspecialchars(url('/catalog')) ?>">каталог</a>.</p>
    <?php else: ?>
        <div class="grid phones">
            <?php foreach ($phones as $phone): ?>
                <article class="card">
                    <div class="product-image-frame"><img class="product-image" src="<?= htmlspecialchars($phone['image'] ?: 'https://placehold.co/640x480?text=Phone') ?>" alt="<?= htmlspecialchars($phone['name']) ?>" loading="lazy"></div>
                    <h3><?= htmlspecialchars($phone['name']) ?></h3>
                    <p><?= htmlspecialchars($phone['description']) ?></p>
                    <p><strong><?= number_format((float)$phone['price'], 0, '.', ' ') ?> ₽</strong></p>
                    <div class="inline">
                        <a class="nav-link" href="<?= htmlspecialchars(url_with_query('/product', ['id' => (int) $phone['id']])) ?>">Открыть</a>
                        <form method="post" action="<?= htmlspecialchars(url('/wishlist/toggle')) ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                            <input type="hidden" name="phone_id" value="<?= (int) $phone['id'] ?>">
                            <input type="hidden" name="redirect_path" value="/wishlist">
                            <button type="submit">Удалить</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
