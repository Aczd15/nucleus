<section>
    <a href="<?= htmlspecialchars(url('/catalog')) ?>">← Назад в каталог</a>
    <article class="card" style="margin-top:1rem;">
        <div class="product-image-frame product-image-frame--large"><img class="product-image product-image--large" src="<?= htmlspecialchars($phone['image'] ?: 'https://placehold.co/900x675?text=Phone') ?>" alt="<?= htmlspecialchars($phone['name']) ?>"></div>
        <h1><?= htmlspecialchars($phone['name']) ?></h1>
        <p><?= htmlspecialchars($phone['description']) ?></p>
        <h3>Характеристики</h3>
        <p><?= nl2br(htmlspecialchars(($phone['specs'] ?? '') ?: 'Характеристики будут добавлены позже.')) ?></p>
        <p><strong>Цена: <?= number_format((float)$phone['price'], 0, '.', ' ') ?> ₽</strong></p>
        <form method="post" action="<?= htmlspecialchars(url('/cart/add')) ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
            <input type="hidden" name="phone_id" value="<?= (int) $phone['id'] ?>">
            <button type="submit">Добавить в корзину</button>
        </form>
    </article>
</section>
