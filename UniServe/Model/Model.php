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
        try {
            $statement = self::$db->prepare($sql);
            $statement->execute($params);
            return $statement;
        } catch (PDOException $e) {
            // Reconnexion automatique si MySQL server has gone away (erreur 2006 ou 2013)
            if (in_array($e->errorInfo[1] ?? 0, [2006, 2013])) {
                self::$db = Database::connect(true);
                $statement = self::$db->prepare($sql);
                $statement->execute($params);
                return $statement;
            }
            throw $e;
        }
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
