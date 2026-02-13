<?php
session_start();

$config = require __DIR__ . '/../config/config.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (str_starts_with($class, $prefix)) {
        $relative = substr($class, strlen($prefix));
        $path = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($path)) {
            require $path;
        }
    }
});

use App\Database;

$db = Database::connect($config['db']);

function render(string $view, array $data = []): void
{
    extract($data, EXTR_SKIP);
    $viewFile = __DIR__ . '/../templates/' . $view . '.php';
    require __DIR__ . '/../templates/layout/header.php';
    require $viewFile;
    require __DIR__ . '/../templates/layout/footer.php';
}

function app_base_url(): string
{
    global $config;
    return rtrim((string) ($config['app']['base_url'] ?? ''), '/');
}

function use_rewrite(): bool
{
    global $config;
    return (bool) ($config['app']['use_rewrite'] ?? false);
}

function url(string $path = '/'): string
{
    $base = app_base_url();
    $normalized = '/' . ltrim($path, '/');

    if ($normalized === '//') {
        $normalized = '/';
    }

    if (use_rewrite()) {
        return $base . $normalized;
    }

    $entry = $base . '/index.php';
    if ($normalized === '/') {
        return $entry;
    }

    return $entry . '?r=' . rawurlencode($normalized);
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_admin(): bool
{
    return (current_user()['role'] ?? '') === 'admin';
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }

    $value = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $value;
}
