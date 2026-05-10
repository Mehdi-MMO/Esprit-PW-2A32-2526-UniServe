<?php

declare(strict_types=1);

class Database
{
    private static ?PDO $instance = null;

    private static bool $dotEnvLoaded = false;

    /**
     * Load optional `.env` from project root (next to index.php) into getenv / $_ENV.
     */
    private static function loadDotEnv(): void
    {
        if (self::$dotEnvLoaded) {
            return;
        }
        self::$dotEnvLoaded = true;

        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
        if (!is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            if ($key === '') {
                continue;
            }
            $value = trim($value);
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"'))
                || (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }

    public static function connect(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        self::loadDotEnv();

        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '3306';
        $dbname = getenv('DB_NAME') ?: 'uniserve';
        $username = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASSWORD');

        if ($password === false) {
            // Useful in Docker setups where root password is exposed this way.
            $password = getenv('MYSQL_ROOT_PASSWORD');
        }

        if ($password === false) {
            $password = '';
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        self::$instance = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        return self::$instance;
    }
}
