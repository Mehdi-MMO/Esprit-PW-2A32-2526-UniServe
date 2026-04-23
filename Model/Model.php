<?php

declare(strict_types=1);

class Model
{
    protected static ?PDO $db = null;

    public function __construct()
    {
        if (!(self::$db instanceof PDO)) {
            self::$db = Database::connect();
        }
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $statement = self::$db->prepare($sql);
        $statement->execute($params);
        return $statement;
    }

    public function lastInsertId(): string
    {
        return self::$db->lastInsertId();
    }
}
