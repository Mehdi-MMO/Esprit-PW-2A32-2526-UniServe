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

    /**
     * @return list<array<string, mixed>>
     */
    public function findStaffAndAdminsActifs(): array
    {
        $statement = $this->model->query(
            'SELECT id, nom, prenom, email, role
             FROM utilisateurs
             WHERE role IN ("staff", "admin") AND statut_compte = "actif"
             ORDER BY nom ASC, prenom ASC'
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

    public function touchLastLogin(int $userId): void
    {
        if ($userId <= 0) {
            return;
        }

        try {
            $this->model->query(
                'UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = ?',
                [$userId]
            );
        } catch (Throwable) {
            // Legacy schema without derniere_connexion
        }
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

    /**
     * True when there is exactly one admin account and $userId is that account.
     */
    public function isTheSingletonAdmin(int $userId): bool
    {
        if ($userId <= 0 || $this->countByRole('admin') !== 1) {
            return false;
        }

        $onlyId = $this->findSingleAdminId();

        return $onlyId !== null && $onlyId === $userId;
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
}

