<section>
    <h1>Каталог смартфонов</h1>

    <form method="get" action="<?= htmlspecialchars(url('/catalog')) ?>" class="card" style="margin-bottom:1rem;">
        <?php if (!use_rewrite()): ?>
            <input type="hidden" name="r" value="/catalog">
        <?php endif; ?>

        <div class="filters-grid">
            <label>Поиск
                <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Например, iPhone">
            </label>
            <label>Цена от
                <input type="number" step="0.01" name="min_price" value="<?= $minPrice > 0 ? htmlspecialchars((string) $minPrice) : '' ?>">
            </label>
            <label>Цена до
                <input type="number" step="0.01" name="max_price" value="<?= $maxPrice > 0 ? htmlspecialchars((string) $maxPrice) : '' ?>">
            </label>
            <label>Сортировка
                <select name="sort">
                    <option value="new" <?= $sort === 'new' ? 'selected' : '' ?>>Сначала новые</option>
                    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Цена по возрастанию</option>
                    <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Цена по убыванию</option>
                    <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>По названию</option>
                </select>
            </label>
        </div>

        <div class="inline" style="margin-top:.8rem;">
            <button type="submit">Применить</button>
            <a class="nav-link" href="<?= htmlspecialchars(url('/catalog')) ?>">Сбросить</a>
            <span class="muted">Найдено: <?= (int) $totalPhones ?></span>
        </div>
    </form>

    <div class="grid phones">
        <?php foreach ($phones as $phone): ?>
            <article class="card">
                <div class="product-image-frame"><img class="product-image" src="<?= htmlspecialchars($phone['image'] ?: 'https://placehold.co/640x480?text=Phone') ?>" alt="<?= htmlspecialchars($phone['name']) ?>" loading="lazy"></div>
                <h3><?= htmlspecialchars($phone['name']) ?></h3>
                <p><?= htmlspecialchars($phone['description']) ?></p>
                <div class="row">
                    <strong><?= number_format((float)$phone['price'], 0, '.', ' ') ?> ₽</strong>
                    <a href="<?= htmlspecialchars(url_with_query('/product', ['id' => (int) $phone['id']])) ?>">Характеристики</a>
                </div>

                <?php if (current_user()): ?>
                    <form method="post" action="<?= htmlspecialchars(url('/wishlist/toggle')) ?>" style="margin-top:.7rem;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <input type="hidden" name="phone_id" value="<?= (int) $phone['id'] ?>">
                        <input type="hidden" name="redirect_path" value="/catalog">
                        <button type="submit"><?= in_array((int) $phone['id'], $wishlistIds ?? [], true) ? 'Убрать из избранного' : 'В избранное' ?></button>
                    </form>

                    <form method="post" action="<?= htmlspecialchars(url('/cart/add')) ?>" style="margin-top:.7rem;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <input type="hidden" name="phone_id" value="<?= (int) $phone['id'] ?>">
                        <button type="submit">В корзину</button>
                    </form>
                <?php else: ?>
                    <a class="nav-link" href="<?= htmlspecialchars(url('/login')) ?>" style="margin-top:.7rem;">Войдите, чтобы добавить в корзину</a>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="pagination" style="margin-top:1rem;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a class="nav-link <?= $i === $page ? 'nav-link-accent' : '' ?>" href="<?= htmlspecialchars(url_with_query('/catalog', ['q' => $q, 'min_price' => $minPrice > 0 ? $minPrice : null, 'max_price' => $maxPrice > 0 ? $maxPrice : null, 'sort' => $sort, 'page' => $i])) ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</section>
