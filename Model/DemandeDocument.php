<?php

declare(strict_types=1);

class DemandeDocument
{
    public const STATUTS = ['en_attente', 'en_validation', 'valide', 'rejete', 'livre'];

    /** @var array<string, list<string>> */
    private const TRANSITIONS = [
        'en_attente' => ['en_validation', 'valide', 'rejete'],
        'en_validation' => ['valide', 'rejete'],
        'valide' => ['livre'],
        'rejete' => [],
        'livre' => [],
    ];

    private Model $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    public static function allowedStatuts(): array
    {
        return self::STATUTS;
    }

    public static function canTransition(string $from, string $to): bool
    {
        if ($from === $to) {
            return true;
        }

        $allowed = self::TRANSITIONS[$from] ?? [];

        return in_array($to, $allowed, true);
    }

    private function baseSelectAdmin(): string
    {
        return 'SELECT d.id, d.etudiant_id, d.type_document_id, d.statut, d.valide_par, d.note_validation,
                       d.demandee_le, d.validee_le, d.livree_le,
                       td.nom AS type_nom,
                       CONCAT(etu.prenom, " ", etu.nom) AS etudiant_nom,
                       etu.photo_profil AS etudiant_photo,
                       etu.email AS etudiant_email,
                       CONCAT(vp.prenom, " ", vp.nom) AS valide_par_nom
                FROM demandes_document d
                INNER JOIN types_document td ON td.id = d.type_document_id
                INNER JOIN utilisateurs etu ON etu.id = d.etudiant_id
                LEFT JOIN utilisateurs vp ON vp.id = d.valide_par';
    }

    /**
     * @param array{statut?: string, q?: string} $filters
     * @return list<array<string, mixed>>
     */
    public function findForAdmin(array $filters = []): array
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
                td.nom LIKE ? OR etu.nom LIKE ? OR etu.prenom LIKE ? OR etu.email LIKE ?
            )';
            array_push($params, $like, $like, $like, $like);
        }

        $sql .= ' ORDER BY d.demandee_le DESC';

        $statement = $this->model->query($sql, $params);

        return $statement->fetchAll();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findForStudent(int $etudiantId): array
    {
        if ($etudiantId <= 0) {
            return [];
        }

        $statement = $this->model->query(
            'SELECT d.id, d.statut, d.demandee_le, d.validee_le, d.livree_le,
                    d.note_validation,
                    td.nom AS type_nom, td.description AS type_description
             FROM demandes_document d
             INNER JOIN types_document td ON td.id = d.type_document_id
             WHERE d.etudiant_id = ?
             ORDER BY d.demandee_le DESC',
            [$etudiantId]
        );

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $statement = $this->model->query(
            $this->baseSelectAdmin() . ' WHERE d.id = ? LIMIT 1',
            [$id]
        );

        $row = $statement->fetch();

        return $row ?: null;
    }

    public function create(int $etudiantId, int $typeDocumentId): int|false
    {
        if ($etudiantId <= 0 || $typeDocumentId <= 0) {
            return false;
        }

        $this->model->query(
            'INSERT INTO demandes_document (etudiant_id, type_document_id, statut)
             VALUES (?, ?, "en_attente")',
            [$etudiantId, $typeDocumentId]
        );

        return (int) $this->model->lastInsertId();
    }

    /**
     * Apply workflow change with timestamps and optional validation metadata.
     */
    public function applyStatutChange(int $id, string $newStatut, int $staffUserId, ?string $note): bool
    {
        if ($id <= 0 || !in_array($newStatut, self::STATUTS, true)) {
            return false;
        }

        $row = $this->model->query(
            'SELECT id, statut FROM demandes_document WHERE id = ? LIMIT 1',
            [$id]
        )->fetch();

        if (!$row) {
            return false;
        }

        $current = (string) ($row['statut'] ?? '');
        if ($current === $newStatut) {
            return true;
        }

        if (!self::canTransition($current, $newStatut)) {
            return false;
        }

        if ($newStatut === 'rejete' && trim((string) $note) === '') {
            return false;
        }

        if (in_array($newStatut, ['valide', 'rejete'], true)) {
            $this->model->query(
                'UPDATE demandes_document SET
                    statut = ?,
                    valide_par = ?,
                    validee_le = NOW(),
                    note_validation = ?
                 WHERE id = ?',
                [
                    $newStatut,
                    $staffUserId,
                    $newStatut === 'rejete' ? trim((string) $note) : null,
                    $id,
                ]
            );

            return true;
        }

        if ($newStatut === 'livre') {
            $this->model->query(
                'UPDATE demandes_document SET statut = ?, livree_le = NOW() WHERE id = ?',
                [$newStatut, $id]
            );

            return true;
        }

        if ($newStatut === 'en_validation') {
            $this->model->query(
                'UPDATE demandes_document SET
                    statut = ?,
                    valide_par = NULL,
                    validee_le = NULL,
                    livree_le = NULL,
                    note_validation = NULL
                 WHERE id = ?',
                [$newStatut, $id]
            );

            return true;
        }

        return false;
    }
}
