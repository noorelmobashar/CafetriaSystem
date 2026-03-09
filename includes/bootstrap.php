<?php

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
