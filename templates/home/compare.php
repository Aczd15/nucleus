<section class="hero compare-hero">
    <h1>Compare Lab</h1>
    <p>Выберите модели ниже — и получите реальное сравнение по цене и характеристикам.</p>
</section>

<section class="card" style="margin-bottom:1rem;">
    <h2>Выбор устройств для сравнения</h2>
    <form method="get" action="<?= htmlspecialchars(url('/compare-lab')) ?>" class="compare-form">
        <?php if (!use_rewrite()): ?>
            <input type="hidden" name="r" value="/compare-lab">
        <?php endif; ?>
        <select name="ids[]" multiple size="6">
            <?php foreach ($allPhones as $phone): ?>
                <option value="<?= (int) $phone['id'] ?>" <?= in_array((int) $phone['id'], $selectedIds, true) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($phone['name']) ?> — <?= number_format((float)$phone['price'], 0, '.', ' ') ?> ₽
                </option>
            <?php endforeach; ?>
        </select>
        <small>Зажмите Ctrl (Cmd на Mac), чтобы выбрать несколько моделей.</small>
        <button type="submit">Сравнить</button>
    </form>
</section>

<?php if (count($comparePhones) < 2): ?>
    <div class="alert error">Выберите минимум 2 устройства для сравнения.</div>
<?php else: ?>
    <section>
        <div class="grid compare-grid">
            <?php foreach ($comparePhones as $phone): ?>
                <article class="card compare-card">
                    <div class="product-image-frame"><img class="product-image" src="<?= htmlspecialchars($phone['image'] ?: 'https://placehold.co/640x480?text=Phone') ?>" alt="<?= htmlspecialchars($phone['name']) ?>" loading="lazy"></div>
                    <h3><?= htmlspecialchars($phone['name']) ?></h3>
                    <strong><?= number_format((float)$phone['price'], 0, '.', ' ') ?> ₽</strong>
                    <a class="nav-link" href="<?= htmlspecialchars(url_with_query('/product', ['id' => (int) $phone['id']])) ?>">Открыть товар</a>
                </article>
            <?php endforeach; ?>
        </div>
    </section>


    <section class="card" style="margin-top:1rem;">
        <h2>График цен</h2>
        <p class="muted">Визуальное сравнение стоимости выбранных моделей.</p>
        <div class="price-chart">
            <?php foreach ($priceChart as $point): ?>
                <div class="price-chart-row">
                    <div class="price-chart-meta">
                        <strong><?= htmlspecialchars($point['name']) ?></strong>
                        <span><?= number_format((float) $point['price'], 0, '.', ' ') ?> ₽</span>
                    </div>
                    <div class="price-chart-track" aria-hidden="true">
                        <div class="price-chart-bar" style="width: <?= (float) $point['percent'] ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section style="margin-top:1rem;">
        <h2>Таблица сравнения</h2>
        <table class="table compare-table">
            <thead>
            <tr>
                <th>Параметр</th>
                <?php foreach ($comparePhones as $phone): ?>
                    <th><?= htmlspecialchars($phone['name']) ?></th>
                <?php endforeach; ?>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><strong>Цена</strong></td>
                <?php foreach ($comparePhones as $phone): ?>
                    <td><strong><?= number_format((float)$phone['price'], 0, '.', ' ') ?> ₽</strong></td>
                <?php endforeach; ?>
            </tr>
            <?php foreach ($specLabels as $label): ?>
                <tr>
                    <td><?= htmlspecialchars($label) ?></td>
                    <?php foreach ($comparePhones as $phone): $id = (int) $phone['id']; ?>
                        <td><?= htmlspecialchars($specMatrix[$label][$id] ?? '—') ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
<?php endif; ?>
