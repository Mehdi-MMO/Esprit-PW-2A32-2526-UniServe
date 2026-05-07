<?php

declare(strict_types=1);

if (!function_exists('app_load_env_file')) {
    function app_load_env_file(string $envPath): void
    {
        static $loaded = false;
        if ($loaded || !is_file($envPath)) {
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines)) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $name = trim($parts[0]);
            $value = trim($parts[1]);

            if ($name === '') {
                continue;
            }

            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }

            if (getenv($name) === false || getenv($name) === '') {
                putenv($name . '=' . $value);
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }

        $loaded = true;
    }
}

if (!function_exists('app_env')) {
    function app_env(string $name, string $default = ''): string
    {
        app_load_env_file(__DIR__ . '/.env');

        $val = getenv($name);
        if ($val !== false && $val !== '') {
            return (string) $val;
        }

        if (isset($_ENV[$name]) && (string) $_ENV[$name] !== '') {
            return (string) $_ENV[$name];
        }

        if (isset($_SERVER[$name]) && (string) $_SERVER[$name] !== '') {
            return (string) $_SERVER[$name];
        }

        return $default;
    }
}

class Database
{
    private static ?PDO $instance = null;

    public static function connect(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $host = app_env('DB_HOST', '127.0.0.1');
        $port = app_env('DB_PORT', '3306');
        $dbname = app_env('DB_NAME', 'uniserve');
        $username = app_env('DB_USER', 'root');
        $password = app_env('DB_PASSWORD', app_env('MYSQL_ROOT_PASSWORD', ''));

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        self::$instance = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        return self::$instance;
    }
}
