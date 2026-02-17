<?php
return [
    'db' => [
        'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=nucleus_store;charset=utf8mb4',
        'user' => 'root',
        'password' => '',
    ],
    'app' => [
        'name' => 'Nucleus',
        // e.g. '/nucleus' when project is served from http://localhost/nucleus
        'base_url' => '',
        // false = routes via /index.php?r=/path (works even without mod_rewrite)
        // true  = pretty URLs like /catalog (requires rewrite rules)
        'use_rewrite' => false,
        'password_pepper' => 'change-this-super-secret-pepper',
    ],
];
