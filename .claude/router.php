<?php
// Router cho `php -S` khi phục vụ Laravel trực tiếp (bỏ qua `artisan serve`, vốn tự gọi
// proc_open() lồng để spawn tiến trình con — bị chặn "requires elevation" trên máy này).
$publicPath = __DIR__ . '/../public';
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($uri !== '/' && file_exists($publicPath . $uri) && !is_dir($publicPath . $uri)) {
    return false;
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $publicPath . '/index.php';
require $publicPath . '/index.php';
