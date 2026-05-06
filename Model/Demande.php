<?php
declare(strict_types=1);

require_once __DIR__ . '/Model.php';

class Demande extends Model implements JsonSerializable
{
    protected string $table = 'demande';

    private ?int $id = null;
    private ?int $utilisateur_id = null;
    private ?int $service_id = null;
    private ?string $titre = null;
    private ?string $description = null;
    private ?string $statut = null;
    private ?string $email = null;
    private ?string $telephone = null;
    private ?string $date_creation = null;
    private ?string $date_cloture = null;
    private ?int $assigne_a = null;

    // Champs de jointure
    private ?string $user_nom = null;
    private ?string $user_prenom = null;
    private ?string $service_nom = null;

    public function getId(): ?int { return $this->id; }
    public function getUtilisateurId(): ?int { return $this->utilisateur_id; }
    public function getServiceId(): ?int { return $this->service_id; }
    public function getTitre(): ?string { return $this->titre; }
    public function getDescription(): ?string { return $this->description; }
    public function getStatut(): ?string { return $this->statut; }
    public function getEmail(): ?string { return $this->email; }
    public function getTelephone(): ?string { return $this->telephone; }
    public function getDateCreation(): ?string { return $this->date_creation; }
    public function getDateCloture(): ?string { return $this->date_cloture; }
    public function getAssigneA(): ?int { return $this->assigne_a; }
    
    public function getUserNom(): ?string { return $this->user_nom; }
    public function getUserPrenom(): ?string { return $this->user_prenom; }
    public function getServiceNom(): ?string { return $this->service_nom; }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'utilisateur_id' => $this->utilisateur_id,
            'service_id' => $this->service_id,
            'titre' => $this->titre,
            'description' => $this->description,
            'statut' => $this->statut,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'date_creation' => $this->date_creation,
            'date_cloture' => $this->date_cloture,
            'assigne_a' => $this->assigne_a,
            'user_nom' => $this->user_nom,
            'user_prenom' => $this->user_prenom,
            'service_nom' => $this->service_nom
        ];
    }

    /**
     * @return Demande[]
     */
    public function getAll(): array
    {
        $sql = "SELECT d.*, u.nom as user_nom, u.prenom as user_prenom, s.nom as service_nom 
                FROM {$this->table} d 
                JOIN utilisateurs u ON d.utilisateur_id = u.id 
                JOIN service s ON d.service_id = s.id 
                ORDER BY d.date_creation DESC";
        $stmt = $this->query($sql);
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    /**
     * @return Demande[]
     */
    public function getAllByUser(int $userId): array
    {
        $sql = "SELECT d.*, s.nom as service_nom 
                FROM {$this->table} d 
                JOIN service s ON d.service_id = s.id 
                WHERE d.utilisateur_id = ? 
                ORDER BY d.date_creation DESC";
        $stmt = $this->query($sql, [$userId]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    public function getById(int $id): ?self
    {
        $sql = "SELECT d.*, u.nom as user_nom, u.prenom as user_prenom, s.nom as service_nom 
                FROM {$this->table} d 
                JOIN utilisateurs u ON d.utilisateur_id = u.id 
                JOIN service s ON d.service_id = s.id 
                WHERE d.id = ?";
        $stmt = $this->query($sql, [$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        $record = $stmt->fetch();
        return $record ?: null;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (utilisateur_id, service_id, titre, description, email, telephone) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->query($sql, [
            $data['utilisateur_id'],
            $data['service_id'],
            $data['titre'],
            $data['description'],
            $data['email'] ?? null,
            $data['telephone'] ?? null
        ]);
        return $stmt->rowCount() > 0;
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET service_id = ?, titre = ?, description = ?, email = ?, telephone = ? WHERE id = ?";
        $stmt = $this->query($sql, [
            $data['service_id'],
            $data['titre'],
            $data['description'],
            $data['email'] ?? null,
            $data['telephone'] ?? null,
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
