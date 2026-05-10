<?php

declare(strict_types=1);

/**
 * Rows in demandes_service (student requests against categories_service).
 */
class DemandeDeService
{
    public const STATUTS = ['en_attente', 'en_cours', 'traite', 'rejete'];

    private Model $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    public static function allowedStatuts(): array
    {
        return self::STATUTS;
    }

    private function baseSelectAdmin(): string
    {
        return 'SELECT d.id, d.etudiant_id, d.categorie_id, d.titre, d.description, d.statut,
                       d.assigne_a, d.soumise_le, d.cloturee_le,
                       cat.nom AS categorie_nom,
                       CONCAT(etu.prenom, " ", etu.nom) AS etudiant_nom,
                       etu.email AS etudiant_email,
                       CONCAT(asg.prenom, " ", asg.nom) AS assigne_nom
                FROM demandes_service d
                INNER JOIN utilisateurs etu ON etu.id = d.etudiant_id
                INNER JOIN categories_service cat ON cat.id = d.categorie_id
                LEFT JOIN utilisateurs asg ON asg.id = d.assigne_a';
    }

    /**
     * @param array{statut?: string, q?: string} $filters
     */
    public function findAllForAdmin(array $filters = []): array
    {
        $sql = $this->baseSelectAdmin() . ' WHERE 1=1';
        $params = [];

        $statut = isset($filters['statut']) ? trim((string) $filters['statut']) : '';
        if ($statut !== '' && in_array($statut, self::STATUTS, true)) {
            $sql .= ' AND d.statut = ?';
            $params[] = $statut;
        }

        $q = isset($filters['q']) ? trim((string) $filters['q']) : '';
        if ($q !== '') {
            $like = '%' . $q . '%';
            $sql .= ' AND (
                d.titre LIKE ? OR d.description LIKE ?
                OR etu.nom LIKE ? OR etu.prenom LIKE ? OR etu.email LIKE ?
                OR cat.nom LIKE ?
            )';
            array_push($params, $like, $like, $like, $like, $like, $like);
        }

        $sql .= ' ORDER BY d.soumise_le DESC';

        $statement = $this->model->query($sql, $params);

        return $statement->fetchAll();
    }

    public function findAllForStudent(int $etudiantId): array
    {
        $statement = $this->model->query(
            'SELECT d.id, d.etudiant_id, d.categorie_id, d.titre, d.description, d.statut,
                    d.assigne_a, d.soumise_le, d.cloturee_le,
                    cat.nom AS categorie_nom
             FROM demandes_service d
             INNER JOIN categories_service cat ON cat.id = d.categorie_id
             WHERE d.etudiant_id = ?
             ORDER BY d.soumise_le DESC',
            [$etudiantId]
        );

        return $statement->fetchAll();
    }

    public function findById(int|string $id): ?array
    {
        $statement = $this->model->query(
            $this->baseSelectAdmin() . ' WHERE d.id = ? LIMIT 1',
            [(int) $id]
        );

        $row = $statement->fetch();

        return $row ?: null;
    }

    /**
     * @param array{categorie_id: int, titre: string, description: string} $data
     */
    public function create(int $etudiantId, array $data): int|false
    {
        if ($etudiantId <= 0) {
            return false;
        }

        $catId = (int) ($data['categorie_id'] ?? 0);
        $titre = trim((string) ($data['titre'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));

        if ($catId <= 0 || $titre === '' || $description === '') {
            return false;
        }

        $this->model->query(
            'INSERT INTO demandes_service (etudiant_id, categorie_id, titre, description, statut)
             VALUES (?, ?, ?, ?, "en_attente")',
            [$etudiantId, $catId, $titre, $description]
        );

        return (int) $this->model->lastInsertId();
    }

    /**
     * Student update: categorie, titre, description only when en_attente.
     */
    public function updateByStudent(int $id, int $etudiantId, array $data): bool
    {
        if ($id <= 0 || $etudiantId <= 0) {
            return false;
        }

        $catId = (int) ($data['categorie_id'] ?? 0);
        $titre = trim((string) ($data['titre'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));

        if ($catId <= 0 || $titre === '' || $description === '') {
            return false;
        }

        $statement = $this->model->query(
            'UPDATE demandes_service
             SET categorie_id = ?, titre = ?, description = ?
             WHERE id = ? AND etudiant_id = ? AND statut = "en_attente"',
            [$catId, $titre, $description, $id, $etudiantId]
        );

        return $statement->rowCount() > 0;
    }

    public function updateStatut(int $id, string $statut): bool
    {
        if (!in_array($statut, self::STATUTS, true)) {
            return false;
        }

        if (in_array($statut, ['traite', 'rejete'], true)) {
            $statement = $this->model->query(
                'UPDATE demandes_service SET statut = ?, cloturee_le = NOW() WHERE id = ?',
                [$statut, $id]
            );
        } else {
            $statement = $this->model->query(
                'UPDATE demandes_service SET statut = ?, cloturee_le = NULL WHERE id = ?',
                [$statut, $id]
            );
        }

        return $statement->rowCount() > 0;
    }

    public function updateAssignee(int $id, ?int $staffUserId): bool
    {
        $statement = $this->model->query(
            'UPDATE demandes_service SET assigne_a = ? WHERE id = ?',
            [$staffUserId, $id]
        );

        return $statement->rowCount() > 0;
    }

    public function deleteByStudent(int $id, int $etudiantId): bool
    {
        $statement = $this->model->query(
            'DELETE FROM demandes_service WHERE id = ? AND etudiant_id = ? AND statut = "en_attente"',
            [$id, $etudiantId]
        );

        return $statement->rowCount() > 0;
    }

    public function deleteByAdmin(int $id): bool
    {
        $statement = $this->model->query(
            'DELETE FROM demandes_service WHERE id = ?',
            [$id]
        );

        return $statement->rowCount() > 0;
    }
}
