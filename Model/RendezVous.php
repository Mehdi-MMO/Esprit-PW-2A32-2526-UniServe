<?php

declare(strict_types=1);

/**
 * Student appointments (table rendez_vous).
 */
class RendezVous
{
    public const STATUTS = ['reserve', 'confirme', 'annule', 'termine'];

    private Model $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    /**
     * @return list<string>
     */
    public static function allowedStatuts(): array
    {
        return self::STATUTS;
    }

    private function normalizeMotif(?string $motif): ?string
    {
        $t = trim((string) $motif);

        return $t === '' ? null : $t;
    }

    /**
     * Same bureau: overlapping slot [start,end] vs existing row (excluding cancelled).
     */
    private function hasOverlap(int $bureauId, string $dateDebut, string $dateFin, ?int $excludeId): bool
    {
        $sql = 'SELECT COUNT(*) AS n FROM rendez_vous
                WHERE bureau_id = ?
                  AND statut NOT IN ("annule")
                  AND date_debut < ?
                  AND date_fin > ?';
        $params = [$bureauId, $dateFin, $dateDebut];

        if ($excludeId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $excludeId;
        }

        $statement = $this->model->query($sql, $params);
        $row = $statement->fetch();

        return (int) ($row['n'] ?? 0) > 0;
    }

    /**
     * Counts all rendez-vous by status (admin dashboard cards).
     *
     * @return array{total: int, reserve: int, confirme: int, annule: int, termine: int}
     */
    public function adminDashboardStats(): array
    {
        $out = ['total' => 0, 'reserve' => 0, 'confirme' => 0, 'annule' => 0, 'termine' => 0];
        $statement = $this->model->query(
            'SELECT statut, COUNT(*) AS c FROM rendez_vous GROUP BY statut',
            []
        );
        foreach ($statement->fetchAll() as $row) {
            $st = (string) ($row['statut'] ?? '');
            $n = (int) ($row['c'] ?? 0);
            $out['total'] += $n;
            if (array_key_exists($st, $out)) {
                $out[$st] = $n;
            }
        }

        return $out;
    }

    /**
     * @param array{statut?: string, q?: string} $filters
     */
    private function appendAdminListFilters(string &$sql, array &$params, array $filters): void
    {
        $statut = isset($filters['statut']) ? trim((string) $filters['statut']) : '';
        if ($statut !== '' && in_array($statut, self::STATUTS, true)) {
            $sql .= ' AND r.statut = ?';
            $params[] = $statut;
        }

        $q = isset($filters['q']) ? trim((string) $filters['q']) : '';
        if ($q !== '') {
            $like = '%' . $q . '%';
            $sql .= ' AND (
                r.motif LIKE ?
                OR u.nom LIKE ? OR u.prenom LIKE ? OR u.email LIKE ?
                OR b.nom LIKE ? OR b.localisation LIKE ?
            )';
            array_push($params, $like, $like, $like, $like, $like, $like);
        }
    }

    /**
     * @param array{statut?: string, q?: string} $filters
     */
    public function countForAdmin(array $filters = []): int
    {
        $sql = 'SELECT COUNT(*) AS n
                FROM rendez_vous r
                INNER JOIN utilisateurs u ON u.id = r.etudiant_id
                INNER JOIN bureaux b ON b.id = r.bureau_id
                WHERE 1=1';
        $params = [];
        $this->appendAdminListFilters($sql, $params, $filters);
        $statement = $this->model->query($sql, $params);
        $row = $statement->fetch();

        return (int) ($row['n'] ?? 0);
    }

    /**
     * @param array{statut?: string, q?: string} $filters
     * @return list<array<string, mixed>>
     */
    public function findAllForAdmin(
        array $filters = [],
        string $sort = 'date_desc',
        ?int $limit = null,
        int $offset = 0
    ): array {
        $sql = 'SELECT r.id, r.etudiant_id, r.bureau_id, r.motif, r.date_debut, r.date_fin, r.statut,
                       r.reserve_le, r.annule_le,
                       b.nom AS bureau_nom, b.localisation AS bureau_localisation,
                       u.prenom AS etudiant_prenom, u.nom AS etudiant_nom_seul,
                       CONCAT(u.prenom, " ", u.nom) AS etudiant_nom, u.email AS etudiant_email
                FROM rendez_vous r
                INNER JOIN utilisateurs u ON u.id = r.etudiant_id
                INNER JOIN bureaux b ON b.id = r.bureau_id
                WHERE 1=1';
        $params = [];
        $this->appendAdminListFilters($sql, $params, $filters);

        $dir = $sort === 'date_asc' ? 'ASC' : 'DESC';
        $sql .= ' ORDER BY r.date_debut ' . $dir;

        if ($limit !== null && $limit > 0) {
            $lim = max(1, (int) $limit);
            $off = max(0, (int) $offset);
            $sql .= ' LIMIT ' . $lim . ' OFFSET ' . $off;
        }

        $statement = $this->model->query($sql, $params);

        return $statement->fetchAll();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findAllForStudent(int $etudiantId): array
    {
        $statement = $this->model->query(
            'SELECT r.id, r.etudiant_id, r.bureau_id, r.motif, r.date_debut, r.date_fin, r.statut,
                    r.reserve_le, r.annule_le,
                    b.nom AS bureau_nom, b.localisation AS bureau_localisation
             FROM rendez_vous r
             INNER JOIN bureaux b ON b.id = r.bureau_id
             WHERE r.etudiant_id = ?
             ORDER BY r.date_debut DESC',
            [$etudiantId]
        );

        return $statement->fetchAll();
    }

    public function findById(int|string $id): ?array
    {
        $statement = $this->model->query(
            'SELECT r.id, r.etudiant_id, r.bureau_id, r.motif, r.date_debut, r.date_fin, r.statut,
                    r.reserve_le, r.annule_le,
                    b.nom AS bureau_nom, b.localisation AS bureau_localisation,
                    CONCAT(u.prenom, " ", u.nom) AS etudiant_nom, u.email AS etudiant_email
             FROM rendez_vous r
             INNER JOIN utilisateurs u ON u.id = r.etudiant_id
             INNER JOIN bureaux b ON b.id = r.bureau_id
             WHERE r.id = ?
             LIMIT 1',
            [(int) $id]
        );

        $row = $statement->fetch();

        return $row ?: null;
    }

    /**
     * @param array{bureau_id: int, motif: string, date_debut: string, date_fin: string} $data
     */
    public function create(int $etudiantId, array $data): int|false
    {
        if ($etudiantId <= 0) {
            return false;
        }

        $bureauId = (int) ($data['bureau_id'] ?? 0);
        $d1 = (string) ($data['date_debut'] ?? '');
        $d2 = (string) ($data['date_fin'] ?? '');
        $motif = $this->normalizeMotif($data['motif'] ?? null);

        if ($bureauId <= 0 || $d1 === '' || $d2 === '') {
            return false;
        }

        if ($this->hasOverlap($bureauId, $d1, $d2, null)) {
            return false;
        }

        $this->model->query(
            'INSERT INTO rendez_vous (etudiant_id, bureau_id, motif, date_debut, date_fin, statut)
             VALUES (?, ?, ?, ?, ?, "reserve")',
            [$etudiantId, $bureauId, $motif, $d1, $d2]
        );

        return (int) $this->model->lastInsertId();
    }

    /**
     * @param array{bureau_id: int, motif: string, date_debut: string, date_fin: string} $data
     */
    public function updateByStudent(int $id, int $etudiantId, array $data): bool
    {
        if ($id <= 0 || $etudiantId <= 0) {
            return false;
        }

        $bureauId = (int) ($data['bureau_id'] ?? 0);
        $d1 = (string) ($data['date_debut'] ?? '');
        $d2 = (string) ($data['date_fin'] ?? '');
        $motif = $this->normalizeMotif($data['motif'] ?? null);

        if ($bureauId <= 0 || $d1 === '' || $d2 === '') {
            return false;
        }

        if ($this->hasOverlap($bureauId, $d1, $d2, $id)) {
            return false;
        }

        $statement = $this->model->query(
            'UPDATE rendez_vous
             SET bureau_id = ?, motif = ?, date_debut = ?, date_fin = ?
             WHERE id = ? AND etudiant_id = ? AND statut = "reserve"',
            [$bureauId, $motif, $d1, $d2, $id, $etudiantId]
        );

        return $statement->rowCount() > 0;
    }

    public function cancelByStudent(int $id, int $etudiantId): bool
    {
        $statement = $this->model->query(
            'UPDATE rendez_vous
             SET statut = "annule", annule_le = NOW()
             WHERE id = ? AND etudiant_id = ?
               AND statut IN ("reserve", "confirme")',
            [$id, $etudiantId]
        );

        return $statement->rowCount() > 0;
    }

    public function updateStatut(int $id, string $statut): bool
    {
        if (!in_array($statut, self::STATUTS, true)) {
            return false;
        }

        if ($statut === 'annule') {
            $statement = $this->model->query(
                'UPDATE rendez_vous SET statut = ?, annule_le = NOW() WHERE id = ?',
                [$statut, $id]
            );
        } else {
            $statement = $this->model->query(
                'UPDATE rendez_vous SET statut = ?, annule_le = NULL WHERE id = ?',
                [$statut, $id]
            );
        }

        return $statement->rowCount() > 0;
    }

    public function deleteByAdmin(int $id): bool
    {
        $statement = $this->model->query(
            'DELETE FROM rendez_vous WHERE id = ?',
            [$id]
        );

        return $statement->rowCount() > 0;
    }
}
