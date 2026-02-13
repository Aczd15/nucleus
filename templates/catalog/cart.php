<section>
    <h1>Корзина</h1>
    <?php if (!$cartItems): ?>
        <p>Корзина пуста. Перейдите в <a href="<?= htmlspecialchars(url('/catalog')) ?>">каталог</a>.</p>
    <?php else: ?>
        <form method="post" action="<?= htmlspecialchars(url('/cart/update')) ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
            <table class="table">
                <thead><tr><th>Товар</th><th>Цена</th><th>Кол-во</th><th>Сумма</th></tr></thead>
                <tbody>
                <?php $total = 0; foreach ($cartItems as $item): $sum = $item['price'] * $item['qty']; $total += $sum; ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= number_format((float)$item['price'], 0, '.', ' ') ?> ₽</td>
                        <td><input type="number" min="0" name="qty[<?= (int) $item['id'] ?>]" value="<?= (int) $item['qty'] ?>"></td>
                        <td><?= number_format((float)$sum, 0, '.', ' ') ?> ₽</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p><strong>Итого: <?= number_format((float)$total, 0, '.', ' ') ?> ₽</strong></p>
            <button type="submit">Обновить корзину</button>
        </form>
    <?php endif; ?>
</section>
