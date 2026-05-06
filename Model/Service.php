<?php
declare(strict_types=1);

require_once __DIR__ . '/Model.php';

class Service extends Model implements JsonSerializable
{
    protected string $table = 'service';

    private ?int $id = null;
    private ?string $nom = null;
    private ?string $description = null;
    private ?int $actif = null;

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): self { $this->id = $id; return $this; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(?string $nom): self { $this->nom = $nom; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function getActif(): ?int { return $this->actif; }
    public function setActif(?int $actif): self { $this->actif = $actif; return $this; }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'description' => $this->description,
            'actif' => $this->actif
        ];
    }

    /**
     * @return Service[]
     */
    public function getAll(): array
    {
        $stmt = $this->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public function getById(int $id): ?self
    {
        $stmt = $this->query("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        $record = $stmt->fetch();
        return $record ?: null;
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
