<?php

declare(strict_types=1);

class User
{
    public const MIN_PASSWORD_LENGTH = 8;
    public const ALLOWED_ROLES = ['etudiant', 'enseignant', 'staff', 'admin'];
    public const ALLOWED_STATUSES = ['actif', 'inactif'];

    private Model $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    private function hasDerniereConnexionColumn(): bool
    {
        $statement = $this->model->query(
            'SELECT COUNT(*) AS cnt
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?',
            ['utilisateurs', 'derniere_connexion']
        );

        $row = $statement->fetch();
        return (int) ($row['cnt'] ?? 0) > 0;
    }

    public static function allowedRoles(): array
    {
        return self::ALLOWED_ROLES;
    }

    public static function allowedStatuses(): array
    {
        return self::ALLOWED_STATUSES;
    }

    private function normalizeNullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);
        return $trimmed === '' ? null : $trimmed;
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->model->query(
            'SELECT id, nom, prenom, email, mot_de_passe_hash, role, matricule, departement, niveau, telephone, photo_profil, statut_compte
             FROM utilisateurs
             WHERE email = ?
             LIMIT 1',
            [$email]
        );

        $record = $statement->fetch();
        return $record ?: null;
    }

    public function findById(int|string $id): ?array
    {
        $statement = $this->model->query(
            'SELECT id, nom, prenom, email, mot_de_passe_hash, role, matricule, departement, niveau, telephone, photo_profil, statut_compte
             FROM utilisateurs
             WHERE id = ?
             LIMIT 1',
            [$id]
        );

        $record = $statement->fetch();
        return $record ?: null;
    }

    public function findAll(): array
    {
        $statement = $this->model->query(
            'SELECT id, nom, prenom, email, role, matricule, departement, niveau, telephone, statut_compte
             FROM utilisateurs
             ORDER BY id DESC'
        );

        return $statement->fetchAll();
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        if ($excludeId === null) {
            $statement = $this->model->query(
                'SELECT id FROM utilisateurs WHERE email = ? LIMIT 1',
                [$email]
            );
        } else {
            $statement = $this->model->query(
                'SELECT id FROM utilisateurs WHERE email = ? AND id <> ? LIMIT 1',
                [$email, $excludeId]
            );
        }

        return (bool) $statement->fetch();
    }

    public function verifyPasswordById(int|string $id, string $plainPassword): bool
    {
        $user = $this->findById($id);
        if ($user === null) {
            return false;
        }

        $hash = (string) ($user['mot_de_passe_hash'] ?? '');
        if ($hash === '') {
            return false;
        }

        return password_verify($plainPassword, $hash);
    }

    public function updatePasswordById(int|string $id, string $plainPassword): bool
    {
        if (strlen($plainPassword) < self::MIN_PASSWORD_LENGTH) {
            return false;
        }

        $statement = $this->model->query(
            'UPDATE utilisateurs SET mot_de_passe_hash = ? WHERE id = ?',
            [password_hash($plainPassword, PASSWORD_DEFAULT), (int) $id]
        );

        return $statement->rowCount() > 0;
    }

    public function create(array $data): int|false
    {
        $nom = trim((string) ($data['nom'] ?? ''));
        $prenom = trim((string) ($data['prenom'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        $role = (string) ($data['role'] ?? 'etudiant');
        $matricule = $this->normalizeNullableString($data['matricule'] ?? null);
        $departement = $this->normalizeNullableString($data['departement'] ?? null);
        $niveau = $this->normalizeNullableString($data['niveau'] ?? null);
        $telephone = $this->normalizeNullableString($data['telephone'] ?? null);
        $statutCompte = (string) ($data['statut_compte'] ?? 'actif');

        if ($nom === '' || $prenom === '' || $email === '' || $password === '') {
            return false;
        }

        if (!in_array($role, self::ALLOWED_ROLES, true)) {
            $role = 'etudiant';
        }

        if (!in_array($statutCompte, self::ALLOWED_STATUSES, true)) {
            $statutCompte = 'actif';
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $this->model->query(
            'INSERT INTO utilisateurs
                (nom, prenom, email, mot_de_passe_hash, role, matricule, departement, niveau, telephone, statut_compte)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [$nom, $prenom, $email, $hash, $role, $matricule, $departement, $niveau, $telephone, $statutCompte]
        );

        return (int) $this->model->lastInsertId();
    }

    public function updateById(int|string $id, array $data): bool
    {
        $allowed = [
            'nom' => 'nom',
            'prenom' => 'prenom',
            'email' => 'email',
            'role' => 'role',
            'matricule' => 'matricule',
            'departement' => 'departement',
            'niveau' => 'niveau',
            'telephone' => 'telephone',
            'photo_profil' => 'photo_profil',
            'statut_compte' => 'statut_compte',
        ];

        $sets = [];
        $params = [];

        foreach ($allowed as $inputKey => $column) {
            if (!array_key_exists($inputKey, $data)) {
                continue;
            }

            $value = $data[$inputKey];

            if (in_array($inputKey, ['matricule', 'departement', 'niveau', 'telephone'], true)) {
                $value = $this->normalizeNullableString($value);
            }

            if ($inputKey === 'nom' || $inputKey === 'prenom' || $inputKey === 'email') {
                $value = trim((string) $value);
            }

            if ($value === null && in_array($inputKey, ['nom', 'prenom', 'email'], true)) {
                continue;
            }

            $sets[] = "`{$column}` = ?";
            $params[] = $value;
        }

        $password = (string) ($data['password'] ?? '');
        if ($password !== '') {
            $sets[] = 'mot_de_passe_hash = ?';
            $params[] = password_hash($password, PASSWORD_DEFAULT);
        }

        if ($sets === []) {
            return false;
        }

        $sql = 'UPDATE utilisateurs SET ' . implode(', ', $sets) . ' WHERE id = ?';
        $params[] = (int) $id;

        $statement = $this->model->query($sql, $params);
        return $statement->rowCount() > 0;
    }

    public function deleteById(int|string $id): bool
    {
        $statement = $this->model->query('DELETE FROM utilisateurs WHERE id = ?', [(int) $id]);
        return $statement->rowCount() > 0;
    }

    public function countByRole(string $role): int
    {
        $statement = $this->model->query(
            'SELECT COUNT(*) AS cnt FROM utilisateurs WHERE role = ?',
            [$role]
        );

        $row = $statement->fetch();
        return (int) (($row['cnt'] ?? 0));
    }

    public function findSingleAdminId(): ?int
    {
        $statement = $this->model->query(
            'SELECT id FROM utilisateurs WHERE role = ? ORDER BY id DESC LIMIT 1',
            ['admin']
        );

        $row = $statement->fetch();
        if (!$row) {
            return null;
        }

        return (int) ($row['id'] ?? 0);
    }

    public function countFiltered(array $filters): int
    {
        [$whereSql, $params] = $this->buildFilteredWhere($filters);

        $sql = 'SELECT COUNT(*) AS cnt FROM utilisateurs';
        if ($whereSql !== '') {
            $sql .= ' WHERE ' . $whereSql;
        }

        $statement = $this->model->query($sql, $params);
        $row = $statement->fetch();
        return (int) (($row['cnt'] ?? 0));
    }

    public function findFiltered(array $filters, int $limit, int $offset, string $sort, string $dir): array
    {
        [$whereSql, $params] = $this->buildFilteredWhere($filters);

        // MariaDB can be picky about binding LIMIT/OFFSET; inline safe ints.
        $limit = max(1, (int) $limit);
        $offset = max(0, (int) $offset);

        $allowedSort = [
            'id' => 'id',
            'nom' => 'nom',
            'prenom' => 'prenom',
            'email' => 'email',
            'role' => 'role',
            'matricule' => 'matricule',
            'telephone' => 'telephone',
            'statut_compte' => 'statut_compte',
            'departement' => 'departement',
            'niveau' => 'niveau',
            'cree_le' => 'cree_le',
        ];

        $orderCol = $allowedSort[$sort] ?? 'id';
        $orderDir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';

        $sql = 'SELECT id, nom, prenom, email, role, matricule, departement, niveau, telephone, statut_compte
                FROM utilisateurs';
        if ($whereSql !== '') {
            $sql .= ' WHERE ' . $whereSql;
        }

        $sql .= " ORDER BY {$orderCol} {$orderDir} LIMIT {$limit} OFFSET {$offset}";

        $statement = $this->model->query($sql, $params);
        return $statement->fetchAll();
    }

    private function buildFilteredWhere(array $filters): array
    {
        $where = [];
        $params = [];

        $q = trim((string) ($filters['q'] ?? ''));
        $role = trim((string) ($filters['role'] ?? ''));
        $statutCompte = trim((string) ($filters['statut_compte'] ?? ''));

        if ($q !== '') {
            $like = '%' . $q . '%';
            $where[] = '(email LIKE ? OR nom LIKE ? OR prenom LIKE ?)';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if ($role !== '' && in_array($role, self::ALLOWED_ROLES, true)) {
            $where[] = 'role = ?';
            $params[] = $role;
        }

        if ($statutCompte !== '' && in_array($statutCompte, self::ALLOWED_STATUSES, true)) {
            $where[] = 'statut_compte = ?';
            $params[] = $statutCompte;
        }

        return [implode(' AND ', $where), $params];
    }

    /**
     * Update last login timestamp for a user
     */
    public function updateLastLogin(int|string $id): bool
    {
        if (!$this->hasDerniereConnexionColumn()) {
            return false;
        }

        $statement = $this->model->query(
            'UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = ?',
            [(int) $id]
        );

        return $statement->rowCount() > 0;
    }

    /**
     * Get active users today (users with derniere_connexion today)
     */
    public function countActiveToday(): int
    {
        if (!$this->hasDerniereConnexionColumn()) {
            return 0;
        }

        $statement = $this->model->query(
            'SELECT COUNT(*) AS cnt FROM utilisateurs 
             WHERE DATE(derniere_connexion) = DATE(NOW())'
        );

        $row = $statement->fetch();
        return (int) ($row['cnt'] ?? 0);
    }

    /**
     * Get active users this week
     */
    public function countActiveThisWeek(): int
    {
        if (!$this->hasDerniereConnexionColumn()) {
            return 0;
        }

        $statement = $this->model->query(
            'SELECT COUNT(*) AS cnt FROM utilisateurs 
             WHERE WEEK(derniere_connexion) = WEEK(NOW()) 
             AND YEAR(derniere_connexion) = YEAR(NOW())'
        );

        $row = $statement->fetch();
        return (int) ($row['cnt'] ?? 0);
    }

    /**
     * Get recent logins
     */
    public function getRecentLogins(int $limit = 10): array
    {
        if (!$this->hasDerniereConnexionColumn()) {
            return [];
        }

        $statement = $this->model->query(
            'SELECT id, nom, prenom, email, role, derniere_connexion 
             FROM utilisateurs 
             WHERE derniere_connexion IS NOT NULL
             ORDER BY derniere_connexion DESC 
             LIMIT ?',
            [$limit]
        );

        return $statement->fetchAll();
    }

    /**
     * Get login trend data for the past N days
     */
    public function getLoginTrendData(int $days = 7): array
    {
        if (!$this->hasDerniereConnexionColumn()) {
            return [];
        }

        $statement = $this->model->query(
            'SELECT DATE(derniere_connexion) as date, COUNT(*) as count
             FROM utilisateurs
             WHERE derniere_connexion IS NOT NULL 
             AND DATE(derniere_connexion) >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY DATE(derniere_connexion)
             ORDER BY DATE(derniere_connexion) ASC',
            [$days - 1]
        );

        return $statement->fetchAll();
    }
}

