# Nucleus — магазин мобильных устройств (PHP)

Готовый PHP-проект с:
- главной страницей и новостями компании Nucleus;
- регистрацией (валидация, подтверждение пароля, капча, соль+хеширование);
- авторизацией;
- админ-панелью для управления телефонами и пользователями;
- рабочей корзиной;
- современным минималистичным дизайном.

## Как создать базу данных (пошагово)

### 1) Установите MySQL/MariaDB и создайте пользователя
Если у вас уже есть `root` и пароль — можно использовать их.

### 2) Проверьте настройки подключения
Откройте `config/config.php` и проверьте:
- `dsn` (host, port, dbname)
- `user`
- `password`

### 3) Инициализируйте БД (выберите один способ)

#### Способ A (рекомендуется): через MySQL CLI
```bash
mysql -u root -p < sql/schema.sql
```

#### Способ B: через встроенный PHP-скрипт
```bash
php scripts/init_db.php
```
Скрипт подключится к MySQL, выполнит `sql/schema.sql` и создаст:
- БД `nucleus_store`
- таблицы `users`, `phones`
- тестовые товары.

#### Способ C: через phpMyAdmin
1. Откройте phpMyAdmin.
2. Вкладка **Import**.
3. Выберите файл `sql/schema.sql`.
4. Нажмите **Go**.


### Apache (XAMPP/OpenServer) — если видите "Not Found"
Проект теперь поддерживает режим **без rewrite** (по умолчанию), поэтому должен открываться даже если `mod_rewrite` выключен.

1. Если проект лежит в `htdocs/nucleus`, откройте:
   - `http://localhost/nucleus/index.php`
2. В `config/config.php` установите:
   - `base_url` => `/nucleus`
   - `use_rewrite` => `false` (по умолчанию)
3. В этом режиме ссылки работают как:
   - `/nucleus/index.php?r=/catalog`
4. Если хотите красивые URL (`/catalog`), включите rewrite:
   - `use_rewrite` => `true`
   - включите `mod_rewrite` и `AllowOverride All`.

### 4) Создайте администратора
```bash
php scripts/create_admin.php "Admin" "admin@nucleus.local" "StrongPass123"
```

### 5) Запустите проект
```bash
php -S 0.0.0.0:8080 router.php
```

## Основные URL
- без rewrite: `/index.php?r=/` , `/index.php?r=/catalog` и т.д.
- с rewrite: `/`, `/catalog`, `/cart`, `/admin`

## Безопасность
- CSRF-защита для POST-форм.
- Пароли: `hash_hmac(sha256 + pepper)` + `password_hash/password_verify`.
