<?php
declare(strict_types=1);

class DemandeCertification extends Model
{
    // ── Student side ────────────────────────────────────────────

    /** Insert a new certification request */
    public function store(array $data): bool
    {
        $sql = "INSERT INTO demandes_certification
                    (nom_certificat, titre_cours, organisation, date_souhaitee, heure_preferee, notes, fichier_path)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        try {
            return $stmt->execute([
                $data['nom_certificat']  ?? '',
                $data['titre_cours']     ?? null,
                $data['organisation']    ?? '',
                $data['date_souhaitee']  ?? '',
                $data['heure_preferee']  ?? null,
                $data['notes']           ?? null,
                $data['fichier_path']    ?? null,
            ]);
        } catch (PDOException $e) {
            error_log('DemandeCertification::store — ' . $e->getMessage());
            return false;
        }
    }

    // ── Admin side ───────────────────────────────────────────────

    /** All requests, newest first */
    public function getAll(): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM demandes_certification ORDER BY soumise_le DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Requests filtered by status */
    public function getByStatut(string $statut): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM demandes_certification WHERE statut = ? ORDER BY soumise_le DESC"
        );
        $stmt->execute([$statut]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Count by status */
    public function countByStatut(string $statut): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM demandes_certification WHERE statut = ?"
        );
        $stmt->execute([$statut]);
        return (int) $stmt->fetchColumn();
    }

    /** Accept or refuse a request */
    public function updateStatut(int $id, string $statut, string $commentaire = ''): bool
    {
        $sql = "UPDATE demandes_certification
                SET statut = ?, commentaire_admin = ?, traitee_le = NOW()
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        try {
            return $stmt->execute([$statut, $commentaire, $id]);
        } catch (PDOException $e) {
            error_log('DemandeCertification::updateStatut — ' . $e->getMessage());
            return false;
        }
    }

    /** Single request by ID */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM demandes_certification WHERE id = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}