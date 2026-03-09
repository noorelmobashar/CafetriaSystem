<?php

declare(strict_types=1);

namespace Cafetria\Support;

final class EnvLoader
{
    private static bool $loaded = false;

    public static function load(string $rootPath, string $fileName = '.env'): void
    {
        if (self::$loaded) {
            return;
        }

        $envPath = rtrim($rootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($fileName, DIRECTORY_SEPARATOR);

        if (!is_file($envPath)) {
            self::$loaded = true;
            return;
        }

        if (class_exists('Dotenv\\Dotenv')) {
            $dotenv = \Dotenv\Dotenv::createImmutable($rootPath, $fileName);
            $dotenv->safeLoad();
            self::$loaded = true;
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            self::$loaded = true;
            return;
        }

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $trimmed, 2), 2, '');
            $key = trim($key);

            if ($key === '') {
                continue;
            }

            $value = self::normalizeValue($value);
            self::set($key, $value);
        }

        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null || $value === '') {
            return $default;
        }

        return $value;
    }

    private static function set(string $key, string $value): void
    {
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv(sprintf('%s=%s', $key, $value));
    }

    private static function normalizeValue(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        $firstCharacter = $value[0];
        $lastCharacter = $value[strlen($value) - 1];

        if (($firstCharacter === '"' && $lastCharacter === '"') || ($firstCharacter === '\'' && $lastCharacter === '\'')) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}