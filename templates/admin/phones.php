<section>
    <h1>Управление телефонами</h1>

    <form method="post" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <input type="hidden" name="id" value="0">
        <label>Название <input type="text" name="name" required></label>
        <label>Описание <textarea name="description" required></textarea></label>
        <label>Цена <input type="number" name="price" step="0.01" required></label>
        <label>URL изображения <input type="text" name="image"></label>
        <button type="submit">Добавить телефон</button>
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
                    <form method="post" style="display:inline-flex; gap:.5rem;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= (int) $phone['id'] ?>">
                        <input type="text" name="name" value="<?= htmlspecialchars($phone['name']) ?>" required>
                        <input type="text" name="description" value="<?= htmlspecialchars($phone['description']) ?>" required>
                        <input type="number" name="price" step="0.01" value="<?= (float) $phone['price'] ?>" required>
                        <input type="text" name="image" value="<?= htmlspecialchars($phone['image']) ?>">
                        <button type="submit">Сохранить</button>
                    </form>
                    <a href="/admin/phones?delete=<?= (int) $phone['id'] ?>" onclick="return confirm('Удалить?')">Удалить</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
