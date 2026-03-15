<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/Support/EnvLoader.php';
require_once dirname(__DIR__) . '/src/Support/Database.php';

$composerAutoload = dirname(__DIR__) . '/vendor/autoload.php';

if (is_file($composerAutoload)) {
    require_once $composerAutoload;
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

\Cafetria\Support\EnvLoader::load(dirname(__DIR__));

if (!function_exists('root_path')) {
    function root_path(string $path = ''): string
    {
        $root = dirname(__DIR__);

        if ($path === '') {
            return $root;
        }

        return $root . '/' . ltrim($path, '/');
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return \Cafetria\Support\EnvLoader::get($key, $default);
    }
}

if (!function_exists('db')) {
    function db(): \PDO
    {
        return \Cafetria\Support\Database::connection();
    }
}

if (!function_exists('asset_url')) {
    function asset_url(string $basePath, string $asset): string
    {
        return rtrim($basePath, '/') . '/' . ltrim($asset, '/');
    }
}

if (!function_exists('is_active_page')) {
    function is_active_page(string $currentPage, string $expectedPage): string
    {
        return $currentPage === $expectedPage ? 'bg-slate-900 text-white shadow-soft' : 'bg-slate-100 text-slate-700 hover:bg-slate-200';
    }
}

require_once dirname(__DIR__) . '/includes/pagination.php';
