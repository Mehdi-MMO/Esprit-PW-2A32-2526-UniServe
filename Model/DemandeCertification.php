<?php
declare(strict_types=1);

class DemandeCertification extends Model
{
    /**
     * Store a demande with utilisateur_id (required by the FK constraint in the integrated schema).
     * Falls back to user ID 2 (demo student) if utilisateur_id not provided.
     */
    public function storeWithUser(array $data): bool
    {
        $sql = "INSERT INTO demandes_certification
                    (utilisateur_id, nom_certificat, titre_cours, organisation, date_souhaitee, heure_preferee, notes, fichier_path)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = self::$db->prepare($sql);
        try {
            return $stmt->execute([
                $data['utilisateur_id']  ?? 2,
                $data['nom_certificat']  ?? '',
                $data['titre_cours']     ?? null,
                $data['organisation']    ?? '',
                $data['date_souhaitee']  ?? '',
                $data['heure_preferee']  ?? null,
                $data['notes']           ?? null,
                $data['fichier_path']    ?? null,
            ]);
        } catch (PDOException $e) {
            error_log('DemandeCertification::storeWithUser — ' . $e->getMessage());
            return false;
        }
    }

    /** Legacy store (without utilisateur_id — for DOCAC standalone module compatibility) */
    public function store(array $data): bool
    {
        return $this->storeWithUser(array_merge(['utilisateur_id' => 2], $data));
    }

    public function getAll(): array
    {
        $stmt = self::$db->prepare("
            SELECT dc.*, CONCAT(COALESCE(u.prenom,''), ' ', COALESCE(u.nom,'')) AS demandeur_nom
            FROM demandes_certification dc
            LEFT JOIN utilisateurs u ON u.id = dc.utilisateur_id
            ORDER BY dc.soumise_le DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByStatut(string $statut): array
    {
        $stmt = self::$db->prepare("SELECT * FROM demandes_certification WHERE statut = ? ORDER BY soumise_le DESC");
        $stmt->execute([$statut]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countByStatut(string $statut): int
    {
        $stmt = self::$db->prepare("SELECT COUNT(*) FROM demandes_certification WHERE statut = ?");
        $stmt->execute([$statut]);
        return (int) $stmt->fetchColumn();
    }

    public function updateStatut(int $id, string $statut, string $commentaire = ''): bool
    {
        $sql = "UPDATE demandes_certification SET statut = ?, commentaire_admin = ?, traitee_le = NOW() WHERE id = ?";
        $stmt = self::$db->prepare($sql);
        try {
            return $stmt->execute([$statut, $commentaire, $id]);
        } catch (PDOException $e) {
            error_log('DemandeCertification::updateStatut — ' . $e->getMessage());
            return false;
        }
    }

    public function markQuizEnvoye(int $id): bool
    {
        $stmt = self::$db->prepare("UPDATE demandes_certification SET statut = 'quiz_envoye' WHERE id = ?");
        try {
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log('DemandeCertification::markQuizEnvoye — ' . $e->getMessage());
            return false;
        }
    }

    public function getById(int $id): ?array
    {
        $stmt = self::$db->prepare("SELECT * FROM demandes_certification WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
