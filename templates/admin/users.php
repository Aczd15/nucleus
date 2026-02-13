<section>
    <h1>Управление пользователями</h1>
    <table class="table">
        <thead><tr><th>ID</th><th>Имя</th><th>Email</th><th>Телефон</th><th>Город</th><th>Роль</th><th>Дата</th><th>Действие</th></tr></thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= (int) $user['id'] ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars((string) ($user['phone'] ?? '')) ?></td>
                <td><?= htmlspecialchars((string) ($user['city'] ?? '')) ?></td>
                <td>
                    <form method="post" action="<?= htmlspecialchars(url('/admin/users')) ?>" class="inline">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                        <select name="role">
                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>user</option>
                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
                        </select>
                        <button type="submit">Сменить</button>
                    </form>
                </td>
                <td><?= htmlspecialchars($user['created_at']) ?></td>
                <td><a href="<?= htmlspecialchars(url('/admin/users')) ?>?delete=<?= (int) $user['id'] ?>" onclick="return confirm('Удалить пользователя?')">Удалить</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
