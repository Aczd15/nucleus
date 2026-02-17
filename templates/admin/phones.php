<section>
    <h1>Управление телефонами</h1>

    <form method="post" action="<?= htmlspecialchars(url('/admin/phones')) ?>" class="admin-form" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <input type="hidden" name="id" value="0">
        <label>Название <input type="text" name="name" required placeholder="iPhone 16 Pro Max"></label>
        <label>Описание <textarea name="description" required></textarea></label>
        <label>Характеристики <textarea name="specs" required placeholder="Экран..., Камера..., Память..."></textarea></label>
        <label>Цена <input type="number" step="0.01" name="price" required></label>
        <label>Фото с компьютера <input type="file" name="image_file" accept="image/*"></label>
        <?php if (!empty($hasPhonePopular)): ?><label><input type="checkbox" name="is_popular" value="1"> Показывать на главной как популярный</label><?php endif; ?>
        <?php if (!empty($hasPhoneSale)): ?><label><input type="checkbox" name="is_sale" value="1"> Участвует в акции</label><label>Старая цена <input type="number" step="0.01" name="old_price" placeholder="Былая цена"></label><?php endif; ?>
        <label>или URL картинки <input type="text" name="image" placeholder="https://..."></label>
        <button type="submit">Добавить</button>
    </form>

    <table class="table">
        <thead><tr><th>ID</th><th>Фото</th><th>Название</th><th>Цена</th><th>Популярный</th><th>Акция</th><th>Действия</th></tr></thead>
        <tbody>
        <?php foreach ($phones as $phone): ?>
            <tr>
                <td><?= (int) $phone['id'] ?></td>
                <td><img class="product-image product-image--thumb" src="<?= htmlspecialchars(($phone['image'] ?? '') ?: 'https://placehold.co/640x480?text=Phone') ?>" alt="<?= htmlspecialchars($phone['name']) ?>"></td>
                <td><?= htmlspecialchars($phone['name']) ?></td>
                <td><?= number_format((float)$phone['price'], 0, '.', ' ') ?> ₽</td>
                <td><?= !empty($phone['is_popular']) ? 'Да' : 'Нет' ?></td>
                <td><?= !empty($phone['is_sale']) ? 'Да' : 'Нет' ?></td>
                <td>
                    <form method="post" action="<?= htmlspecialchars(url('/admin/phones')) ?>" style="display:inline-flex; gap:.5rem; flex-wrap:wrap;" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= (int) $phone['id'] ?>">
                        <input type="hidden" name="existing_image" value="<?= htmlspecialchars((string) ($phone['image'] ?? '')) ?>">
                        <input type="text" name="name" value="<?= htmlspecialchars($phone['name']) ?>" required>
                        <input type="text" name="description" value="<?= htmlspecialchars($phone['description']) ?>" required>
                        <input type="text" name="specs" value="<?= htmlspecialchars((string) ($phone['specs'] ?? '')) ?>" required>
                        <input type="number" step="0.01" name="price" value="<?= (float) $phone['price'] ?>" required>
                        <input type="file" name="image_file" accept="image/*">
                        <?php if (!empty($hasPhonePopular)): ?><label style="display:flex;align-items:center;gap:.35rem;"><input type="checkbox" name="is_popular" value="1" <?= !empty($phone['is_popular']) ? 'checked' : '' ?>> Популярный</label><?php endif; ?>
                        <?php if (!empty($hasPhoneSale)): ?><label style="display:flex;align-items:center;gap:.35rem;"><input type="checkbox" name="is_sale" value="1" <?= !empty($phone['is_sale']) ? 'checked' : '' ?>> Акция</label><input type="number" step="0.01" name="old_price" value="<?= htmlspecialchars((string) ($phone['old_price'] ?? '')) ?>" placeholder="Старая цена"><?php endif; ?>
                        <input type="text" name="image" value="<?= htmlspecialchars((string) ($phone['image'] ?? '')) ?>" placeholder="https://...">
                        <button type="submit">Сохранить</button>
                    </form>
                    <a href="<?= htmlspecialchars(url_with_query('/admin/phones', ['delete' => (int) $phone['id']])) ?>" onclick="return confirm('Удалить?')">Удалить</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
