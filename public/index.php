<?php

require __DIR__ . '/../src/bootstrap.php';

use App\Auth;

$routeParam = $_GET['r'] ?? null;
$method = $_SERVER['REQUEST_METHOD'];

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

$hasPhoneSpecs = db_has_column('phones', 'specs');
$hasUserPhone = db_has_column('users', 'phone');
$hasUserCity = db_has_column('users', 'city');
$hasUserBirthDate = db_has_column('users', 'birth_date');

switch ($path) {
    case '/':
    case '/index.php':
        $news = [
            ['title' => 'Новая поставка iPhone 16 Pro Max', 'text' => 'В наличии все актуальные цвета и объемы памяти.'],
            ['title' => 'Бесплатная настройка устройства', 'text' => 'Перенесем данные и установим нужные приложения при покупке.'],
            ['title' => 'Расширенная гарантия Nucleus Care', 'text' => 'Дополнительная защита экрана и корпуса до 24 месяцев.'],
        ];
        $phones = db()->query('SELECT * FROM phones ORDER BY created_at DESC LIMIT 6')->fetchAll();
        render('home/index', compact('news', 'phones'));
        break;


    case '/about':
        render('home/about');
        break;

    case '/delivery-payment':
        render('home/delivery');
        break;

    case '/register':
        if ($method === 'GET') {
            $a = random_int(2, 9);
            $b = random_int(1, 9);
            $_SESSION['captcha_answer'] = $a + $b;
            $_SESSION['captcha_label'] = "Сколько будет {$a} + {$b}?";
            render('auth/register', ['errors' => []]);
            break;
        }

        if (!verify_csrf()) {
            flash('error', 'Невалидный CSRF токен.');
            redirect('/register');
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $birthDate = trim($_POST['birth_date'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $captcha = (int) ($_POST['captcha'] ?? 0);
        $errors = [];

        if (mb_strlen($name) < 2) $errors[] = 'Имя должно быть от 2 символов.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Некорректный email.';
        if ($phone !== '' && !preg_match('/^[0-9+\-()\s]{7,20}$/', $phone)) $errors[] = 'Некорректный номер телефона.';
        if ($birthDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthDate)) $errors[] = 'Дата рождения должна быть в формате YYYY-MM-DD.';
        if (strlen($password) < 8) $errors[] = 'Пароль минимум 8 символов.';
        if ($password !== $confirm) $errors[] = 'Пароли не совпадают.';
        if ($captcha !== (int) ($_SESSION['captcha_answer'] ?? -1)) $errors[] = 'Неверная капча.';

        $exists = db()->prepare('SELECT id FROM users WHERE email = :email');
        $exists->execute(['email' => $email]);
        if ($exists->fetch()) $errors[] = 'Пользователь уже существует.';

        if ($errors) {
            $a = random_int(2, 9);
            $b = random_int(1, 9);
            $_SESSION['captcha_answer'] = $a + $b;
            $_SESSION['captcha_label'] = "Сколько будет {$a} + {$b}?";
            render('auth/register', compact('errors'));
            break;
        }

        if ($hasUserPhone && $hasUserCity && $hasUserBirthDate) {
            $stmt = db()->prepare('INSERT INTO users (name, email, phone, city, birth_date, password_hash, role) VALUES (:name,:email,:phone,:city,:birth_date,:password_hash,:role)');
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'phone' => $phone !== '' ? $phone : null,
                'city' => $city !== '' ? $city : null,
                'birth_date' => $birthDate !== '' ? $birthDate : null,
                'password_hash' => Auth::hashPassword($password, $config['app']['password_pepper']),
                'role' => 'user',
            ]);
        } else {
            $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:name,:email,:password_hash,:role)');
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'password_hash' => Auth::hashPassword($password, $config['app']['password_pepper']),
                'role' => 'user',
            ]);
        }

        flash('success', 'Регистрация прошла успешно.');
        redirect('/login');

    case '/login':
        if ($method === 'GET') {
            render('auth/login', ['errors' => []]);
            break;
        }

        if (!verify_csrf()) {
            flash('error', 'Невалидный CSRF токен.');
            redirect('/login');
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors = [];

        $stmt = db()->prepare('SELECT * FROM users WHERE email = :email');
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

        flash('success', 'Вы вошли в аккаунт.');
        redirect('/');

    case '/logout':
        unset($_SESSION['user']);
        flash('success', 'Вы вышли из аккаунта.');
        redirect('/');

    case '/catalog':
        $phones = db()->query('SELECT * FROM phones ORDER BY created_at DESC')->fetchAll();
        render('catalog/index', compact('phones'));
        break;

    case '/product':
        $id = (int) ($_GET['id'] ?? 0);
        $stmt = db()->prepare('SELECT * FROM phones WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $phone = $stmt->fetch();

        if (!$phone) {
            http_response_code(404);
            render('home/404');
            break;
        }

        render('catalog/show', compact('phone'));
        break;

    case '/cart':
        $cartItems = $_SESSION['cart'] ?? [];
        render('catalog/cart', compact('cartItems'));
        break;

    case '/cart/add':
        if ($method !== 'POST' || !verify_csrf()) {
            flash('error', 'Невалидный запрос.');
            redirect('/catalog');
        }

        $id = (int) ($_POST['phone_id'] ?? 0);
        $stmt = db()->prepare('SELECT id, name, price FROM phones WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $phone = $stmt->fetch();

        if (!$phone) {
            flash('error', 'Товар не найден.');
            redirect('/catalog');
        }

        $_SESSION['cart'][$id]['id'] = $phone['id'];
        $_SESSION['cart'][$id]['name'] = $phone['name'];
        $_SESSION['cart'][$id]['price'] = (float) $phone['price'];
        $_SESSION['cart'][$id]['qty'] = ($_SESSION['cart'][$id]['qty'] ?? 0) + 1;

        flash('success', 'Товар добавлен в корзину.');
        redirect('/catalog');

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
            } elseif (isset($_SESSION['cart'][$id])) {
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

        $usersCount = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $phonesCount = (int) db()->query('SELECT COUNT(*) FROM phones')->fetchColumn();
        render('admin/dashboard', compact('usersCount', 'phonesCount'));
        break;

    case '/admin/phones':
        if (!is_admin()) {
            flash('error', 'Доступ запрещен.');
            redirect('/');
        }

        if ($method === 'POST') {
            if (!verify_csrf()) {
                flash('error', 'Невалидный CSRF токен.');
                redirect('/admin/phones');
            }

            $id = (int) ($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $specs = trim($_POST['specs'] ?? '');
            $price = (float) ($_POST['price'] ?? 0);
            $image = trim($_POST['image'] ?? '');

            if ($id > 0) {
                if ($hasPhoneSpecs) {
                    $stmt = db()->prepare('UPDATE phones SET name=:name, description=:description, specs=:specs, price=:price, image=:image WHERE id=:id');
                    $stmt->execute(compact('id', 'name', 'description', 'specs', 'price', 'image'));
                } else {
                    $stmt = db()->prepare('UPDATE phones SET name=:name, description=:description, price=:price, image=:image WHERE id=:id');
                    $stmt->execute(compact('id', 'name', 'description', 'price', 'image'));
                }
                flash('success', 'Телефон обновлен.');
            } else {
                if ($hasPhoneSpecs) {
                    $stmt = db()->prepare('INSERT INTO phones (name, description, specs, price, image) VALUES (:name, :description, :specs, :price, :image)');
                    $stmt->execute(compact('name', 'description', 'specs', 'price', 'image'));
                } else {
                    $stmt = db()->prepare('INSERT INTO phones (name, description, price, image) VALUES (:name, :description, :price, :image)');
                    $stmt->execute(compact('name', 'description', 'price', 'image'));
                }
                flash('success', 'Телефон добавлен.');
            }

            redirect('/admin/phones');
        }

        if (isset($_GET['delete'])) {
            $id = (int) $_GET['delete'];
            $stmt = db()->prepare('DELETE FROM phones WHERE id=:id');
            $stmt->execute(['id' => $id]);
            flash('success', 'Телефон удален.');
            redirect('/admin/phones');
        }

        $phones = db()->query('SELECT * FROM phones ORDER BY created_at DESC')->fetchAll();
        render('admin/phones', compact('phones'));
        break;

    case '/admin/users':
        if (!is_admin()) {
            flash('error', 'Доступ запрещен.');
            redirect('/');
        }

        if ($method === 'POST') {
            if (!verify_csrf()) {
                flash('error', 'Невалидный CSRF токен.');
                redirect('/admin/users');
            }

            $id = (int) ($_POST['id'] ?? 0);
            $role = $_POST['role'] ?? 'user';
            $stmt = db()->prepare('UPDATE users SET role = :role WHERE id = :id');
            $stmt->execute(['id' => $id, 'role' => $role]);
            flash('success', 'Роль обновлена.');
            redirect('/admin/users');
        }

        if (isset($_GET['delete'])) {
            $id = (int) $_GET['delete'];
            if ($id === (int) (current_user()['id'] ?? 0)) {
                flash('error', 'Нельзя удалить текущего администратора.');
                redirect('/admin/users');
            }
            $stmt = db()->prepare('DELETE FROM users WHERE id = :id');
            $stmt->execute(['id' => $id]);
            flash('success', 'Пользователь удален.');
            redirect('/admin/users');
        }

        if ($hasUserPhone && $hasUserCity && $hasUserBirthDate) {
            $users = db()->query('SELECT id, name, email, phone, city, birth_date, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();
        } else {
            $users = db()->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();
        }
        render('admin/users', compact('users'));
        break;

    default:
        http_response_code(404);
        render('home/404');
        break;
}
