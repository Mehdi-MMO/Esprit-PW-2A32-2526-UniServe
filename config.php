<?php

declare(strict_types=1);

class Database
{
    private static ?PDO $instance = null;

    public static function connect(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '3307';
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
