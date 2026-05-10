<?php

declare(strict_types=1);

class UtilisateursController extends Controller
{
    public function landing(): void
    {
        $this->index();
    }

    private function findUniqueAdminId(User $userModel): ?int
    {
        if ($userModel->countByRole('admin') !== 1) {
            return null;
        }

        return $userModel->findSingleAdminId();
    }

    private function resolveListState(User $userModel): array
    {
        $singleAdminId = $this->findUniqueAdminId($userModel);

        $q = trim((string) ($_GET['q'] ?? ''));
        $role = trim((string) ($_GET['role'] ?? ''));
        $statutCompte = trim((string) ($_GET['statut_compte'] ?? ''));

        $page = (int) ($_GET['page'] ?? 1);
        $page = $page > 0 ? $page : 1;

        $perPage = (int) ($_GET['per_page'] ?? 10);
        $perPage = in_array($perPage, [10, 25], true) ? $perPage : 10;

        $filters = [
            'q' => $q,
            'role' => $role,
            'statut_compte' => $statutCompte,
        ];

        $sort = 'id';
        $dir = 'DESC';

        $total = $userModel->countFiltered($filters);
        $pages = $perPage > 0 ? (int) max(1, ceil($total / $perPage)) : 1;
        if ($page > $pages) {
            $page = $pages;
        }

        $offset = ($page - 1) * $perPage;
        $users = $userModel->findFiltered($filters, $perPage, $offset, $sort, $dir);
        $stats = [
            'total' => $userModel->countFiltered([]),
            'actif' => $userModel->countFiltered(['statut_compte' => 'actif']),
            'inactif' => $userModel->countFiltered(['statut_compte' => 'inactif']),
            'admin' => $userModel->countByRole('admin'),
            'staff' => $userModel->countByRole('staff'),
        ];

        return [
            'users' => $users,
            'singleAdminId' => $singleAdminId,
            'filters' => $filters,
            'stats' => $stats,
            'pagination' => [
                'page' => $page,
                'pages' => $pages,
                'total' => $total,
                'per_page' => $perPage,
            ],
        ];
    }

    public function index(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        $userModel = new User();
        $state = $this->resolveListState($userModel);

        $this->render('backoffice/utilisateurs/index', [
            'title' => 'Utilisateurs',
            'users' => $state['users'],
            'singleAdminId' => $state['singleAdminId'],
            'filters' => $state['filters'],
            'stats' => $state['stats'],
            'pagination' => $state['pagination'],
        ]);
    }

    public function ajax(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        header('Content-Type: application/json; charset=utf-8');

        $userModel = new User();
        $state = $this->resolveListState($userModel);
        $users = $state['users'];
        $singleAdminId = $state['singleAdminId'];
        $filters = $state['filters'];
        $pagination = $state['pagination'];
        $q = (string) ($filters['q'] ?? '');
        $role = (string) ($filters['role'] ?? '');
        $statutCompte = (string) ($filters['statut_compte'] ?? '');
        $page = (int) ($pagination['page'] ?? 1);
        $pages = (int) ($pagination['pages'] ?? 1);
        $total = (int) ($pagination['total'] ?? 0);
        $perPage = (int) ($pagination['per_page'] ?? 10);

        $rowsHtml = '';
        if (empty($users)) {
            $rowsHtml = '<tr><td colspan="7" class="text-center text-muted py-4 us-empty-state">Aucun utilisateur trouvé.</td></tr>';
        } else {
            foreach ($users as $u) {
                $id = (int) ($u['id'] ?? 0);
                $isSingleAdmin = $singleAdminId !== null && $id === $singleAdminId;

                $statut = (string) ($u['statut_compte'] ?? '');
                $badgeClass = $statut === 'actif' ? 'actif' : 'inactif';
                $roleValue = strtolower((string) ($u['role'] ?? ''));

                $editUrl = $this->url('/utilisateurs/edit/' . $id);
                $deleteUrl = $this->url('/utilisateurs/delete/' . $id);

                if ($isSingleAdmin) {
                    $deleteHtml = '<button class="btn btn-outline-danger btn-sm" type="button" disabled title="Suppression bloquée (admin unique)"> <i class="bi bi-trash me-1"></i>Supprimer</button>';
                } else {
                    $deleteHtml = '<form method="post" action="' . $deleteUrl . '" class="d-inline">
                                        <button class="btn btn-outline-danger btn-sm" type="submit" onclick="return confirm(\'Supprimer cet utilisateur ?\');">
                                            <i class="bi bi-trash me-1"></i>Supprimer
                                        </button>
                                    </form>';
                }

                $rowsHtml .= '<tr>';
                $rowsHtml .= '<td>';
                $rowsHtml .= '<div class="fw-semibold us-user-name">' .
                    htmlspecialchars((string) ($u['prenom'] ?? ''), ENT_QUOTES, 'UTF-8') . ' ' .
                    htmlspecialchars((string) ($u['nom'] ?? ''), ENT_QUOTES, 'UTF-8') .
                    '</div>';
                $rowsHtml .= '<div class="text-muted small us-user-meta">' . htmlspecialchars((string) ($u['departement'] ?? ''), ENT_QUOTES, 'UTF-8') . '</div>';
                $rowsHtml .= '</td>';
                $rowsHtml .= '<td>' . htmlspecialchars((string) ($u['email'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
                $rowsHtml .= '<td><span class="us-role-chip ' . htmlspecialchars($roleValue, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars(ucfirst($roleValue), ENT_QUOTES, 'UTF-8') . '</span></td>';
                $rowsHtml .= '<td>' . htmlspecialchars((string) ($u['matricule'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
                $rowsHtml .= '<td>' . htmlspecialchars((string) ($u['telephone'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
                $rowsHtml .= '<td><span class="us-status-chip ' . $badgeClass . '">' . htmlspecialchars(ucfirst($statut), ENT_QUOTES, 'UTF-8') . '</span></td>';
                $rowsHtml .= '<td class="text-end">';
                $rowsHtml .= '<div class="us-action-group">';
                $rowsHtml .= '<a class="btn btn-outline-primary btn-sm" href="' . $editUrl . '"><i class="bi bi-pencil me-1"></i>Modifier</a>';
                $rowsHtml .= $deleteHtml;
                $rowsHtml .= '</div>';
                $rowsHtml .= '</td>';
                $rowsHtml .= '</tr>';
            }
        }

        $baseParams = [];
        if ($q !== '') {
            $baseParams['q'] = $q;
        }
        if ($role !== '') {
            $baseParams['role'] = $role;
        }
        if ($statutCompte !== '') {
            $baseParams['statut_compte'] = $statutCompte;
        }
        if ($perPage !== 10) {
            $baseParams['per_page'] = $perPage;
        }
        $baseQuery = http_build_query($baseParams);
        $utilisateursUrl = $this->url('/utilisateurs');

        $makePageHref = function (int $targetPage) use ($utilisateursUrl, $baseQuery): string {
            if ($targetPage <= 0) {
                $targetPage = 1;
            }
            return $utilisateursUrl . ($baseQuery !== '' ? '?' . $baseQuery . '&' : '?') . 'page=' . $targetPage;
        };

        $paginationHtml = '';
        if ($pages > 1) {
            $paginationHtml = '<nav aria-label="Pagination"><ul class="pagination mb-0">';
            $prev = $page - 1;
            $next = $page + 1;

            $paginationHtml .= '<li class="page-item ' . ($page <= 1 ? 'disabled' : '') . '">';
            if ($page <= 1) {
                $paginationHtml .= '<a class="page-link" href="#" aria-disabled="true">Précédent</a>';
            } else {
                $paginationHtml .= '<a class="page-link" href="' . $makePageHref($prev) . '">Précédent</a>';
            }
            $paginationHtml .= '</li>';

            $start = max(1, $page - 2);
            $end = min($pages, $page + 2);
            for ($p = $start; $p <= $end; $p++) {
                $active = $p === $page ? 'active' : '';
                $paginationHtml .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . $makePageHref($p) . '">' . (int) $p . '</a></li>';
            }

            $paginationHtml .= '<li class="page-item ' . ($page >= $pages ? 'disabled' : '') . '">';
            if ($page >= $pages) {
                $paginationHtml .= '<a class="page-link" href="#" aria-disabled="true">Suivant</a>';
            } else {
                $paginationHtml .= '<a class="page-link" href="' . $makePageHref($next) . '">Suivant</a>';
            }
            $paginationHtml .= '</li>';

            $paginationHtml .= '</ul></nav>';
        }

        echo json_encode([
            'rowsHtml' => $rowsHtml,
            'paginationHtml' => $paginationHtml,
            'meta' => [
                'page' => $page,
                'pages' => $pages,
                'total' => $total,
                'per_page' => $perPage,
            ],
        ]);
        exit;
    }

    public function create(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        $error = null;
        $old = [
            'nom' => '',
            'prenom' => '',
            'email' => '',
            'role' => 'etudiant',
            'matricule' => '',
            'departement' => '',
            'niveau' => '',
            'telephone' => '',
            'statut_compte' => 'actif',
        ];

        $userModel = new User();
        $singleAdminId = $userModel->findSingleAdminId();

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $nom = $this->normalizeText((string) ($_POST['nom'] ?? ''));
            $prenom = $this->normalizeText((string) ($_POST['prenom'] ?? ''));
            $email = $this->normalizeEmail((string) ($_POST['email'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $role = (string) ($_POST['role'] ?? 'etudiant');
            $matricule = (string) ($_POST['matricule'] ?? '');
            $departement = (string) ($_POST['departement'] ?? '');
            $niveau = (string) ($_POST['niveau'] ?? '');
            $telephone = (string) ($_POST['telephone'] ?? '');
            $statutCompte = (string) ($_POST['statut_compte'] ?? 'actif');

            $old = [
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'role' => $role,
                'matricule' => $matricule,
                'departement' => $departement,
                'niveau' => $niveau,
                'telephone' => $telephone,
                'statut_compte' => $statutCompte,
            ];

            if ($nom === '' || $prenom === '' || $email === '' || $password === '') {
                $error = 'Nom, prénom, email et mot de passe sont obligatoires.';
            } elseif (($emailError = $this->validateInstitutionalEmail($email)) !== null) {
                $error = $emailError;
            } elseif (($passwordError = $this->validateMinLength($password, User::MIN_PASSWORD_LENGTH, 'Le mot de passe')) !== null) {
                $error = $passwordError;
            } elseif (!in_array($role, User::allowedRoles(), true)) {
                $error = 'Rôle invalide.';
            } elseif (!in_array($statutCompte, User::allowedStatuses(), true)) {
                $error = 'Statut du compte invalide.';
            } elseif ($role === 'admin' && $singleAdminId !== null) {
                $error = 'Un seul compte admin est autorisé.';
                $old['role'] = 'etudiant';
            } elseif ($userModel->emailExists($email)) {
                $error = 'Cet email existe déjà.';
            } else {
                $createdId = $userModel->create([
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'password' => $password,
                    'role' => $role,
                    'matricule' => $matricule,
                    'departement' => $departement,
                    'niveau' => $niveau,
                    'telephone' => $telephone,
                    'statut_compte' => $statutCompte,
                ]);

                if ($createdId === false) {
                    $error = 'Impossible de créer l’utilisateur.';
                } else {
                    $this->redirect('/utilisateurs');
                    return;
                }
            }
        }

        $this->render('backoffice/utilisateurs/create', [
            'title' => 'Créer un utilisateur',
            'error' => $error,
            'old' => $old,
            'singleAdminId' => $singleAdminId,
        ]);
    }

    public function edit(int|string $id): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        $userId = (int) $id;
        if ($userId <= 0) {
            $this->redirect('/utilisateurs');
            return;
        }

        $userModel = new User();
        $singleAdminId = $this->findUniqueAdminId($userModel);
        $isSingleAdminEditing = $singleAdminId !== null && $userId === $singleAdminId;
        $user = $userModel->findById($userId);
        if ($user === null) {
            $this->redirect('/utilisateurs');
            return;
        }

        $error = null;

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $existingRole = (string) ($user['role'] ?? 'etudiant');
            $existingStatutCompte = (string) ($user['statut_compte'] ?? 'actif');
            $existingMatricule = (string) ($user['matricule'] ?? '');
            $existingDepartement = (string) ($user['departement'] ?? '');
            $existingNiveau = (string) ($user['niveau'] ?? '');
            $existingTelephone = (string) ($user['telephone'] ?? '');

            $nom = $this->normalizeText((string) ($_POST['nom'] ?? ''));
            $prenom = $this->normalizeText((string) ($_POST['prenom'] ?? ''));
            $email = $this->normalizeEmail((string) ($_POST['email'] ?? ''));
            $role = $isSingleAdminEditing ? $existingRole : (string) ($_POST['role'] ?? 'etudiant');
            $matricule = $isSingleAdminEditing ? $existingMatricule : (string) ($_POST['matricule'] ?? '');
            $departement = $isSingleAdminEditing ? $existingDepartement : (string) ($_POST['departement'] ?? '');
            $niveau = $isSingleAdminEditing ? $existingNiveau : (string) ($_POST['niveau'] ?? '');
            $telephone = $isSingleAdminEditing ? $existingTelephone : (string) ($_POST['telephone'] ?? '');
            $statutCompte = $isSingleAdminEditing ? $existingStatutCompte : (string) ($_POST['statut_compte'] ?? 'actif');
            $newPassword = (string) ($_POST['new_password'] ?? '');

            if ($nom === '' || $prenom === '' || $email === '') {
                $error = 'Nom, prénom et email sont obligatoires.';
            } elseif (($emailError = $this->validateInstitutionalEmail($email)) !== null) {
                $error = $emailError;
            } elseif ($newPassword !== '' && ($passwordError = $this->validateMinLength($newPassword, User::MIN_PASSWORD_LENGTH, 'Le nouveau mot de passe')) !== null) {
                $error = $passwordError;
            } elseif (!in_array($role, User::allowedRoles(), true)) {
                $error = 'Rôle invalide.';
            } elseif (!in_array($statutCompte, User::allowedStatuses(), true)) {
                $error = 'Statut du compte invalide.';
            } elseif ($isSingleAdminEditing && $role !== 'admin') {
                $error = 'Vous ne pouvez pas changer le rôle du compte admin unique.';
            } elseif (!$isSingleAdminEditing && $role === 'admin' && $singleAdminId !== null) {
                $error = 'Un seul compte admin est autorisé.';
            } elseif ($isSingleAdminEditing && $statutCompte !== 'actif') {
                $error = 'Le compte admin unique doit rester actif.';
            } elseif ($userModel->emailExists($email, $userId)) {
                $error = 'Cet email existe déjà.';
            } else {
                $payload = [
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'role' => $role,
                    'matricule' => $matricule,
                    'departement' => $departement,
                    'niveau' => $niveau,
                    'telephone' => $telephone,
                    'statut_compte' => $statutCompte,
                ];

                if ($newPassword !== '') {
                    $payload['password'] = $newPassword;
                }

                $ok = $userModel->updateById($userId, $payload);
                if ($ok) {
                    $this->redirect('/utilisateurs');
                    return;
                }

                $error = 'Impossible de mettre à jour l’utilisateur (rien à modifier ?).';
            }
        }

        $this->render('backoffice/utilisateurs/edit', [
            'title' => 'Modifier un utilisateur',
            'error' => $error,
            'user' => $user,
            'singleAdminId' => $singleAdminId,
            'isSingleAdminEditing' => $isSingleAdminEditing,
        ]);
    }

    public function delete(int|string $id): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        $userId = (int) $id;
        if ($userId <= 0) {
            $this->redirect('/utilisateurs');
            return;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $userModel = new User();
            $singleAdminId = $this->findUniqueAdminId($userModel);
            $currentUserId = (int) ($_SESSION['user']['id'] ?? 0);

            if ($currentUserId > 0 && $userId === $currentUserId) {
                $this->redirect('/utilisateurs?error=' . urlencode('Vous ne pouvez pas supprimer votre propre compte.'));
                return;
            }

            if ($singleAdminId !== null && $userId === $singleAdminId) {
                $this->redirect('/utilisateurs?error=' . urlencode('Suppression bloquée : seul compte admin.'));
                return;
            }

            $userModel->deleteById($userId);
        }

        $this->redirect('/utilisateurs');
    }
}
