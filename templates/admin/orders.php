<section>
    <h1>Заказы</h1>
    <table class="table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Пользователь</th>
            <th>Сумма</th>
            <th>Статус</th>
            <th>Дата</th>
            <th>Действие</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td>#<?= (int) $order['id'] ?></td>
                <td>
                    <strong><?= htmlspecialchars($order['user_name']) ?></strong><br>
                    <span class="muted"><?= htmlspecialchars($order['user_email']) ?></span>
                </td>
                <td><?= number_format((float) $order['total_amount'], 0, '.', ' ') ?> ₽</td>
                <td><strong><?= htmlspecialchars($order['status']) ?></strong></td>
                <td><?= htmlspecialchars($order['created_at']) ?></td>
                <td>
                    <form method="post" action="<?= htmlspecialchars(url('/admin/orders')) ?>" class="inline">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                        <select name="status">
                            <?php foreach ($allowedStatuses as $status): ?>
                                <option value="<?= htmlspecialchars($status) ?>" <?= $status === $order['status'] ? 'selected' : '' ?>><?= htmlspecialchars($status) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit">Обновить</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
