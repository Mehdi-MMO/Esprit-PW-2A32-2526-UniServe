<?php

declare(strict_types=1);

require_once __DIR__ . '/../Model/CategorieService.php';
require_once __DIR__ . '/../Model/DemandeDeService.php';

class DemandesController extends Controller
{
    private function isPost(): bool
    {
        return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST';
    }

    private function currentUserId(): int
    {
        return (int) ($_SESSION['user']['id'] ?? 0);
    }

    private function currentRole(): string
    {
        return (string) ($_SESSION['user']['role'] ?? '');
    }

    private function isEtudiant(): bool
    {
        return $this->currentRole() === 'etudiant';
    }

    private function isStaffOrAdmin(): bool
    {
        return in_array($this->currentRole(), ['staff', 'admin'], true);
    }

    /**
     * @param list<array<string, mixed>> $demandes
     * @return array{total: int, en_attente: int, en_cours: int, traite: int, rejete: int}
     */
    private function statsFromRows(array $demandes): array
    {
        $out = ['total' => count($demandes), 'en_attente' => 0, 'en_cours' => 0, 'traite' => 0, 'rejete' => 0];
        foreach ($demandes as $d) {
            $s = (string) ($d['statut'] ?? '');
            if ($s === 'en_attente') {
                $out['en_attente']++;
            } elseif ($s === 'en_cours') {
                $out['en_cours']++;
            } elseif ($s === 'traite') {
                $out['traite']++;
            } elseif ($s === 'rejete') {
                $out['rejete']++;
            }
        }

        return $out;
    }

    private function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    public function landing(): void
    {
        $this->index();
    }

    public function index(): void
    {
        $this->requireLogin();

        $catModel = new CategorieService();
        $demModel = new DemandeDeService();

        if ($this->isStaffOrAdmin()) {
            $statut = trim((string) ($_GET['statut'] ?? ''));
            $q = trim((string) ($_GET['q'] ?? ''));
            $filters = [];
            if ($statut !== '') {
                $filters['statut'] = $statut;
            }
            if ($q !== '') {
                $filters['q'] = $q;
            }

            $demandes = $demModel->findAllForAdmin($filters);
            $stats = $this->statsFromRows($demandes);
            $stats['categories_actives'] = $catModel->countActive();

            $staffList = (new User())->findStaffAndAdminsActifs();

            $this->render('backoffice/demandes/index', [
                'title' => 'Demandes de service',
                'demandes' => $demandes,
                'stats' => $stats,
                'statut_filter' => $statut,
                'q' => $q,
                'staff_list' => $staffList,
                'statut_labels' => $this->statutLabels(),
            ]);
            return;
        }

        if ($this->isEtudiant()) {
            $uid = $this->currentUserId();
            $demandes = $demModel->findAllForStudent($uid);
            $stats = $this->statsFromRows($demandes);
            $stats['categories_actives'] = $catModel->countActive();

            $this->render('frontoffice/demandes/index', [
                'title' => 'Mes demandes de service',
                'demandes' => $demandes,
                'stats' => $stats,
                'statut_labels' => $this->statutLabels(),
                'teacher_notice' => false,
            ]);
            return;
        }

        $this->render('frontoffice/demandes/index', [
            'title' => 'Demandes de service',
            'demandes' => [],
            'stats' => ['total' => 0, 'en_attente' => 0, 'en_cours' => 0, 'traite' => 0, 'rejete' => 0, 'categories_actives' => $catModel->countActive()],
            'statut_labels' => $this->statutLabels(),
            'teacher_notice' => true,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function statutLabels(): array
    {
        return [
            'en_attente' => 'En attente',
            'en_cours' => 'En cours',
            'traite' => 'Traitée',
            'rejete' => 'Rejetée',
        ];
    }

    public function createForm(): void
    {
        $this->requireLogin();
        if (!$this->isEtudiant()) {
            $this->redirectByUserRole($this->currentRole());
            return;
        }

        $categories = (new CategorieService())->findAllActive();

        $this->render('frontoffice/demandes/create', [
            'title' => 'Nouvelle demande',
            'categories' => $categories,
            'old' => [
                'categorie_id' => '',
                'titre' => '',
                'description' => '',
            ],
            'error' => null,
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();
        if (!$this->isEtudiant()) {
            $this->redirectByUserRole($this->currentRole());
            return;
        }

        if (!$this->isPost()) {
            $this->redirect('/demandes/createForm');
            return;
        }

        $payload = [
            'categorie_id' => (int) ($_POST['categorie_id'] ?? 0),
            'titre' => trim((string) ($_POST['titre'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
        ];

        $catModel = new CategorieService();
        $cat = $catModel->findById($payload['categorie_id']);
        if ($cat === null || (int) ($cat['actif'] ?? 0) !== 1) {
            $this->render('frontoffice/demandes/create', [
                'title' => 'Nouvelle demande',
                'categories' => $catModel->findAllActive(),
                'old' => $payload,
                'error' => 'Catégorie invalide ou inactive.',
            ]);
            return;
        }

        $demModel = new DemandeDeService();
        $newId = $demModel->create($this->currentUserId(), $payload);
        if ($newId === false) {
            $this->render('frontoffice/demandes/create', [
                'title' => 'Nouvelle demande',
                'categories' => $catModel->findAllActive(),
                'old' => $payload,
                'error' => 'Veuillez remplir tous les champs obligatoires.',
            ]);
            return;
        }

        $this->setFlash('success', 'Demande enregistrée.');
        $this->redirect('/demandes');
    }

    public function editForm(int|string $id): void
    {
        $this->requireLogin();
        if (!$this->isEtudiant()) {
            $this->redirectByUserRole($this->currentRole());
            return;
        }

        $demId = (int) $id;
        $demModel = new DemandeDeService();
        $demande = $demModel->findById($demId);

        if (
            $demande === null
            || (int) ($demande['etudiant_id'] ?? 0) !== $this->currentUserId()
            || (string) ($demande['statut'] ?? '') !== 'en_attente'
        ) {
            $this->redirect('/demandes');
            return;
        }

        $catModel = new CategorieService();

        $this->render('frontoffice/demandes/edit', [
            'title' => 'Modifier la demande',
            'demande' => $demande,
            'categories' => $catModel->findAllActive(),
            'error' => null,
        ]);
    }

    public function update(int|string $id): void
    {
        $this->requireLogin();
        if (!$this->isEtudiant()) {
            $this->redirectByUserRole($this->currentRole());
            return;
        }

        if (!$this->isPost()) {
            $this->redirect('/demandes/editForm/' . (int) $id);
            return;
        }

        $demId = (int) $id;
        $payload = [
            'categorie_id' => (int) ($_POST['categorie_id'] ?? 0),
            'titre' => trim((string) ($_POST['titre'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
        ];

        $demModel = new DemandeDeService();
        $demandeRow = $demModel->findById($demId);
        if (
            $demandeRow === null
            || (int) ($demandeRow['etudiant_id'] ?? 0) !== $this->currentUserId()
            || (string) ($demandeRow['statut'] ?? '') !== 'en_attente'
        ) {
            $this->redirect('/demandes');
            return;
        }

        $catModel = new CategorieService();
        $cat = $catModel->findById($payload['categorie_id']);
        if ($cat === null || (int) ($cat['actif'] ?? 0) !== 1) {
            $this->render('frontoffice/demandes/edit', [
                'title' => 'Modifier la demande',
                'demande' => array_merge($demandeRow, [
                    'categorie_id' => $payload['categorie_id'],
                    'titre' => $payload['titre'],
                    'description' => $payload['description'],
                ]),
                'categories' => $catModel->findAllActive(),
                'error' => 'Catégorie invalide ou inactive.',
            ]);
            return;
        }

        $ok = $demModel->updateByStudent($demId, $this->currentUserId(), $payload);
        if (!$ok) {
            $demande = $demModel->findById($demId);
            $this->render('frontoffice/demandes/edit', [
                'title' => 'Modifier la demande',
                'demande' => array_merge($demande ?? [], [
                    'categorie_id' => $payload['categorie_id'],
                    'titre' => $payload['titre'],
                    'description' => $payload['description'],
                ]),
                'categories' => $catModel->findAllActive(),
                'error' => 'Modification impossible (demande introuvable ou déjà traitée).',
            ]);
            return;
        }

        $this->setFlash('success', 'Demande mise à jour.');
        $this->redirect('/demandes');
    }

    public function delete(int|string $id): void
    {
        $this->requireLogin();
        if (!$this->isEtudiant()) {
            $this->redirectByUserRole($this->currentRole());
            return;
        }

        if (!$this->isPost()) {
            $this->redirect('/demandes');
            return;
        }

        $demModel = new DemandeDeService();
        $ok = $demModel->deleteByStudent((int) $id, $this->currentUserId());
        $this->setFlash($ok ? 'success' : 'danger', $ok ? 'Demande supprimée.' : 'Suppression impossible.');

        $this->redirect('/demandes');
    }

    public function updateStatut(int|string $id): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->isPost()) {
            $this->redirect('/demandes');
            return;
        }

        $statut = trim((string) ($_POST['statut'] ?? ''));
        if (!in_array($statut, DemandeDeService::allowedStatuts(), true)) {
            $this->setFlash('danger', 'Statut invalide.');
            $this->redirect('/demandes');
            return;
        }

        $demModel = new DemandeDeService();
        $ok = $demModel->updateStatut((int) $id, $statut);
        $this->setFlash($ok ? 'success' : 'danger', $ok ? 'Statut mis à jour.' : 'Mise à jour impossible.');

        $this->redirect('/demandes');
    }

    public function assign(int|string $id): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->isPost()) {
            $this->redirect('/demandes');
            return;
        }

        $raw = trim((string) ($_POST['assigne_a'] ?? ''));
        $assigneeId = $raw === '' ? null : (int) $raw;

        if ($assigneeId !== null && $assigneeId <= 0) {
            $this->setFlash('danger', 'Assignation invalide.');
            $this->redirect('/demandes');
            return;
        }

        if ($assigneeId !== null) {
            $u = (new User())->findById($assigneeId);
            $role = (string) ($u['role'] ?? '');
            if ($u === null || !in_array($role, ['staff', 'admin'], true)) {
                $this->setFlash('danger', 'Utilisateur staff/admin introuvable.');
                $this->redirect('/demandes');
                return;
            }
        }

        $ok = (new DemandeDeService())->updateAssignee((int) $id, $assigneeId);
        $this->setFlash($ok ? 'success' : 'danger', $ok ? 'Assignation enregistrée.' : 'Assignation impossible.');

        $this->redirect('/demandes');
    }

    public function adminDelete(int|string $id): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->isPost()) {
            $this->redirect('/demandes');
            return;
        }

        $ok = (new DemandeDeService())->deleteByAdmin((int) $id);
        $this->setFlash($ok ? 'success' : 'danger', $ok ? 'Demande supprimée.' : 'Suppression impossible.');

        $this->redirect('/demandes');
    }
}
