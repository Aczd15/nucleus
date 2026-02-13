# Nucleus — магазин мобильных устройств (PHP)

Готовый PHP-проект с:
- главной страницей и новостями компании Nucleus;
- регистрацией (валидация, подтверждение пароля, капча, соль+хеширование);
- авторизацией;
- админ-панелью для управления телефонами и пользователями;
- рабочей корзиной;
- современным минималистичным дизайном.

## Запуск
1. Создайте БД:
```bash
mysql -u root -p < sql/schema.sql
```
2. Настройте `config/config.php` (dsn, user, password, pepper).
3. Создайте администратора:
```bash
php scripts/create_admin.php "Admin" "admin@nucleus.local" "StrongPass123"
```
4. Запустите встроенный сервер:
```bash
php -S 0.0.0.0:8080 router.php
```

## Основные URL
- `/` — главная
- `/register` — регистрация
- `/login` — вход
- `/catalog` — каталог
- `/cart` — корзина
- `/admin` — админ-панель (только admin)

## Безопасность
- CSRF-защита для POST-форм.
- Пароли: `hash_hmac(sha256 + pepper)` + `password_hash/password_verify`.
