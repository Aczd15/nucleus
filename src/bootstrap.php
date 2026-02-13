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

function db(): PDO
{
    global $config;
    static $pdo = null;

    if (!$pdo) {
        $pdo = Database::connect($config['db']);
    }

    return $pdo;
}

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

    $configured = rtrim((string) ($config['app']['base_url'] ?? ''), '/');
    if ($configured !== '') {
        return $configured;
    }

    $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
    $dir = str_replace('\\', '/', dirname($scriptName));
    if ($dir === '/' || $dir === '.') {
        return '';
    }

    return rtrim($dir, '/');
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

    if (use_rewrite()) {
        return $base . $normalized;
    }

    $entry = $base . '/index.php';
    if ($normalized === '/') {
        return $entry;
    }

    return $entry . '?r=' . rawurlencode($normalized);
}

function url_with_query(string $path, array $params = []): string
{
    $base = url($path);
    if (!$params) {
        return $base;
    }

    $separator = str_contains($base, '?') ? '&' : '?';
    return $base . $separator . http_build_query($params);
}

function asset_url(string $path): string
{
    $base = app_base_url();
    $normalized = ltrim($path, '/');

    $scriptFilename = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_FILENAME'] ?? ''));
    $scriptDir = basename(dirname($scriptFilename));

    // If app runs with DocumentRoot=/public -> assets are served from /assets/*
    if ($scriptDir === 'public') {
        return $base . '/assets/' . $normalized;
    }

    // If app runs from repo root/index.php proxy (typical XAMPP htdocs/nucleus)
    return $base . '/public/assets/' . $normalized;
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
