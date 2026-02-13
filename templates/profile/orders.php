<section>
    <h1>Мои заказы</h1>

    <?php if (!$orders): ?>
        <p>У вас пока нет заказов. Перейдите в <a href="<?= htmlspecialchars(url('/catalog')) ?>">каталог</a>.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <article class="card" style="margin-bottom:1rem;">
                <h3>Заказ #<?= (int) $order['id'] ?></h3>
                <p>Статус: <strong><?= htmlspecialchars($order['status']) ?></strong></p>
                <p>Дата: <?= htmlspecialchars($order['created_at']) ?></p>
                <p>Сумма: <strong><?= number_format((float) $order['total_amount'], 0, '.', ' ') ?> ₽</strong></p>

                <?php if (!in_array((string) $order['status'], ['delivered', 'cancelled'], true)): ?>
                    <form method="post" action="<?= htmlspecialchars(url('/orders/cancel')) ?>" class="cancel-order-form" onsubmit="return confirm('Отменить заказ?')">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                        <label>Причина отмены
                            <textarea name="reason" required placeholder="Укажите причину отмены"></textarea>
                        </label>
                        <button type="submit">Отменить заказ</button>
                    </form>
                <?php endif; ?>

                <?php if (!empty($order['history'])): ?>
                    <h4>История статусов</h4>
                    <ul class="status-history">
                        <?php foreach ($order['history'] as $history): ?>
                            <li>
                                <strong><?= htmlspecialchars($history['status']) ?></strong>
                                <span class="muted">— <?= htmlspecialchars($history['created_at']) ?></span>
                                <?php if (!empty($history['comment'])): ?>
                                    <div class="muted"><?= htmlspecialchars($history['comment']) ?></div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <table class="table" style="margin-top:.7rem;">
                    <thead><tr><th>Товар</th><th>Цена</th><th>Кол-во</th><th>Итого</th></tr></thead>
                    <tbody>
                    <?php foreach ($order['items'] as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['product_name']) ?></td>
                            <td><?= number_format((float) $item['unit_price'], 0, '.', ' ') ?> ₽</td>
                            <td><?= (int) $item['qty'] ?></td>
                            <td><?= number_format((float) $item['unit_price'] * (int) $item['qty'], 0, '.', ' ') ?> ₽</td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
