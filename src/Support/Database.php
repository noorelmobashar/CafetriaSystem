<?php

declare(strict_types=1);

namespace Cafetria\Support;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?self $instance = null;

    private PDO $connection;

    private function __construct()
    {
        $host = (string) EnvLoader::get('DB_HOST', '127.0.0.1');
        $port = (int) EnvLoader::get('DB_PORT', 3306);
        $database = (string) EnvLoader::get('DB_DATABASE', '');
        $username = (string) EnvLoader::get('DB_USERNAME', '');
        $password = (string) EnvLoader::get('DB_PASSWORD', '');
        $charset = (string) EnvLoader::get('DB_CHARSET', 'utf8mb4');

        if ($database === '' || $username === '') {
            throw new RuntimeException('Database credentials are missing. Update your .env file before opening database-backed features.');
        }

        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $database, $charset);

        try {
            $this->connection = new PDO(
                $dsn,
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $exception) {
            throw new RuntimeException('Unable to connect to the configured database.', 0, $exception);
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function connection(): PDO
    {
        return self::getInstance()->getConnection();
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    private function __clone(): void
    {
    }

    public function __wakeup(): void
    {
        throw new RuntimeException('Database singleton cannot be unserialized.');
    }
}