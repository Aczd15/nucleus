<?php

require __DIR__ . '/../src/bootstrap.php';

$routeParam = $_GET['r'] ?? null;

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

switch ($path) {
    case '/':
    case '/index.php':
        render('home/index');
        break;

    default:
        http_response_code(404);
        render('home/404');
        break;
}
