<?php
declare(strict_types=1);

class Cours extends Model
{
    /**
     * Récupère tous les cours (avec décodage des fichiers JSON)
     */
    public function getAllCours(): array
    {
        $sql  = "SELECT * FROM cours ORDER BY titre";
        $stmt = self::$db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // décoder fichiers_json en tableau pour la vue
        foreach ($rows as &$r) {
            $r['fichiers'] = !empty($r['fichiers_json'])
                ? (json_decode($r['fichiers_json'], true) ?? [])
                : [];
        }
        return $rows;
    }

    /**
     * Crée un nouveau cours (avec image et fichiers optionnels)
     */
    public function createCours(array $data, string $imagePath = '', array $fichiers = []): bool
    {
        $sql = "INSERT INTO cours (titre, description, formateur, contenu, image_path, fichiers_json)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = self::$db->prepare($sql);

        try {
            return $stmt->execute([
                trim($data['titre']       ?? ''),
                trim($data['description'] ?? ''),
                trim($data['formateur']   ?? ''),
                trim($data['contenu']     ?? ''),
                $imagePath ?: null,
                !empty($fichiers) ? json_encode($fichiers, JSON_UNESCAPED_UNICODE) : null,
            ]);
        } catch (PDOException $e) {
            error_log("Cours::createCours — " . $e->getMessage());
            return false;
        }
    }

    /**
     * Modifie un cours existant.
     * Si $imagePath est vide → on garde l'image existante.
     * $fichiers contient déjà la fusion existants + nouveaux (faite par le contrôleur).
     */
    public function updateCours(array $data, string $oldTitre, string $imagePath = '', array $fichiers = []): bool
    {
        if ($imagePath !== '') {
            $sql = "UPDATE cours
                       SET titre = ?, description = ?, formateur = ?, contenu = ?,
                           image_path = ?, fichiers_json = ?
                     WHERE titre = ?";
            $params = [
                trim($data['titre']       ?? ''),
                trim($data['description'] ?? ''),
                trim($data['formateur']   ?? ''),
                trim($data['contenu']     ?? ''),
                $imagePath,
                json_encode($fichiers, JSON_UNESCAPED_UNICODE),
                $oldTitre,
            ];
        } else {
            $sql = "UPDATE cours
                       SET titre = ?, description = ?, formateur = ?, contenu = ?,
                           fichiers_json = ?
                     WHERE titre = ?";
            $params = [
                trim($data['titre']       ?? ''),
                trim($data['description'] ?? ''),
                trim($data['formateur']   ?? ''),
                trim($data['contenu']     ?? ''),
                json_encode($fichiers, JSON_UNESCAPED_UNICODE),
                $oldTitre,
            ];
        }

        $stmt = self::$db->prepare($sql);
        try {
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Cours::updateCours — " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime un cours via son titre
     */
    public function deleteCours(string $titre): bool
    {
        $stmt = self::$db->prepare("DELETE FROM cours WHERE titre = ?");
        try {
            return $stmt->execute([$titre]);
        } catch (PDOException $e) {
            error_log("Cours::deleteCours — " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère un cours par son titre (avec ses fichiers décodés)
     */
    public function getCoursParTitre(string $titre): ?array
    {
        $stmt = self::$db->prepare("SELECT * FROM cours WHERE titre = ?");
        $stmt->execute([$titre]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        $row['fichiers'] = !empty($row['fichiers_json'])
            ? (json_decode($row['fichiers_json'], true) ?? [])
            : [];
        return $row;
    }

    public function coursExiste(string $titre): bool
    {
        return $this->getCoursParTitre($titre) !== null;
    }
}
