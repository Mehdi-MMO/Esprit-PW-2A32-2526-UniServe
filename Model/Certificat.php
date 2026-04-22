<?php
declare(strict_types=1);

class Certificat extends Model {
    
    /**
     * Récupère tous les certificats triés par date
     */
    public function getAllCertificats(): array {
        $sql = "SELECT * FROM certificats ORDER BY date_obtention DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ajoute un nouveau certificat
     */
    public function addCertificat(string $nom, string $date, string $org, string $path, string $titreCours): bool {
        $sql = "INSERT INTO certificats (nom_certificat, date_obtention, organisation, fichier_path, titre_cours) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([$nom, $date, $org, $path, $titreCours]);
        } catch (PDOException $e) {
            error_log("Erreur lors de l'ajout du certificat: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Modifie un certificat existant
     */
    public function updateCertificat(int $id, string $nom, string $date, string $org, string $path, string $titreCours): bool {
        // Si le chemin est vide, on ne met pas à jour le fichier
        if (empty($path)) {
            $sql = "UPDATE certificats SET nom_certificat = ?, date_obtention = ?, organisation = ?, titre_cours = ? 
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            
            try {
                return $stmt->execute([$nom, $date, $org, $titreCours, $id]);
            } catch (PDOException $e) {
                error_log("Erreur lors de la modification du certificat: " . $e->getMessage());
                return false;
            }
        } else {
            // Sinon on met à jour aussi le fichier
            $sql = "UPDATE certificats SET nom_certificat = ?, date_obtention = ?, organisation = ?, fichier_path = ?, titre_cours = ? 
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            
            try {
                return $stmt->execute([$nom, $date, $org, $path, $titreCours, $id]);
            } catch (PDOException $e) {
                error_log("Erreur lors de la modification du certificat: " . $e->getMessage());
                return false;
            }
        }
    }

    /**
     * Supprime un certificat via son ID
     */
    public function deleteCertificat(int $id): bool {
        $sql = "DELETE FROM certificats WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression du certificat: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère un certificat par son ID
     */
    public function getCertificatById(int $id): ?array {
        $sql = "SELECT * FROM certificats WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Récupère les certificats liés à un cours
     */
    public function getCertificatsByCours(string $titreCours): array {
        $sql = "SELECT * FROM certificats WHERE titre_cours = ? ORDER BY date_obtention DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$titreCours]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}