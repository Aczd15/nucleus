<section>
    <h1>Каталог смартфонов</h1>
    <div class="grid phones">
        <?php foreach ($phones as $phone): ?>
            <article class="card">
                <img src="<?= htmlspecialchars($phone['image'] ?: 'https://placehold.co/480x320?text=Phone') ?>" alt="<?= htmlspecialchars($phone['name']) ?>">
                <h3><?= htmlspecialchars($phone['name']) ?></h3>
                <p><?= htmlspecialchars($phone['description']) ?></p>
                <div class="row">
                    <strong><?= number_format((float)$phone['price'], 0, '.', ' ') ?> ₽</strong>
                    <a href="<?= htmlspecialchars(url_with_query('/product', ['id' => (int) $phone['id']])) ?>">Характеристики</a>
                </div>
                <form method="post" action="<?= htmlspecialchars(url('/cart/add')) ?>" style="margin-top:.7rem;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="phone_id" value="<?= (int) $phone['id'] ?>">
                    <button type="submit">В корзину</button>
                </form>
            </article>
        <?php endforeach; ?>
    </div>
</section>
