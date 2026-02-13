<section>
    <h1>Управление телефонами</h1>

    <form method="post" action="<?= htmlspecialchars(url('/admin/phones')) ?>" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <input type="hidden" name="id" value="0">
        <label>Название <input type="text" name="name" required placeholder="iPhone 16 Pro Max"></label>
        <label>Описание <textarea name="description" required></textarea></label>
        <label>Характеристики <textarea name="specs" required placeholder="Экран..., Камера..., Память..."></textarea></label>
        <label>Цена <input type="number" step="0.01" name="price" required></label>
        <label>URL картинки <input type="text" name="image"></label>
        <button type="submit">Добавить</button>
    </form>

    <table class="table">
        <thead><tr><th>ID</th><th>Название</th><th>Цена</th><th>Действия</th></tr></thead>
        <tbody>
        <?php foreach ($phones as $phone): ?>
            <tr>
                <td><?= (int) $phone['id'] ?></td>
                <td><?= htmlspecialchars($phone['name']) ?></td>
                <td><?= number_format((float)$phone['price'], 0, '.', ' ') ?> ₽</td>
                <td>
                    <form method="post" action="<?= htmlspecialchars(url('/admin/phones')) ?>" style="display:inline-flex; gap:.5rem; flex-wrap:wrap;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= (int) $phone['id'] ?>">
                        <input type="text" name="name" value="<?= htmlspecialchars($phone['name']) ?>" required>
                        <input type="text" name="description" value="<?= htmlspecialchars($phone['description']) ?>" required>
                        <input type="text" name="specs" value="<?= htmlspecialchars($phone['specs']) ?>" required>
                        <input type="number" step="0.01" name="price" value="<?= (float) $phone['price'] ?>" required>
                        <input type="text" name="image" value="<?= htmlspecialchars($phone['image']) ?>">
                        <button type="submit">Сохранить</button>
                    </form>
                    <a href="<?= htmlspecialchars(url_with_query('/admin/phones', ['delete' => (int) $phone['id']])) ?>" onclick="return confirm('Удалить?')">Удалить</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
