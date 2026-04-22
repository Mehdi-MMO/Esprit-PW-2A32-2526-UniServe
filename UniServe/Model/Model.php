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

    public function findAll(string $table): array
    {
        $statement = $this->query("SELECT * FROM `{$table}`");
        return $statement->fetchAll();
    }

    public function findById(string $table, int|string $id): ?array
    {
        $statement = $this->query("SELECT * FROM `{$table}` WHERE id = ? LIMIT 1", [$id]);
        $record = $statement->fetch();
        return $record ?: null;
    }
}
