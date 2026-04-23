<?php
declare(strict_types=1);

require_once __DIR__ . '/Model.php';

class Service extends Model
{
    protected string $table = 'service';

    public function getAll(): array
    {
        return $this->findAll($this->table);
    }

    public function getById(int $id): ?array
    {
        return $this->findById($this->table, $id);
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (nom, description, actif) VALUES (?, ?, ?)";
        $stmt = $this->query($sql, [$data['nom'], $data['description'], $data['actif'] ?? 1]);
        return $stmt->rowCount() > 0;
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET nom = ?, description = ?, actif = ? WHERE id = ?";
        $stmt = $this->query($sql, [$data['nom'], $data['description'], $data['actif'] ?? 1, $id]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }
}
