<?php
declare(strict_types=1);

class Cours extends Model {
    
    /**
     * Récupère tous les cours
     */
    public function getAllCours(): array {
        $sql = "SELECT * FROM cours ORDER BY titre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un nouveau cours
     */
    public function createCours(array $data): bool {
        $sql = "INSERT INTO cours (titre, description, formateur) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([
                $data['titre'] ?? '',
                $data['description'] ?? '',
                $data['formateur'] ?? ''
            ]);
        } catch (PDOException $e) {
            error_log("Erreur lors de la création du cours: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Modifie un cours existant
     */
    public function updateCours(array $data, string $oldTitre): bool {
        $sql = "UPDATE cours SET titre = ?, description = ?, formateur = ? WHERE titre = ?";
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([
                $data['titre'] ?? '',
                $data['description'] ?? '',
                $data['formateur'] ?? '',
                $oldTitre
            ]);
        } catch (PDOException $e) {
            error_log("Erreur lors de la modification du cours: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime un cours via son titre (clé primaire)
     */
    public function deleteCours(string $titre): bool {
        $sql = "DELETE FROM cours WHERE titre = ?";
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([$titre]);
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression du cours: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère un cours par son titre
     */
    public function getCoursParTitre(string $titre): ?array {
        $sql = "SELECT * FROM cours WHERE titre = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$titre]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Vérifie si un cours existe
     */
    public function coursExiste(string $titre): bool {
        return $this->getCoursParTitre($titre) !== null;
    }
}