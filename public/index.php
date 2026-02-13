<?php

require __DIR__ . '/../src/bootstrap.php';

$routeParam = $_GET['r'] ?? null;

if (is_string($routeParam) && $routeParam !== '') {
    $path = '/' . ltrim($routeParam, '/');
} else {
    $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
    $baseUrl = app_base_url();

    if ($baseUrl !== '' && str_starts_with($requestPath, $baseUrl)) {
        $path = substr($requestPath, strlen($baseUrl));
        $path = $path === '' ? '/' : $path;
    } else {
        $path = $requestPath;
    }
}

$method = $_SERVER['REQUEST_METHOD'];

use App\Auth;

switch ($path) {
    case '/':
        $news = [
            ['title' => 'Nucleus запускает линейку AI Phone X', 'text' => 'Умные устройства с лучшей автономностью и камерой 200 МП.'],
            ['title' => 'Открытие нового шоурума', 'text' => 'Теперь вы можете протестировать устройства Nucleus в офлайн-шоуруме.'],
            ['title' => 'Сезонная скидка до 25%', 'text' => 'Специальное предложение на флагманы и аксессуары до конца месяца.'],
        ];

        $phones = $db->query('SELECT * FROM phones ORDER BY created_at DESC LIMIT 6')->fetchAll();
        render('home/index', compact('news', 'phones'));
        break;

    case '/register':
        if ($method === 'GET') {
            $_SESSION['captcha_answer'] = random_int(10, 99);
            render('auth/register', ['errors' => []]);
            break;
        }

        if (!verify_csrf()) {
            flash('error', 'CSRF токен невалиден.');
            redirect('/register');
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $captcha = trim($_POST['captcha'] ?? '');
        $errors = [];

        if ($name === '' || mb_strlen($name) < 2) {
            $errors[] = 'Имя должно содержать минимум 2 символа.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Введите корректный email.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Пароль должен быть минимум 8 символов.';
        }
        if ($password !== $confirmPassword) {
            $errors[] = 'Подтверждение пароля не совпадает.';
        }
        if ((int) $captcha !== (int) ($_SESSION['captcha_answer'] ?? -1)) {
            $errors[] = 'Неверная капча.';
        }

        $exists = $db->prepare('SELECT id FROM users WHERE email = :email');
        $exists->execute(['email' => $email]);
        if ($exists->fetch()) {
            $errors[] = 'Пользователь с таким email уже существует.';
        }

        if ($errors) {
            $_SESSION['captcha_answer'] = random_int(10, 99);
            render('auth/register', compact('errors'));
            break;
        }

        $stmt = $db->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)');
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => Auth::hashPassword($password, $config['app']['password_pepper']),
            'role' => 'user',
        ]);

        flash('success', 'Регистрация успешна. Теперь войдите в аккаунт.');
        redirect('/login');

    case '/login':
        if ($method === 'GET') {
            render('auth/login', ['errors' => []]);
            break;
        }

        if (!verify_csrf()) {
            flash('error', 'CSRF токен невалиден.');
            redirect('/login');
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors = [];

        $stmt = $db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !Auth::verifyPassword($password, $user['password_hash'], $config['app']['password_pepper'])) {
            $errors[] = 'Неверный email или пароль.';
        }

        if ($errors) {
            render('auth/login', compact('errors'));
            break;
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];

        flash('success', 'Вы успешно авторизованы.');
        redirect('/');

    case '/logout':
        unset($_SESSION['user']);
        flash('success', 'Вы вышли из аккаунта.');
        redirect('/');

    case '/catalog':
        $phones = $db->query('SELECT * FROM phones ORDER BY created_at DESC')->fetchAll();
        render('catalog/index', compact('phones'));
        break;

    case '/cart/add':
        if ($method !== 'POST' || !verify_csrf()) {
            flash('error', 'Невалидный запрос.');
            redirect('/catalog');
        }

        $phoneId = (int) ($_POST['phone_id'] ?? 0);
        $stmt = $db->prepare('SELECT id, name, price FROM phones WHERE id = :id');
        $stmt->execute(['id' => $phoneId]);
        $phone = $stmt->fetch();

        if (!$phone) {
            flash('error', 'Товар не найден.');
            redirect('/catalog');
        }

        $_SESSION['cart'][$phoneId]['id'] = $phone['id'];
        $_SESSION['cart'][$phoneId]['name'] = $phone['name'];
        $_SESSION['cart'][$phoneId]['price'] = (float) $phone['price'];
        $_SESSION['cart'][$phoneId]['qty'] = ($_SESSION['cart'][$phoneId]['qty'] ?? 0) + 1;

        flash('success', 'Товар добавлен в корзину.');
        redirect('/catalog');

    case '/cart':
        $cartItems = $_SESSION['cart'] ?? [];
        render('catalog/cart', compact('cartItems'));
        break;

    case '/cart/update':
        if ($method !== 'POST' || !verify_csrf()) {
            flash('error', 'Невалидный запрос.');
            redirect('/cart');
        }

        foreach ($_POST['qty'] ?? [] as $id => $qty) {
            $id = (int) $id;
            $qty = max(0, (int) $qty);
            if ($qty === 0) {
                unset($_SESSION['cart'][$id]);
                continue;
            }
            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id]['qty'] = $qty;
            }
        }

        flash('success', 'Корзина обновлена.');
        redirect('/cart');

    case '/admin':
        if (!is_admin()) {
            flash('error', 'Доступ запрещен.');
            redirect('/');
        }

        $usersCount = (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $phonesCount = (int) $db->query('SELECT COUNT(*) FROM phones')->fetchColumn();
        render('admin/dashboard', compact('usersCount', 'phonesCount'));
        break;

    case '/admin/phones':
        if (!is_admin()) {
            flash('error', 'Доступ запрещен.');
            redirect('/');
        }

        if ($method === 'POST') {
            if (!verify_csrf()) {
                flash('error', 'CSRF токен невалиден.');
                redirect('/admin/phones');
            }

            $id = (int) ($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = (float) ($_POST['price'] ?? 0);
            $image = trim($_POST['image'] ?? '');

            if ($id > 0) {
                $stmt = $db->prepare('UPDATE phones SET name=:name, description=:description, price=:price, image=:image WHERE id=:id');
                $stmt->execute(compact('id', 'name', 'description', 'price', 'image'));
                flash('success', 'Телефон обновлен.');
            } else {
                $stmt = $db->prepare('INSERT INTO phones (name, description, price, image) VALUES (:name, :description, :price, :image)');
                $stmt->execute(compact('name', 'description', 'price', 'image'));
                flash('success', 'Телефон добавлен.');
            }

            redirect('/admin/phones');
        }

        if (isset($_GET['delete'])) {
            $id = (int) $_GET['delete'];
            $stmt = $db->prepare('DELETE FROM phones WHERE id=:id');
            $stmt->execute(['id' => $id]);
            flash('success', 'Телефон удален.');
            redirect('/admin/phones');
        }

        $phones = $db->query('SELECT * FROM phones ORDER BY created_at DESC')->fetchAll();
        render('admin/phones', compact('phones'));
        break;

    case '/admin/users':
        if (!is_admin()) {
            flash('error', 'Доступ запрещен.');
            redirect('/');
        }

        if ($method === 'POST') {
            if (!verify_csrf()) {
                flash('error', 'CSRF токен невалиден.');
                redirect('/admin/users');
            }

            $id = (int) ($_POST['id'] ?? 0);
            $role = $_POST['role'] ?? 'user';
            $stmt = $db->prepare('UPDATE users SET role = :role WHERE id = :id');
            $stmt->execute(['id' => $id, 'role' => $role]);
            flash('success', 'Роль пользователя обновлена.');
            redirect('/admin/users');
        }

        if (isset($_GET['delete'])) {
            $id = (int) $_GET['delete'];
            if ($id === (int) current_user()['id']) {
                flash('error', 'Нельзя удалить текущего администратора.');
                redirect('/admin/users');
            }
            $stmt = $db->prepare('DELETE FROM users WHERE id=:id');
            $stmt->execute(['id' => $id]);
            flash('success', 'Пользователь удален.');
            redirect('/admin/users');
        }

        $users = $db->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();
        render('admin/users', compact('users'));
        break;

    default:
        http_response_code(404);
        render('home/404');
}
