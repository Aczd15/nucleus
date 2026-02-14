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
$hasPhonePopular = ensure_phone_popular_column();
$hasPhoneSale = ensure_phone_sale_columns();
ensure_reviews_table();
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
        if ($hasPhonePopular) {
            $phones = db()->query('SELECT * FROM phones WHERE is_popular = 1 ORDER BY created_at DESC LIMIT 6')->fetchAll();
            if (!$phones) {
                $phones = db()->query('SELECT * FROM phones ORDER BY created_at DESC LIMIT 6')->fetchAll();
            }
        } else {
            $phones = db()->query('SELECT * FROM phones ORDER BY created_at DESC LIMIT 6')->fetchAll();
        }
        render('home/index', compact('news', 'phones'));
        break;


    case '/about':
        render('home/about');
        break;

    case '/promotions':
        $salePhones = $hasPhoneSale
            ? db()->query('SELECT * FROM phones WHERE is_sale = 1 ORDER BY created_at DESC')->fetchAll()
            : [];
        render('home/promotions', compact('salePhones'));
        break;

    case '/delivery-payment':
        render('home/delivery');
        break;


    case '/wow':
        $highlights = [
            ['title' => 'Hyper Charge Zone', 'text' => 'Демо-стенд, где вы сравниваете скорость зарядки разных флагманов в реальном времени.'],
            ['title' => 'Night Camera Battle', 'text' => 'Сравнение ночной съемки в одинаковых условиях освещения.'],
            ['title' => 'Pro Creator Setup', 'text' => 'Готовые наборы смартфон + микрофон + стабилизатор для контент-мейкеров.'],
        ];
        render('home/wow', compact('highlights'));
        break;

    case '/compare-lab':
        $allPhones = db()->query('SELECT id, name, specs, price, image FROM phones ORDER BY created_at DESC')->fetchAll();

        $selectedIds = array_values(array_unique(array_map('intval', (array) ($_GET['ids'] ?? []))));
        $selectedIds = array_values(array_filter($selectedIds, static fn (int $id): bool => $id > 0));

        if (!$selectedIds && count($allPhones) >= 2) {
            $selectedIds = [(int) $allPhones[0]['id'], (int) $allPhones[1]['id']];
        }

        $phonesById = [];
        foreach ($allPhones as $phone) {
            $phonesById[(int) $phone['id']] = $phone;
        }

        $comparePhones = [];
        foreach ($selectedIds as $id) {
            if (isset($phonesById[$id])) {
                $comparePhones[] = $phonesById[$id];
            }
        }

        $specLabels = [];
        $specMatrix = [];
        foreach ($comparePhones as $phone) {
            $phoneId = (int) $phone['id'];
            $specText = (string) ($phone['specs'] ?? '');
            foreach (array_filter(array_map('trim', explode('|', $specText))) as $chunk) {
                if (str_contains($chunk, ':')) {
                    [$label, $value] = array_map('trim', explode(':', $chunk, 2));
                } else {
                    $label = 'Прочее';
                    $value = $chunk;
                }
                if ($label === '') {
                    $label = 'Прочее';
                }
                $specLabels[$label] = true;
                $specMatrix[$label][$phoneId] = $value;
            }
        }

        $specLabels = array_keys($specLabels);

        $priceChart = [];
        if ($comparePhones) {
            $maxPrice = max(array_map(static fn (array $phone): float => (float) $phone['price'], $comparePhones));
            foreach ($comparePhones as $phone) {
                $price = (float) $phone['price'];
                $priceChart[] = [
                    'id' => (int) $phone['id'],
                    'name' => (string) $phone['name'],
                    'price' => $price,
                    'percent' => $maxPrice > 0 ? max(8.0, round(($price / $maxPrice) * 100, 2)) : 0.0,
                ];
            }
        }

        render('home/compare', compact('allPhones', 'comparePhones', 'selectedIds', 'specLabels', 'specMatrix', 'priceChart'));
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

    case '/profile':
        if (!current_user()) {
            flash('error', 'Сначала войдите в аккаунт.');
            redirect('/login');
        }

        $userId = (int) current_user()['id'];

        if ($method === 'POST') {
            if (!verify_csrf()) {
                flash('error', 'Невалидный CSRF токен.');
                redirect('/profile');
            }

            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $birthDate = trim($_POST['birth_date'] ?? '');

            if ($name === '' || mb_strlen($name) < 2) {
                flash('error', 'Имя должно быть не короче 2 символов.');
                redirect('/profile');
            }

            if ($hasUserPhone && $hasUserCity && $hasUserBirthDate) {
                $stmt = db()->prepare('UPDATE users SET name=:name, phone=:phone, city=:city, birth_date=:birth_date WHERE id=:id');
                $stmt->execute([
                    'id' => $userId,
                    'name' => $name,
                    'phone' => $phone !== '' ? $phone : null,
                    'city' => $city !== '' ? $city : null,
                    'birth_date' => $birthDate !== '' ? $birthDate : null,
                ]);
            } else {
                $stmt = db()->prepare('UPDATE users SET name=:name WHERE id=:id');
                $stmt->execute(['id' => $userId, 'name' => $name]);
            }

            $_SESSION['user']['name'] = $name;
            flash('success', 'Данные профиля обновлены.');
            redirect('/profile');
        }

        $select = $hasUserPhone && $hasUserCity && $hasUserBirthDate
            ? 'SELECT id,name,email,phone,city,birth_date,created_at FROM users WHERE id=:id'
            : 'SELECT id,name,email,created_at FROM users WHERE id=:id';

        $stmt = db()->prepare($select);
        $stmt->execute(['id' => $userId]);
        $profile = $stmt->fetch();
        render('profile/index', compact('profile'));
        break;

    case '/catalog':
        $q = trim((string) ($_GET['q'] ?? ''));
        $minPrice = (float) ($_GET['min_price'] ?? 0);
        $maxPrice = (float) ($_GET['max_price'] ?? 0);
        $sort = (string) ($_GET['sort'] ?? 'new');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 6;

        $orderBy = match ($sort) {
            'price_asc' => 'price ASC, id DESC',
            'price_desc' => 'price DESC, id DESC',
            'name_asc' => 'name ASC',
            default => 'created_at DESC',
        };

        $where = [];
        $params = [];

        if ($q !== '') {
            $where[] = '(name LIKE :q OR description LIKE :q)';
            $params['q'] = '%' . $q . '%';
        }

        if ($minPrice > 0) {
            $where[] = 'price >= :min_price';
            $params['min_price'] = $minPrice;
        }

        if ($maxPrice > 0) {
            $where[] = 'price <= :max_price';
            $params['max_price'] = $maxPrice;
        }

        $whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

        $countStmt = db()->prepare('SELECT COUNT(*) FROM phones' . $whereSql);
        $countStmt->execute($params);
        $totalPhones = (int) $countStmt->fetchColumn();

        $totalPages = max(1, (int) ceil($totalPhones / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;

        $stmt = db()->prepare('SELECT * FROM phones' . $whereSql . ' ORDER BY ' . $orderBy . ' LIMIT :limit OFFSET :offset');
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $phones = $stmt->fetchAll();

        $wishlistIds = [];
        if (current_user()) {
            ensure_wishlist_table();
            $wStmt = db()->prepare('SELECT phone_id FROM wishlists WHERE user_id = :user_id');
            $wStmt->execute(['user_id' => (int) current_user()['id']]);
            $wishlistIds = array_map('intval', array_column($wStmt->fetchAll(), 'phone_id'));
        }

        render('catalog/index', compact('phones', 'wishlistIds', 'q', 'minPrice', 'maxPrice', 'sort', 'page', 'totalPages', 'totalPhones'));
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

        $similarStmt = db()->prepare('SELECT * FROM phones WHERE id != :id ORDER BY ABS(price - :price), created_at DESC LIMIT 4');
        $similarStmt->execute(['id' => $id, 'price' => (float) $phone['price']]);
        $similarPhones = $similarStmt->fetchAll();

        $reviewsStmt = db()->prepare('SELECT r.rating, r.comment, r.created_at, u.name AS user_name FROM reviews r JOIN users u ON u.id = r.user_id WHERE r.phone_id = :phone_id ORDER BY r.created_at DESC');
        $reviewsStmt->execute(['phone_id' => $id]);
        $reviews = $reviewsStmt->fetchAll();

        $ratingStmt = db()->prepare('SELECT COALESCE(AVG(rating),0) AS avg_rating, COUNT(*) AS total_reviews FROM reviews WHERE phone_id = :phone_id');
        $ratingStmt->execute(['phone_id' => $id]);
        $rating = $ratingStmt->fetch();

        render('catalog/show', compact('phone', 'similarPhones', 'reviews', 'rating'));
        break;

    case '/product/review':
        if (!current_user()) {
            flash('error', 'Сначала войдите в аккаунт.');
            redirect('/login');
        }

        if ($method !== 'POST' || !verify_csrf()) {
            flash('error', 'Невалидный запрос.');
            redirect('/catalog');
        }

        $phoneId = (int) ($_POST['phone_id'] ?? 0);
        $ratingValue = max(1, min(5, (int) ($_POST['rating'] ?? 0)));
        $comment = trim((string) ($_POST['comment'] ?? ''));

        if ($comment === '') {
            flash('error', 'Напишите отзыв.');
            redirect(url_with_query('/product', ['id' => $phoneId]));
        }

        $phoneExists = db()->prepare('SELECT id FROM phones WHERE id = :id');
        $phoneExists->execute(['id' => $phoneId]);
        if (!$phoneExists->fetch()) {
            flash('error', 'Товар не найден.');
            redirect('/catalog');
        }

        $insReview = db()->prepare('INSERT INTO reviews (phone_id, user_id, rating, comment) VALUES (:phone_id, :user_id, :rating, :comment)');
        $insReview->execute([
            'phone_id' => $phoneId,
            'user_id' => (int) current_user()['id'],
            'rating' => $ratingValue,
            'comment' => $comment,
        ]);

        flash('success', 'Спасибо за отзыв!');
        redirect(url_with_query('/product', ['id' => $phoneId]));

    case '/wishlist':
        if (!current_user()) {
            flash('error', 'Сначала войдите в аккаунт.');
            redirect('/login');
        }

        ensure_wishlist_table();

        $stmt = db()->prepare('SELECT p.* FROM wishlists w JOIN phones p ON p.id = w.phone_id WHERE w.user_id = :user_id ORDER BY w.created_at DESC');
        $stmt->execute(['user_id' => (int) current_user()['id']]);
        $phones = $stmt->fetchAll();

        render('catalog/wishlist', compact('phones'));
        break;

    case '/wishlist/toggle':
        if ($method !== 'POST' || !verify_csrf()) {
            flash('error', 'Невалидный запрос.');
            redirect('/catalog');
        }

        if (!current_user()) {
            flash('error', 'Сначала войдите в аккаунт.');
            redirect('/login');
        }

        ensure_wishlist_table();

        $phoneId = (int) ($_POST['phone_id'] ?? 0);
        $redirectPath = (string) ($_POST['redirect_path'] ?? '/catalog');
        if ($redirectPath === '') {
            $redirectPath = '/catalog';
        }

        $existsStmt = db()->prepare('SELECT id FROM phones WHERE id = :id');
        $existsStmt->execute(['id' => $phoneId]);
        if (!$existsStmt->fetch()) {
            flash('error', 'Товар не найден.');
            redirect('/catalog');
        }

        $isFavStmt = db()->prepare('SELECT id FROM wishlists WHERE user_id = :user_id AND phone_id = :phone_id');
        $isFavStmt->execute(['user_id' => (int) current_user()['id'], 'phone_id' => $phoneId]);
        $isFav = (bool) $isFavStmt->fetch();

        if ($isFav) {
            $del = db()->prepare('DELETE FROM wishlists WHERE user_id = :user_id AND phone_id = :phone_id');
            $del->execute(['user_id' => (int) current_user()['id'], 'phone_id' => $phoneId]);
            flash('success', 'Товар удалён из избранного.');
        } else {
            $ins = db()->prepare('INSERT INTO wishlists (user_id, phone_id) VALUES (:user_id, :phone_id)');
            $ins->execute(['user_id' => (int) current_user()['id'], 'phone_id' => $phoneId]);
            flash('success', 'Товар добавлен в избранное.');
        }

        redirect($redirectPath);

    case '/cart':
        $cartItems = $_SESSION['cart'] ?? [];
        render('catalog/cart', compact('cartItems'));
        break;

    case '/cart/add':
        if ($method !== 'POST' || !verify_csrf()) {
            flash('error', 'Невалидный запрос.');
            redirect('/catalog');
        }

        if (!current_user()) {
            flash('error', 'Для добавления товара в корзину нужно войти в аккаунт.');
            redirect('/login');
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

    case '/order/checkout':
        if (!current_user()) {
            flash('error', 'Сначала войдите в аккаунт.');
            redirect('/login');
        }

        if ($method !== 'POST' || !verify_csrf()) {
            flash('error', 'Невалидный запрос.');
            redirect('/cart');
        }

        $cartItems = $_SESSION['cart'] ?? [];
        if (!$cartItems) {
            flash('error', 'Корзина пуста.');
            redirect('/cart');
        }

        ensure_orders_tables();
        ensure_order_history_table();

        $total = 0;
        foreach ($cartItems as $item) {
            $total += ((float) $item['price']) * ((int) $item['qty']);
        }

        $pdo = db();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO orders (user_id, total_amount, status) VALUES (:user_id, :total_amount, :status)');
            $stmt->execute([
                'user_id' => (int) current_user()['id'],
                'total_amount' => $total,
                'status' => 'payment_pending',
            ]);

            $orderId = (int) $pdo->lastInsertId();
            $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, phone_id, product_name, unit_price, qty) VALUES (:order_id, :phone_id, :product_name, :unit_price, :qty)');
            foreach ($cartItems as $item) {
                $itemStmt->execute([
                    'order_id' => $orderId,
                    'phone_id' => (int) $item['id'],
                    'product_name' => $item['name'],
                    'unit_price' => (float) $item['price'],
                    'qty' => (int) $item['qty'],
                ]);
            }

            $histStmt = $pdo->prepare('INSERT INTO order_status_history (order_id, status, comment) VALUES (:order_id, :status, :comment)');
            $histStmt->execute([
                'order_id' => $orderId,
                'status' => 'payment_pending',
                'comment' => 'Заказ создан пользователем',
            ]);

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            flash('error', 'Не удалось оформить заказ: ' . $e->getMessage());
            redirect('/cart');
        }

        unset($_SESSION['cart']);
        flash('success', 'Заказ успешно оформлен.');
        redirect('/orders');

    case '/order/pay':
        if (!current_user()) {
            flash('error', 'Сначала войдите в аккаунт.');
            redirect('/login');
        }

        if ($method !== 'POST' || !verify_csrf()) {
            flash('error', 'Невалидный запрос.');
            redirect('/orders');
        }

        ensure_orders_tables();
        ensure_order_history_table();

        $orderId = (int) ($_POST['order_id'] ?? 0);

        $orderStmt = db()->prepare('SELECT id, status FROM orders WHERE id = :id AND user_id = :user_id');
        $orderStmt->execute(['id' => $orderId, 'user_id' => (int) current_user()['id']]);
        $order = $orderStmt->fetch();
        if (!$order) {
            flash('error', 'Заказ не найден.');
            redirect('/orders');
        }

        if (!in_array((string) $order['status'], ['new', 'payment_pending'], true)) {
            flash('error', 'Этот заказ нельзя оплатить.');
            redirect('/orders');
        }

        $upd = db()->prepare('UPDATE orders SET status = :status WHERE id = :id');
        $upd->execute(['status' => 'paid', 'id' => $orderId]);

        $hist = db()->prepare('INSERT INTO order_status_history (order_id, status, comment) VALUES (:order_id, :status, :comment)');
        $hist->execute(['order_id' => $orderId, 'status' => 'paid', 'comment' => 'Оплата подтверждена (демо)']);

        flash('success', 'Оплата прошла успешно.');
        redirect('/orders');

    case '/orders/cancel':
        if (!current_user()) {
            flash('error', 'Сначала войдите в аккаунт.');
            redirect('/login');
        }

        if ($method !== 'POST' || !verify_csrf()) {
            flash('error', 'Невалидный запрос.');
            redirect('/orders');
        }

        ensure_orders_tables();
        ensure_order_history_table();

        $orderId = (int) ($_POST['order_id'] ?? 0);
        $reason = trim((string) ($_POST['reason'] ?? ''));

        if ($reason === '') {
            flash('error', 'Укажите причину отмены заказа.');
            redirect('/orders');
        }

        $orderStmt = db()->prepare('SELECT id, status FROM orders WHERE id = :id AND user_id = :user_id');
        $orderStmt->execute([
            'id' => $orderId,
            'user_id' => (int) current_user()['id'],
        ]);
        $order = $orderStmt->fetch();

        if (!$order) {
            flash('error', 'Заказ не найден.');
            redirect('/orders');
        }

        if (in_array((string) $order['status'], ['delivered', 'cancelled'], true)) {
            flash('error', 'Этот заказ уже нельзя отменить.');
            redirect('/orders');
        }

        $upd = db()->prepare('UPDATE orders SET status = :status WHERE id = :id');
        $upd->execute(['status' => 'cancelled', 'id' => $orderId]);

        $hist = db()->prepare('INSERT INTO order_status_history (order_id, status, comment) VALUES (:order_id, :status, :comment)');
        $hist->execute([
            'order_id' => $orderId,
            'status' => 'cancelled',
            'comment' => 'Отмена пользователем: ' . $reason,
        ]);

        flash('success', 'Заказ отменен.');
        redirect('/orders');

    case '/orders':
        if (!current_user()) {
            flash('error', 'Сначала войдите в аккаунт.');
            redirect('/login');
        }

        ensure_orders_tables();

        $ordersStmt = db()->prepare('SELECT id, total_amount, status, created_at FROM orders WHERE user_id = :user_id ORDER BY created_at DESC');
        $ordersStmt->execute(['user_id' => (int) current_user()['id']]);
        $orders = $ordersStmt->fetchAll();

        ensure_order_history_table();

        $itemsStmt = db()->prepare('SELECT order_id, product_name, unit_price, qty FROM order_items WHERE order_id = :order_id');
        $historyStmt = db()->prepare('SELECT status, comment, created_at FROM order_status_history WHERE order_id = :order_id ORDER BY created_at ASC');
        foreach ($orders as &$order) {
            $itemsStmt->execute(['order_id' => (int) $order['id']]);
            $order['items'] = $itemsStmt->fetchAll();
            $historyStmt->execute(['order_id' => (int) $order['id']]);
            $order['history'] = $historyStmt->fetchAll();
        }
        unset($order);

        render('profile/orders', compact('orders'));
        break;

    case '/admin':
        if (!is_admin()) {
            flash('error', 'Доступ запрещен.');
            redirect('/');
        }

        ensure_orders_tables();
        $usersCount = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $phonesCount = (int) db()->query('SELECT COUNT(*) FROM phones')->fetchColumn();
        $ordersCount = (int) db()->query('SELECT COUNT(*) FROM orders')->fetchColumn();
        render('admin/dashboard', compact('usersCount', 'phonesCount', 'ordersCount'));
        break;

    case '/admin/orders':
        if (!is_admin()) {
            flash('error', 'Доступ запрещен.');
            redirect('/');
        }

        ensure_orders_tables();
        ensure_order_history_table();

        $allowedStatuses = ['new', 'payment_pending', 'paid', 'shipped', 'delivered', 'cancelled'];

        if ($method === 'POST') {
            if (!verify_csrf()) {
                flash('error', 'Невалидный CSRF токен.');
                redirect('/admin/orders');
            }

            $orderId = (int) ($_POST['order_id'] ?? 0);
            $status = (string) ($_POST['status'] ?? 'new');
            $cancelReason = trim((string) ($_POST['cancel_reason'] ?? ''));
            if (!in_array($status, $allowedStatuses, true)) {
                flash('error', 'Некорректный статус.');
                redirect('/admin/orders');
            }

            if ($status === 'cancelled' && $cancelReason === '') {
                flash('error', 'Для отмены заказа укажите причину отказа.');
                redirect('/admin/orders');
            }

            $updateStmt = db()->prepare('UPDATE orders SET status = :status WHERE id = :id');
            $updateStmt->execute(['status' => $status, 'id' => $orderId]);

            $comment = $status === 'cancelled'
                ? ('Отменено администратором. Причина: ' . $cancelReason)
                : 'Статус обновлен администратором';

            $histStmt = db()->prepare('INSERT INTO order_status_history (order_id, status, comment) VALUES (:order_id, :status, :comment)');
            $histStmt->execute([
                'order_id' => $orderId,
                'status' => $status,
                'comment' => $comment,
            ]);

            flash('success', 'Статус заказа обновлен.');
            redirect('/admin/orders');
        }

        $orders = db()->query('SELECT o.id, o.user_id, o.total_amount, o.status, o.created_at, u.name AS user_name, u.email AS user_email FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC')->fetchAll();
        render('admin/orders', compact('orders', 'allowedStatuses'));
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
            $isPopular = isset($_POST['is_popular']) ? 1 : 0;
            $image = trim($_POST['image'] ?? '');
            $existingImage = trim($_POST['existing_image'] ?? '');

            if ($image === '' && $existingImage !== '') {
                $image = $existingImage;
            }

            if (isset($_FILES['image_file']) && is_array($_FILES['image_file']) && (int) ($_FILES['image_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                if ((int) $_FILES['image_file']['error'] !== UPLOAD_ERR_OK) {
                    flash('error', 'Ошибка загрузки файла изображения.');
                    redirect('/admin/phones');
                }

                $tmp = (string) $_FILES['image_file']['tmp_name'];
                $originalName = (string) ($_FILES['image_file']['name'] ?? '');
                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                if (!in_array($ext, $allowed, true)) {
                    flash('error', 'Разрешены только изображения: jpg, jpeg, png, webp, gif.');
                    redirect('/admin/phones');
                }

                $uploadDir = __DIR__ . '/uploads';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileName = 'phone_' . bin2hex(random_bytes(8)) . '.' . $ext;
                $dest = $uploadDir . '/' . $fileName;

                if (!normalize_uploaded_image($tmp, $dest, $ext, 1280, 1280)) {
                    flash('error', 'Не удалось обработать и сохранить изображение.');
                    redirect('/admin/phones');
                }

                $image = uploads_base_url() . '/' . $fileName;
            }

            $isSale = isset($_POST['is_sale']) ? 1 : 0;
            $oldPrice = isset($_POST['old_price']) && $_POST['old_price'] !== '' ? (float) $_POST['old_price'] : null;

            $fields = [
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'image' => $image,
            ];
            if ($hasPhoneSpecs) {
                $fields['specs'] = $specs;
            }
            if ($hasPhonePopular) {
                $fields['is_popular'] = $isPopular;
            }
            if ($hasPhoneSale) {
                $fields['is_sale'] = $isSale;
                $fields['old_price'] = $isSale ? ($oldPrice ?: max($price, 0.0)) : null;
            }

            if ($id > 0) {
                $set = [];
                foreach (array_keys($fields) as $col) {
                    $set[] = $col . '=:' . $col;
                }
                $sql = 'UPDATE phones SET ' . implode(', ', $set) . ' WHERE id=:id';
                $stmt = db()->prepare($sql);
                $fields['id'] = $id;
                $stmt->execute($fields);
                flash('success', 'Телефон обновлен.');
            } else {
                $columns = implode(', ', array_keys($fields));
                $placeholders = ':' . implode(', :', array_keys($fields));
                $sql = 'INSERT INTO phones (' . $columns . ') VALUES (' . $placeholders . ')';
                $stmt = db()->prepare($sql);
                $stmt->execute($fields);
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
        render('admin/phones', compact('phones', 'hasPhonePopular', 'hasPhoneSale'));
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
