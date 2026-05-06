<?php

declare(strict_types=1);

class Database
{
    private static ?PDO $instance = null;

    public static function connect(bool $forceReconnect = false): PDO
    {
        if (!$forceReconnect && self::$instance instanceof PDO) {
            return self::$instance;
        }

        self::$instance = null;

        $host     = 'localhost';
        $port     = '3307';
        $dbname   = 'web';
        $username = 'root';
        $password = '';
        $dsn      = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        self::$instance = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        ]);

        return self::$instance;
    }
}
