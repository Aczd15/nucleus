<?php

$config = require __DIR__ . '/../config/config.php';

$sqlPath = __DIR__ . '/../sql/schema.sql';
if (!file_exists($sqlPath)) {
    fwrite(STDERR, "Schema file not found: {$sqlPath}\n");
    exit(1);
}

try {
    $dsnWithoutDb = preg_replace('/;dbname=[^;]+/', '', $config['db']['dsn']);

    $pdo = new PDO(
        $dsnWithoutDb,
        $config['db']['user'],
        $config['db']['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $sql = file_get_contents($sqlPath);
    $pdo->exec($sql);

    echo "Database initialized successfully from sql/schema.sql\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Database init failed: " . $e->getMessage() . "\n");
    exit(1);
}
