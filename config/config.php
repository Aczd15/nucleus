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
        'password_pepper' => 'change-this-super-secret-pepper',
    ],
];
