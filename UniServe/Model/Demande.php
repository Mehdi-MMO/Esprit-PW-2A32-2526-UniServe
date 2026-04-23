<?php
declare(strict_types=1);

require_once __DIR__ . '/Model.php';

class Demande extends Model
{
    protected string $table = 'demande';

    public function getAll(): array
    {
        $sql = "SELECT d.*, u.nom as user_nom, u.prenom as user_prenom, s.nom as service_nom 
                FROM {$this->table} d 
                JOIN utilisateurs u ON d.utilisateur_id = u.id 
                JOIN service s ON d.service_id = s.id 
                ORDER BY d.date_creation DESC";
        return $this->query($sql)->fetchAll();
    }

    public function getAllByUser(int $userId): array
    {
        $sql = "SELECT d.*, s.nom as service_nom 
                FROM {$this->table} d 
                JOIN service s ON d.service_id = s.id 
                WHERE d.utilisateur_id = ? 
                ORDER BY d.date_creation DESC";
        return $this->query($sql, [$userId])->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $sql = "SELECT d.*, u.nom as user_nom, u.prenom as user_prenom, s.nom as service_nom 
                FROM {$this->table} d 
                JOIN utilisateurs u ON d.utilisateur_id = u.id 
                JOIN service s ON d.service_id = s.id 
                WHERE d.id = ?";
        $record = $this->query($sql, [$id])->fetch();
        return $record ?: null;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (utilisateur_id, service_id, titre, description) VALUES (?, ?, ?, ?)";
        $stmt = $this->query($sql, [
            $data['utilisateur_id'],
            $data['service_id'],
            $data['titre'],
            $data['description']
        ]);
        return $stmt->rowCount() > 0;
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET service_id = ?, titre = ?, description = ? WHERE id = ?";
        $stmt = $this->query($sql, [
            $data['service_id'],
            $data['titre'],
            $data['description'],
            $id
        ]);
        return $stmt->rowCount() > 0;
    }

    public function updateStatut(int $id, string $statut): bool
    {
        $sql = "UPDATE {$this->table} SET statut = ? WHERE id = ?";
        $stmt = $this->query($sql, [$statut, $id]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }
}
