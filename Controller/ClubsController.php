<?php

/**
 * ClubsController - Handles all club management (backoffice and frontoffice)
 * 
 * Responsibilities:
 * - Admin club management (create, read, update, delete, approve, reject)
 * - Statistics and analytics for clubs
 * - User club requests and approvals
 */

declare(strict_types=1);

require_once __DIR__ . '/../Model/Club.php';
require_once __DIR__ . '/../Service/ValidationService.php';

class ClubsController extends Controller
{
    // ===== HELPER METHODS =====
    
    private function isPostRequest(): bool
    {
        return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST';
    }

    private function isRequesterRole(): bool
    {
        $role = (string) ($_SESSION['user']['role'] ?? '');
        return in_array($role, ['etudiant', 'enseignant'], true);
    }

    private function isAdminOrStaff(): bool
    {
        $role = (string) ($_SESSION['user']['role'] ?? '');
        return in_array($role, ['staff', 'admin'], true);
    }

    private function currentUserId(): int
    {
        return (int) ($_SESSION['user']['id'] ?? 0);
    }

    private function canManageClub(int $clubId): bool
    {
        if ($this->isAdminOrStaff()) {
            return true;
        }

        $userId = $this->currentUserId();
        if ($userId <= 0) {
            return false;
        }

        $clubModel = new Club();
        return $clubModel->isOwner($clubId, $userId);
    }

    private function clubListPathForCurrentUser(): string
    {
        return $this->isAdminOrStaff() ? '/clubs/manage' : '/clubs';
    }

    // ===== STATISTICS METHODS =====
    
    /**
     * Get clubs statistics for dashboard display
     * 
     * @return array Statistics with keys: total, pending, active
     */
    private function getClubStats(): array
    {
        $clubModel = new Club();
        
        $allClubs = $clubModel->getAllAdmin();
        $totalClubs = count($allClubs);
        
        $pendingClubs = $clubModel->getPendingForAdmin();
        $pendingCount = count($pendingClubs);
        
        $activeClubs = array_filter($allClubs, static fn (array $club): bool => 
            (int) ($club['actif'] ?? 0) === 1
        );
        $activeCount = count($activeClubs);
        
        return [
            'total' => $totalClubs,
            'pending' => $pendingCount,
            'active' => $activeCount,
        ];
    }

    // ===== BACKOFFICE ROUTES =====
    
    /**
     * Display club management dashboard with statistics
     */
    public function manage(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        $clubModel = new Club();
        $pendingClubs = $clubModel->getPendingForAdmin();
        $clubs = array_values(array_filter(
            $clubModel->getAllAdmin(),
            static fn (array $club): bool => (int) ($club['actif'] ?? 0) === 1
        ));
        $q = trim((string) ($_GET['q'] ?? ''));

        // Apply search filter
        if ($q !== '') {
            $pendingClubs = array_values(array_filter($pendingClubs, static function (array $club) use ($q): bool {
                $haystack = strtolower((string) ($club['nom'] ?? '') . ' ' . (string) ($club['email_contact'] ?? ''));
                return str_contains($haystack, strtolower($q));
            }));

            $clubs = array_values(array_filter($clubs, static function (array $club) use ($q): bool {
                $haystack = strtolower((string) ($club['nom'] ?? '') . ' ' . (string) ($club['email_contact'] ?? ''));
                return str_contains($haystack, strtolower($q));
            }));
        }

        $stats = $this->getClubStats();

        $this->render('backoffice/clubs/index', [
            'title' => 'Gestion des clubs',
            'stats' => $stats,
            'pendingClubs' => $pendingClubs,
            'clubs' => $clubs,
            'q' => $q,
            'success' => (string) ($_GET['success'] ?? ''),
            'error' => (string) ($_GET['error'] ?? ''),
        ]);
    }

    /**
     * Show create club form
     */
    public function createForm(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        $this->render('backoffice/clubs/create', [
            'title' => 'Créer un club',
            'validationRules' => ValidationService::getClubValidationRules(),
            'old' => [
                'nom' => '',
                'description' => '',
                'email_contact' => '',
                'actif' => 1,
            ],
            'error' => null,
        ]);
    }

    /**
     * Create a new club (POST handler)
     */
    public function create(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->isPostRequest()) {
            $this->redirect('/clubs/createForm');
            return;
        }

        $payload = [
            'cree_par' => $this->currentUserId(),
            'nom' => trim((string) ($_POST['nom'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'email_contact' => trim((string) ($_POST['email_contact'] ?? '')),
            'actif' => isset($_POST['actif']) ? 1 : 0,
            'statut_validation' => 'approuve',
        ];

        // Validate input
        $validation = ValidationService::validateClubInput($payload);
        if (!$validation['valid']) {
            $error = reset($validation['errors']);
            $this->render('backoffice/clubs/create', [
                'title' => 'Créer un club',
                'validationRules' => ValidationService::getClubValidationRules(),
                'old' => $payload,
                'error' => $error,
            ]);
            return;
        }

        // Create club
        $clubModel = new Club();
        $createdId = $clubModel->create($payload);
        if ($createdId === false) {
            $this->render('backoffice/clubs/create', [
                'title' => 'Créer un club',
                'validationRules' => ValidationService::getClubValidationRules(),
                'old' => $payload,
                'error' => 'Impossible de créer le club.',
            ]);
            return;
        }

        $this->redirect('/clubs/manage?success=' . urlencode('Club créé avec succès.'));
    }

    /**
     * Show edit club form
     */
    public function editForm(int|string $id): void
    {
        $this->requireLogin();

        $clubId = (int) $id;
        if ($clubId <= 0) {
            $this->redirect('/clubs/manage?error=' . urlencode('Club invalide.'));
            return;
        }

        if (!$this->canManageClub($clubId)) {
            $this->redirect('/clubs?error=' . urlencode('Accès non autorisé.'));
            return;
        }

        $clubModel = new Club();
        $club = $clubModel->findById($clubId);
        if ($club === null) {
            $this->redirect($this->clubListPathForCurrentUser() . '?error=' . urlencode('Club introuvable.'));
            return;
        }

        $this->render('backoffice/clubs/edit', [
            'title' => 'Modifier un club',
            'validationRules' => ValidationService::getClubValidationRules(),
            'club' => $club,
            'error' => null,
        ]);
    }

    /**
     * Update a club (POST handler)
     */
    public function edit(int|string $id): void
    {
        $this->requireLogin();

        $clubId = (int) $id;
        if ($clubId <= 0) {
            $this->redirect($this->clubListPathForCurrentUser() . '?error=' . urlencode('Club invalide.'));
            return;
        }

        if (!$this->canManageClub($clubId)) {
            $this->redirect('/clubs?error=' . urlencode('Accès non autorisé.'));
            return;
        }

        if (!$this->isPostRequest()) {
            $this->redirect('/clubs/editForm/' . $clubId);
            return;
        }

        $clubModel = new Club();
        $club = $clubModel->findById($clubId);
        if ($club === null) {
            $this->redirect($this->clubListPathForCurrentUser() . '?error=' . urlencode('Club introuvable.'));
            return;
        }

        $payload = [
            'nom' => trim((string) ($_POST['nom'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'email_contact' => trim((string) ($_POST['email_contact'] ?? '')),
            'actif' => isset($_POST['actif']) ? 1 : 0,
        ];

        // Validate input
        $validation = ValidationService::validateClubInput($payload);
        if (!$validation['valid']) {
            $error = reset($validation['errors']);
            $this->render('backoffice/clubs/edit', [
                'title' => 'Modifier un club',
                'validationRules' => ValidationService::getClubValidationRules(),
                'club' => array_merge($club, $payload),
                'error' => $error,
            ]);
            return;
        }

        // Update club
        $updated = $clubModel->update($clubId, $payload);
        if (!$updated) {
            $this->render('backoffice/clubs/edit', [
                'title' => 'Modifier un club',
                'validationRules' => ValidationService::getClubValidationRules(),
                'club' => array_merge($club, $payload),
                'error' => 'Aucune modification enregistrée.',
            ]);
            return;
        }

        $this->redirect($this->clubListPathForCurrentUser() . '?success=' . urlencode('Club mis à jour.'));
    }

    /**
     * Delete a club
     */
    public function delete(int|string $id): void
    {
        $this->requireLogin();

        $clubId = (int) $id;
        if ($clubId <= 0) {
            $this->redirect($this->clubListPathForCurrentUser() . '?error=' . urlencode('Club invalide.'));
            return;
        }

        if (!$this->canManageClub($clubId)) {
            $this->redirect('/clubs?error=' . urlencode('Accès non autorisé.'));
            return;
        }

        if ($this->isPostRequest()) {
            $clubModel = new Club();
            $clubModel->delete($clubId);
        }

        $this->redirect($this->clubListPathForCurrentUser());
    }

    /**
     * Approve a pending club
     */
    public function approve(int|string $id): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->isPostRequest()) {
            $this->redirect('/clubs/manage');
            return;
        }

        $clubId = (int) $id;
        if ($clubId <= 0) {
            $this->redirect('/clubs/manage?error=' . urlencode('Club invalide.'));
            return;
        }

        $clubModel = new Club();
        $clubModel->approve($clubId, $this->currentUserId());
        $this->redirect('/clubs/manage?success=' . urlencode('Club approuvé.'));
    }

    /**
     * Reject a pending club
     */
    public function reject(int|string $id): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->isPostRequest()) {
            $this->redirect('/clubs/manage');
            return;
        }

        $clubId = (int) $id;
        if ($clubId <= 0) {
            $this->redirect('/clubs/manage?error=' . urlencode('Club invalide.'));
            return;
        }

        $clubModel = new Club();
        $clubModel->reject($clubId, $this->currentUserId());
        $this->redirect('/clubs/manage?success=' . urlencode('Club rejeté.'));
    }

    // ===== FRONTOFFICE ROUTES =====
    
    /**
     * Display list of clubs (frontoffice)
     */
    public function index(): void
    {
        $this->requireLogin();

        $clubModel = new Club();
        $clubs = $clubModel->getAll();
        $myClubs = [];
        if ($this->isRequesterRole()) {
            $myClubs = $clubModel->findByOwner($this->currentUserId());
        }

        $this->render('frontoffice/clubs/index', [
            'title' => 'Clubs actifs',
            'clubs' => $clubs,
            'myClubs' => $myClubs,
            'success' => (string) ($_GET['success'] ?? ''),
            'error' => (string) ($_GET['error'] ?? ''),
        ]);
    }

    /**
     * Show single club details (frontoffice)
     */
    public function show(int|string $id): void
    {
        $this->requireLogin();

        $clubId = (int) $id;
        if ($clubId <= 0) {
            $this->redirect('/clubs?error=' . urlencode('Club invalide.'));
            return;
        }

        $clubModel = new Club();
        $club = $clubModel->findById($clubId);
        if ($club === null || (int) ($club['actif'] ?? 0) !== 1) {
            $this->redirect('/clubs?error=' . urlencode('Club introuvable.'));
            return;
        }

        $events = $clubModel->getEventsForClub($clubId);

        $this->render('frontoffice/clubs/show', [
            'title' => 'Club',
            'club' => $club,
            'events' => $events,
            'success' => (string) ($_GET['success'] ?? ''),
            'error' => (string) ($_GET['error'] ?? ''),
        ]);
    }

    /**
     * Show club request form (frontoffice - for users to request new clubs)
     */
    public function createRequestForm(): void
    {
        $this->requireLogin();
        if (!$this->isRequesterRole()) {
            $this->redirect('/clubs');
            return;
        }

        $this->render('frontoffice/clubs/request_create', [
            'title' => 'Demander un club',
            'validationRules' => ValidationService::getClubValidationRules(),
            'old' => [
                'nom' => '',
                'description' => '',
                'email_contact' => '',
            ],
            'error' => null,
        ]);
    }

    /**
     * Submit a club request (frontoffice - for users to request new clubs)
     */
    public function createRequest(): void
    {
        $this->requireLogin();
        if (!$this->isRequesterRole()) {
            $this->redirect('/clubs');
            return;
        }

        if (!$this->isPostRequest()) {
            $this->redirect('/clubs/createRequestForm');
            return;
        }

        $payload = [
            'nom' => trim((string) ($_POST['nom'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'email_contact' => trim((string) ($_POST['email_contact'] ?? '')),
        ];

        // Validate input
        $validation = ValidationService::validateClubInput($payload);
        if (!$validation['valid']) {
            $error = reset($validation['errors']);
            $this->render('frontoffice/clubs/request_create', [
                'title' => 'Demander un club',
                'validationRules' => ValidationService::getClubValidationRules(),
                'old' => $payload,
                'error' => $error,
            ]);
            return;
        }

        // Create club with pending status
        $clubModel = new Club();
        $createdId = $clubModel->createWithOwner($payload, $this->currentUserId());
        if ($createdId === false) {
            $this->render('frontoffice/clubs/request_create', [
                'title' => 'Demander un club',
                'validationRules' => ValidationService::getClubValidationRules(),
                'old' => $payload,
                'error' => 'Impossible de soumettre la demande.',
            ]);
            return;
        }

        $this->redirect('/clubs?success=' . urlencode('Demande de club envoyée pour validation.'));
    }
}
