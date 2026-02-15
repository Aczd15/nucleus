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

function db_has_column(string $table, string $column): bool
{
    static $cache = [];
    $key = $table . '.' . $column;

    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    $stmt = db()->prepare("SHOW COLUMNS FROM `{$table}` LIKE :column");
    $stmt->execute(['column' => $column]);
    $cache[$key] = (bool) $stmt->fetch();

    return $cache[$key];
}

function ensure_orders_tables(): void
{
    db()->exec('CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status VARCHAR(50) NOT NULL DEFAULT "new",
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    db()->exec('CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        phone_id INT NOT NULL,
        product_name VARCHAR(180) NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL,
        qty INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (order_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
}


function ensure_order_history_table(): void
{
    db()->exec('CREATE TABLE IF NOT EXISTS order_status_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        status VARCHAR(50) NOT NULL,
        comment VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (order_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
}


function ensure_news_table(): void
{
    db()->exec('CREATE TABLE IF NOT EXISTS news (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        text TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
}

function ensure_phone_sale_columns(): bool
{
    $changed = false;

    $checkSale = db()->query("SHOW COLUMNS FROM `phones` LIKE 'is_sale'");
    if (!$checkSale || !$checkSale->fetch()) {
        db()->exec('ALTER TABLE phones ADD COLUMN is_sale TINYINT(1) NOT NULL DEFAULT 0');
        $changed = true;
    }

    $checkOldPrice = db()->query("SHOW COLUMNS FROM `phones` LIKE 'old_price'");
    if (!$checkOldPrice || !$checkOldPrice->fetch()) {
        db()->exec('ALTER TABLE phones ADD COLUMN old_price DECIMAL(10,2) DEFAULT NULL');
        $changed = true;
    }

    return true;
}

function ensure_reviews_table(): void
{
    db()->exec('CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        phone_id INT NOT NULL,
        user_id INT NOT NULL,
        rating TINYINT NOT NULL,
        comment TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (phone_id),
        INDEX (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
}

function ensure_phone_popular_column(): bool
{
    $check = db()->query("SHOW COLUMNS FROM `phones` LIKE 'is_popular'");
    if ($check && $check->fetch()) {
        return true;
    }

    db()->exec('ALTER TABLE phones ADD COLUMN is_popular TINYINT(1) NOT NULL DEFAULT 0');
    return true;
}

function ensure_wishlist_table(): void
{
    db()->exec('CREATE TABLE IF NOT EXISTS wishlists (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        phone_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_user_phone (user_id, phone_id),
        INDEX (user_id),
        INDEX (phone_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
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


function uploads_base_url(): string
{
    $base = app_base_url();
    $scriptFilename = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_FILENAME'] ?? ''));
    $scriptDir = basename(dirname($scriptFilename));

    if ($scriptDir === 'public') {
        return $base . '/uploads';
    }

    return $base . '/public/uploads';
}


function normalize_uploaded_image(string $tmpPath, string $destinationPath, string $extension, int $maxWidth = 1280, int $maxHeight = 1280): bool
{
    if (!is_file($tmpPath)) {
        return false;
    }

    $extension = strtolower($extension);
    $supported = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($extension, $supported, true)) {
        return false;
    }

    if (!function_exists('imagecreatetruecolor')) {
        return move_uploaded_file($tmpPath, $destinationPath);
    }

    $meta = @getimagesize($tmpPath);
    if (!$meta || empty($meta[0]) || empty($meta[1])) {
        return false;
    }

    [$width, $height] = $meta;
    $src = match ($extension) {
        'jpg', 'jpeg' => @imagecreatefromjpeg($tmpPath),
        'png' => @imagecreatefrompng($tmpPath),
        'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($tmpPath) : false,
        'gif' => @imagecreatefromgif($tmpPath),
        default => false,
    };

    if (!$src) {
        return move_uploaded_file($tmpPath, $destinationPath);
    }

    $ratio = min($maxWidth / $width, $maxHeight / $height, 1);
    $newWidth = max(1, (int) round($width * $ratio));
    $newHeight = max(1, (int) round($height * $ratio));

    $dst = imagecreatetruecolor($newWidth, $newHeight);

    if (in_array($extension, ['png', 'webp', 'gif'], true)) {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $newWidth, $newHeight, $transparent);
    }

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    $saved = match ($extension) {
        'jpg', 'jpeg' => imagejpeg($dst, $destinationPath, 84),
        'png' => imagepng($dst, $destinationPath, 6),
        'webp' => function_exists('imagewebp') ? imagewebp($dst, $destinationPath, 84) : false,
        'gif' => imagegif($dst, $destinationPath),
        default => false,
    };

    imagedestroy($src);
    imagedestroy($dst);

    if ($saved) {
        @unlink($tmpPath);
    }

    return (bool) $saved;
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
