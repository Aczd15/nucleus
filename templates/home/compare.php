<section class="hero compare-hero">
    <h1>Compare Lab</h1>
    <p>Сравните топовые смартфоны по цене и характеристикам в одном месте.</p>
</section>

<section>
    <div class="grid compare-grid">
        <?php foreach ($compare as $phone): ?>
            <article class="card compare-card">
                <img src="<?= htmlspecialchars($phone['image'] ?: 'https://placehold.co/480x320?text=Phone') ?>" alt="<?= htmlspecialchars($phone['name']) ?>">
                <h3><?= htmlspecialchars($phone['name']) ?></h3>
                <p><?= nl2br(htmlspecialchars(($phone['specs'] ?? '') ?: 'Характеристики будут добавлены.')) ?></p>
                <strong><?= number_format((float)$phone['price'], 0, '.', ' ') ?> ₽</strong>
                <a class="nav-link" href="<?= htmlspecialchars(url_with_query('/product', ['id' => (int) $phone['id']])) ?>">Открыть товар</a>
            </article>
        <?php endforeach; ?>
    </div>
</section>
