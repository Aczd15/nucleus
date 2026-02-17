<?php
require __DIR__ . '/../src/bootstrap.php';

use App\Auth;

$name = $argv[1] ?? null;
$email = $argv[2] ?? null;
$password = $argv[3] ?? null;

if (!$name || !$email || !$password) {
    echo "Usage: php scripts/create_admin.php <name> <email> <password>\n";
    exit(1);
}

$stmt = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)');
$stmt->execute([
    'name' => $name,
    'email' => $email,
    'password_hash' => Auth::hashPassword($password, $config['app']['password_pepper']),
    'role' => 'admin',
]);

echo "Admin created: {$email}\n";
