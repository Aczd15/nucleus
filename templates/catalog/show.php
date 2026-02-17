<section>
    <a href="<?= htmlspecialchars(url('/catalog')) ?>">← Назад в каталог</a>
    <article class="card" style="margin-top:1rem;">
        <div class="product-image-frame product-image-frame--large"><img class="product-image product-image--large" src="<?= htmlspecialchars($phone['image'] ?: 'https://placehold.co/900x675?text=Phone') ?>" alt="<?= htmlspecialchars($phone['name']) ?>"></div>
        <h1><?= htmlspecialchars($phone['name']) ?></h1>
        <p><?= htmlspecialchars($phone['description']) ?></p>
        <h3>Характеристики</h3>
        <p><?= nl2br(htmlspecialchars(($phone['specs'] ?? '') ?: 'Характеристики будут добавлены позже.')) ?></p>
        <p>
            <?php if (!empty($phone['is_sale']) && !empty($phone['old_price'])): ?>
                <span class="muted" style="text-decoration:line-through;"><?= number_format((float)$phone['old_price'], 0, '.', ' ') ?> ₽</span>
            <?php endif; ?>
            <strong>Цена: <?= number_format((float)$phone['price'], 0, '.', ' ') ?> ₽</strong>
        </p>
        <p>Рейтинг: <strong><?= number_format((float)($rating['avg_rating'] ?? 0), 1) ?>/5</strong> (<?= (int) ($rating['total_reviews'] ?? 0) ?> отзывов)</p>

        <?php if (current_user()): ?>
            <form method="post" action="<?= htmlspecialchars(url('/wishlist/toggle')) ?>" style="margin:.8rem 0; max-width:320px;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                <input type="hidden" name="phone_id" value="<?= (int) $phone['id'] ?>">
                <input type="hidden" name="redirect_path" value="/catalog">
                <button type="submit">В избранное</button>
            </form>
        <?php endif; ?>
        <form method="post" action="<?= htmlspecialchars(url('/cart/add')) ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
            <input type="hidden" name="phone_id" value="<?= (int) $phone['id'] ?>">
            <button type="submit">Добавить в корзину</button>
        </form>
    </article>
</section>

<section class="card" style="margin-top:1rem;">
    <h2>Отзывы</h2>
    <?php if (current_user()): ?>
        <form method="post" action="<?= htmlspecialchars(url('/product/review')) ?>" class="review-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
            <input type="hidden" name="phone_id" value="<?= (int) $phone['id'] ?>">
            <label>Оценка
                <select name="rating" required>
                    <option value="5">5 — Отлично</option>
                    <option value="4">4 — Хорошо</option>
                    <option value="3">3 — Нормально</option>
                    <option value="2">2 — Слабо</option>
                    <option value="1">1 — Плохо</option>
                </select>
            </label>
            <label>Комментарий
                <textarea name="comment" required placeholder="Поделитесь впечатлением"></textarea>
            </label>
            <button type="submit">Оставить отзыв</button>
        </form>
    <?php else: ?>
        <p>Чтобы оставить отзыв, <a href="<?= htmlspecialchars(url('/login')) ?>">войдите в аккаунт</a>.</p>
    <?php endif; ?>

    <?php if (!$reviews): ?>
        <p class="muted">Пока отзывов нет.</p>
    <?php else: ?>
        <?php foreach ($reviews as $review): ?>
            <article class="card" style="margin-top:.7rem;">
                <p><strong><?= htmlspecialchars($review['user_name']) ?></strong> — <?= (int) $review['rating'] ?>/5</p>
                <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                <p class="muted"><?= htmlspecialchars($review['created_at']) ?></p>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<section style="margin-top:1rem;">
    <h2>Похожие товары</h2>
    <div class="grid phones">
        <?php foreach ($similarPhones as $item): ?>
            <article class="card">
                <div class="product-image-frame"><img class="product-image" src="<?= htmlspecialchars($item['image'] ?: 'https://placehold.co/640x480?text=Phone') ?>" alt="<?= htmlspecialchars($item['name']) ?>"></div>
                <h3><?= htmlspecialchars($item['name']) ?></h3>
                <p><strong><?= number_format((float)$item['price'], 0, '.', ' ') ?> ₽</strong></p>
                <a class="nav-link" href="<?= htmlspecialchars(url_with_query('/product', ['id' => (int)$item['id']])) ?>">Открыть</a>
            </article>
        <?php endforeach; ?>
    </div>
</section>
