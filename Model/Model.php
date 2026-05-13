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

    /**
     * Run SQL without bound parameters (DDL / maintenance). Prefer {@see query} for data statements.
     */
    public function execSql(string $sql): int|false
    {
        return self::$db->exec($sql);
    }

    public function beginTransaction(): void
    {
        self::$db->beginTransaction();
    }

    public function commit(): void
    {
        self::$db->commit();
    }

    public function rollBack(): void
    {
        self::$db->rollBack();
    }

    public function lastInsertId(): string
    {
        return self::$db->lastInsertId();
    }
}
