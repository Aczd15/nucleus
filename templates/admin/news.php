<section>
    <h1>Управление новостями</h1>

    <form method="post" action="<?= htmlspecialchars(url('/admin/news')) ?>" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <input type="hidden" name="id" value="0">
        <label>Заголовок <input type="text" name="title" required></label>
        <label>Текст <textarea name="text" required></textarea></label>
        <button type="submit">Добавить новость</button>
    </form>

    <table class="table">
        <thead><tr><th>ID</th><th>Заголовок</th><th>Текст</th><th>Дата</th><th>Действия</th></tr></thead>
        <tbody>
        <?php foreach ($newsItems as $item): ?>
            <tr>
                <td><?= (int) $item['id'] ?></td>
                <td><?= htmlspecialchars($item['title']) ?></td>
                <td><?= htmlspecialchars($item['text']) ?></td>
                <td><?= htmlspecialchars($item['created_at']) ?></td>
                <td>
                    <form method="post" action="<?= htmlspecialchars(url('/admin/news')) ?>" style="display:grid; gap:.4rem; min-width:260px;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                        <input type="text" name="title" value="<?= htmlspecialchars($item['title']) ?>" required>
                        <textarea name="text" required><?= htmlspecialchars($item['text']) ?></textarea>
                        <button type="submit">Сохранить</button>
                    </form>
                    <a href="<?= htmlspecialchars(url_with_query('/admin/news', ['delete' => (int)$item['id']])) ?>" onclick="return confirm('Удалить новость?')">Удалить</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
