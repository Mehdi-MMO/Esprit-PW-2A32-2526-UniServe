<?php

/**
 * EventsController - Handles all event management (backoffice and frontoffice)
 * 
 * Responsibilities:
 * - Admin event management (create, read, update, delete, approve, reject)
 * - Event registration and attendance tracking
 * - Statistics and analytics for events
 * - User event requests and approvals
 */

declare(strict_types=1);

require_once __DIR__ . '/../Model/Event.php';
require_once __DIR__ . '/../Model/Club.php';
require_once __DIR__ . '/../Service/ValidationService.php';

class EventsController extends Controller
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

    private function canManageEvent(int $eventId): bool
    {
        if ($this->isAdminOrStaff()) {
            return true;
        }

        $userId = $this->currentUserId();
        if ($userId <= 0) {
            return false;
        }

        $eventModel = new Event();
        return $eventModel->canUserManageEvent($eventId, $userId);
    }

    private function eventListPathForCurrentUser(): string
    {
        return $this->isAdminOrStaff() ? '/events/manage' : '/events';
    }

    /**
     * Apply automatic status based on capacity
     */
    private function applyCapacityStatus(Event $eventModel, int $eventId): void
    {
        $event = $eventModel->findById($eventId);
        if ($event === null) {
            return;
        }

        $capacite = isset($event['capacite']) ? (int) $event['capacite'] : null;
        if ($capacite === null || $capacite <= 0) {
            return;
        }

        $count = $eventModel->countInscriptions($eventId);
        $statut = (string) ($event['statut'] ?? 'planifie');

        if ($count >= $capacite && in_array($statut, ['planifie', 'ouvert'], true)) {
            $eventModel->update($eventId, ['statut' => 'complet']);
            return;
        }

        if ($count < $capacite && $statut === 'complet') {
            $eventModel->update($eventId, ['statut' => 'ouvert']);
        }
    }

    // ===== STATISTICS METHODS =====
    
    /**
     * Get events statistics for dashboard display
     * 
     * @return array Statistics with keys: total, active, pending, total_registrations, upcoming
     */
    private function getEventStats(): array
    {
        $eventModel = new Event();
        
        $allEvents = $eventModel->getAllAdmin();
        $totalEvents = count($allEvents);
        
        $activeEvents = array_filter($allEvents, static fn (array $event): bool => 
            (string) ($event['statut'] ?? '') === 'ouvert'
        );
        $activeCount = count($activeEvents);
        
        $pendingEvents = $eventModel->getPendingForAdmin();
        $pendingCount = count($pendingEvents);
        
        $totalRegistrations = 0;
        foreach ($allEvents as $event) {
            $totalRegistrations += $eventModel->countInscriptions((int) ($event['id'] ?? 0));
        }
        
        $upcomingCount = 0;
        $now = time();
        foreach ($allEvents as $event) {
            $eventTime = strtotime((string) ($event['date_debut'] ?? ''));
            if ($eventTime !== false && $eventTime > $now) {
                $upcomingCount++;
            }
        }
        
        return [
            'total' => $totalEvents,
            'active' => $activeCount,
            'pending' => $pendingCount,
            'total_registrations' => $totalRegistrations,
            'upcoming' => $upcomingCount,
        ];
    }

    // ===== BACKOFFICE ROUTES =====
    
    /**
     * Display event management dashboard with statistics
     */
    public function manage(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        $eventModel = new Event();
        $pendingEvents = $eventModel->getPendingForAdmin();
        $events = array_values(array_filter(
            $eventModel->getAllAdmin(),
            static fn (array $event): bool => (string) ($event['statut'] ?? '') !== 'planifie'
        ));
        $q = trim((string) ($_GET['q'] ?? ''));

        // Apply search filter
        if ($q !== '') {
            $pendingEvents = array_values(array_filter($pendingEvents, static function (array $event) use ($q): bool {
                $haystack = strtolower(
                    (string) ($event['titre'] ?? '') . ' ' .
                    (string) ($event['club_nom'] ?? '') . ' ' .
                    (string) ($event['lieu'] ?? '') . ' ' .
                    (string) ($event['statut'] ?? '')
                );
                return str_contains($haystack, strtolower($q));
            }));

            $events = array_values(array_filter($events, static function (array $event) use ($q): bool {
                $haystack = strtolower(
                    (string) ($event['titre'] ?? '') . ' ' .
                    (string) ($event['club_nom'] ?? '') . ' ' .
                    (string) ($event['lieu'] ?? '') . ' ' .
                    (string) ($event['statut'] ?? '')
                );
                return str_contains($haystack, strtolower($q));
            }));
        }

        $stats = $this->getEventStats();

        $this->render('backoffice/evenements/index', [
            'title' => 'Gestion des événements',
            'stats' => $stats,
            'pendingEvents' => $pendingEvents,
            'events' => $events,
            'q' => $q,
            'success' => (string) ($_GET['success'] ?? ''),
            'error' => (string) ($_GET['error'] ?? ''),
        ]);
    }

    /**
     * Show create event form
     */
    public function createForm(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        $clubModel = new Club();
        $clubs = $clubModel->getAllAdmin();

        $this->render('backoffice/evenements/create', [
            'title' => 'Créer un événement',
            'validationRules' => ValidationService::getEventValidationRules(),
            'clubs' => $clubs,
            'old' => [
                'club_id' => null,
                'titre' => '',
                'description' => '',
                'lieu' => '',
                'date_debut' => '',
                'date_fin' => '',
                'capacite' => null,
                'statut' => 'planifie',
            ],
            'error' => null,
        ]);
    }

    /**
     * Create a new event (POST handler)
     */
    public function create(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->isPostRequest()) {
            $this->redirect('/events/createForm');
            return;
        }

        $clubModel = new Club();
        $clubs = $clubModel->getAllAdmin();

        // Parse and normalize payload
        $payload = $this->parseEventPayload($_POST);

        // Validate input
        $validation = ValidationService::validateEventInput($payload, Event::allowedStatuses());
        if (!$validation['valid']) {
            $error = reset($validation['errors']);
            $this->render('backoffice/evenements/create', [
                'title' => 'Créer un événement',
                'validationRules' => ValidationService::getEventValidationRules(),
                'clubs' => $clubs,
                'old' => $payload,
                'error' => $error,
            ]);
            return;
        }

        // Validate club exists
        if ($payload['club_id'] !== null && $payload['club_id'] > 0) {
            if ($clubModel->findById($payload['club_id']) === null) {
                $this->render('backoffice/evenements/create', [
                    'title' => 'Créer un événement',
                    'validationRules' => ValidationService::getEventValidationRules(),
                    'clubs' => $clubs,
                    'old' => $payload,
                    'error' => 'Club introuvable.',
                ]);
                return;
            }
        }

        // Create event
        $currentUser = $this->currentUser();
        $payload['cree_par'] = (int) ($currentUser['id'] ?? 0);

        $eventModel = new Event();
        $eventId = $eventModel->create($payload);
        if ($eventId === false) {
            $this->render('backoffice/evenements/create', [
                'title' => 'Créer un événement',
                'validationRules' => ValidationService::getEventValidationRules(),
                'clubs' => $clubs,
                'old' => $payload,
                'error' => 'Impossible de créer l\'événement.',
            ]);
            return;
        }

        $this->applyCapacityStatus($eventModel, (int) $eventId);
        $this->redirect('/events/manage?success=' . urlencode('Événement créé avec succès.'));
    }

    /**
     * Show edit event form
     */
    public function editForm(int|string $id): void
    {
        $this->requireLogin();

        $eventId = (int) $id;
        if ($eventId <= 0) {
            $this->redirect($this->eventListPathForCurrentUser() . '?error=' . urlencode('Événement invalide.'));
            return;
        }

        if (!$this->canManageEvent($eventId)) {
            $this->redirect('/events?error=' . urlencode('Accès non autorisé.'));
            return;
        }

        $eventModel = new Event();
        $event = $eventModel->findById($eventId);
        if ($event === null) {
            $this->redirect($this->eventListPathForCurrentUser() . '?error=' . urlencode('Événement introuvable.'));
            return;
        }

        $clubModel = new Club();
        $clubs = $this->isAdminOrStaff()
            ? $clubModel->getAllAdmin()
            : $clubModel->findByOwner($this->currentUserId());

        $this->render('backoffice/evenements/edit', [
            'title' => 'Modifier un événement',
            'validationRules' => ValidationService::getEventValidationRules(),
            'clubs' => $clubs,
            'event' => $event,
            'error' => null,
        ]);
    }

    /**
     * Update an event (POST handler)
     */
    public function edit(int|string $id): void
    {
        $this->requireLogin();

        $eventId = (int) $id;
        if ($eventId <= 0) {
            $this->redirect($this->eventListPathForCurrentUser() . '?error=' . urlencode('Événement invalide.'));
            return;
        }

        if (!$this->canManageEvent($eventId)) {
            $this->redirect('/events?error=' . urlencode('Accès non autorisé.'));
            return;
        }

        if (!$this->isPostRequest()) {
            $this->redirect('/events/editForm/' . $eventId);
            return;
        }

        $eventModel = new Event();
        $existingEvent = $eventModel->findById($eventId);
        if ($existingEvent === null) {
            $this->redirect($this->eventListPathForCurrentUser() . '?error=' . urlencode('Événement introuvable.'));
            return;
        }

        $clubModel = new Club();
        $clubs = $this->isAdminOrStaff()
            ? $clubModel->getAllAdmin()
            : $clubModel->findByOwner($this->currentUserId());

        // Parse and normalize payload
        $payload = $this->parseEventPayload($_POST);

        // Validate input
        $validation = ValidationService::validateEventInput($payload, Event::allowedStatuses());
        if (!$validation['valid']) {
            $error = reset($validation['errors']);
            $event = array_merge($existingEvent, $payload);
            $this->render('backoffice/evenements/edit', [
                'title' => 'Modifier un événement',
                'validationRules' => ValidationService::getEventValidationRules(),
                'clubs' => $clubs,
                'event' => $event,
                'error' => $error,
            ]);
            return;
        }

        // Validate club exists
        if ($payload['club_id'] !== null && $payload['club_id'] > 0) {
            if ($clubModel->findById($payload['club_id']) === null) {
                $this->render('backoffice/evenements/edit', [
                    'title' => 'Modifier un événement',
                    'validationRules' => ValidationService::getEventValidationRules(),
                    'clubs' => $clubs,
                    'event' => array_merge($existingEvent, $payload),
                    'error' => 'Club introuvable.',
                ]);
                return;
            }
        }

        // Update event
        $updated = $eventModel->update($eventId, $payload);
        if (!$updated) {
            $event = array_merge($existingEvent, $payload);
            $this->render('backoffice/evenements/edit', [
                'title' => 'Modifier un événement',
                'validationRules' => ValidationService::getEventValidationRules(),
                'clubs' => $clubs,
                'event' => $event,
                'error' => 'Aucune modification enregistrée.',
            ]);
            return;
        }

        $this->applyCapacityStatus($eventModel, $eventId);
        $this->redirect($this->eventListPathForCurrentUser() . '?success=' . urlencode('Événement mis à jour.'));
    }

    /**
     * Delete an event
     */
    public function delete(int|string $id): void
    {
        $this->requireLogin();

        $eventId = (int) $id;
        if ($eventId <= 0) {
            $this->redirect($this->eventListPathForCurrentUser() . '?error=' . urlencode('Événement invalide.'));
            return;
        }

        if (!$this->canManageEvent($eventId)) {
            $this->redirect('/events?error=' . urlencode('Accès non autorisé.'));
            return;
        }

        if ($this->isPostRequest()) {
            $eventModel = new Event();
            $eventModel->delete($eventId);
        }

        $this->redirect($this->eventListPathForCurrentUser());
    }

    /**
     * Approve a pending event
     */
    public function approve(int|string $id): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->isPostRequest()) {
            $this->redirect('/events/manage');
            return;
        }

        $eventId = (int) $id;
        if ($eventId <= 0) {
            $this->redirect('/events/manage?error=' . urlencode('Événement invalide.'));
            return;
        }

        $eventModel = new Event();
        $eventModel->approve($eventId, $this->currentUserId());
        $this->redirect('/events/manage?success=' . urlencode('Événement approuvé.'));
    }

    /**
     * Reject a pending event
     */
    public function reject(int|string $id): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->isPostRequest()) {
            $this->redirect('/events/manage');
            return;
        }

        $eventId = (int) $id;
        if ($eventId <= 0) {
            $this->redirect('/events/manage?error=' . urlencode('Événement invalide.'));
            return;
        }

        $eventModel = new Event();
        $eventModel->reject($eventId, $this->currentUserId());
        $this->redirect('/events/manage?success=' . urlencode('Événement rejeté.'));
    }

    /**
     * Display event registrations for a specific event
     */
    public function inscriptions(int|string $id): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        $eventId = (int) $id;
        if ($eventId <= 0) {
            $this->redirect('/events/manage?error=' . urlencode('Événement invalide.'));
            return;
        }

        $eventModel = new Event();
        $event = $eventModel->findById($eventId);
        if ($event === null) {
            $this->redirect('/events/manage?error=' . urlencode('Événement introuvable.'));
            return;
        }

        $inscriptions = $eventModel->getInscriptions($eventId);

        $this->render('backoffice/evenements/inscriptions', [
            'title' => 'Inscriptions événement',
            'event' => $event,
            'inscriptions' => $inscriptions,
            'success' => (string) ($_GET['success'] ?? ''),
            'error' => (string) ($_GET['error'] ?? ''),
        ]);
    }

    /**
     * Check in a user for an event (mark attendance)
     */
    public function checkIn(int|string $eventId, int|string $userId): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->isPostRequest()) {
            $this->redirect('/events/inscriptions/' . (int) $eventId);
            return;
        }

        $eventIdInt = (int) $eventId;
        $userIdInt = (int) $userId;

        if ($eventIdInt <= 0 || $userIdInt <= 0) {
            $this->redirect('/events/manage?error=' . urlencode('Paramètres invalides.'));
            return;
        }

        $eventModel = new Event();
        $ok = $eventModel->checkIn($eventIdInt, $userIdInt);

        if (!$ok) {
            $this->redirect('/events/inscriptions/' . $eventIdInt . '?error=' . urlencode('Impossible de valider la présence.'));
            return;
        }

        $this->redirect('/events/inscriptions/' . $eventIdInt . '?success=' . urlencode('Présence validée.'));
    }

    // ===== FRONTOFFICE ROUTES =====
    
    /**
     * Display list of upcoming events (frontoffice)
     */
    public function index(): void
    {
        $this->requireLogin();

        $eventModel = new Event();
        $events = $eventModel->getAllUpcoming();
        $myEvents = [];
        if ($this->isRequesterRole()) {
            $myEvents = $eventModel->findByOwner($this->currentUserId());
        }

        $this->render('frontoffice/evenements/index', [
            'title' => 'Événements à venir',
            'events' => $events,
            'myEvents' => $myEvents,
            'success' => (string) ($_GET['success'] ?? ''),
            'error' => (string) ($_GET['error'] ?? ''),
        ]);
    }

    /**
     * Show single event details (frontoffice)
     */
    public function show(int|string $id): void
    {
        $this->requireLogin();

        $eventId = (int) $id;
        if ($eventId <= 0) {
            $this->redirect('/events?error=' . urlencode('Événement invalide.'));
            return;
        }

        $eventModel = new Event();
        $event = $eventModel->findById($eventId);
        if ($event === null) {
            $this->redirect('/events?error=' . urlencode('Événement introuvable.'));
            return;
        }

        $currentUser = $this->currentUser();
        $userId = (int) ($currentUser['id'] ?? 0);
        $isRegistered = $userId > 0 ? $eventModel->isUserRegistered($eventId, $userId) : false;
        $registrations = $eventModel->countInscriptions($eventId);

        $this->render('frontoffice/evenements/show', [
            'title' => 'Détail événement',
            'event' => $event,
            'registrations' => $registrations,
            'isRegistered' => $isRegistered,
            'success' => (string) ($_GET['success'] ?? ''),
            'error' => (string) ($_GET['error'] ?? ''),
        ]);
    }

    /**
     * Register a user for an event
     */
    public function register(int|string $id): void
    {
        $this->requireLogin();

        if (!$this->isPostRequest()) {
            $this->redirect('/events/show/' . (int) $id);
            return;
        }

        $eventId = (int) $id;
        if ($eventId <= 0) {
            $this->redirect('/events?error=' . urlencode('Événement invalide.'));
            return;
        }

        $eventModel = new Event();
        $event = $eventModel->findById($eventId);
        if ($event === null) {
            $this->redirect('/events?error=' . urlencode('Événement introuvable.'));
            return;
        }

        $currentUser = $this->currentUser();
        $userId = (int) ($currentUser['id'] ?? 0);
        if ($userId <= 0) {
            $this->redirect('/auth/login');
            return;
        }

        if ($eventModel->isUserRegistered($eventId, $userId)) {
            $this->redirect('/events/show/' . $eventId . '?error=' . urlencode('Vous êtes déjà inscrit à cet événement.'));
            return;
        }

        $statut = (string) ($event['statut'] ?? 'planifie');
        if ($statut !== 'ouvert') {
            $this->redirect('/events/show/' . $eventId . '?error=' . urlencode('Les inscriptions sont fermées pour cet événement.'));
            return;
        }

        $capacite = isset($event['capacite']) ? (int) $event['capacite'] : null;
        $inscriptions = $eventModel->countInscriptions($eventId);
        if ($capacite !== null && $capacite > 0 && $inscriptions >= $capacite) {
            $eventModel->update($eventId, ['statut' => 'complet']);
            $this->redirect('/events/show/' . $eventId . '?error=' . urlencode('Capacité maximale atteinte.'));
            return;
        }

        $registered = $eventModel->register($eventId, $userId);
        if (!$registered) {
            $this->redirect('/events/show/' . $eventId . '?error=' . urlencode('Inscription impossible.'));
            return;
        }

        $this->applyCapacityStatus($eventModel, $eventId);
        $this->redirect('/events/show/' . $eventId . '?success=' . urlencode('Inscription confirmée.'));
    }

    /**
     * Unregister a user from an event
     */
    public function unregister(int|string $id): void
    {
        $this->requireLogin();

        if (!$this->isPostRequest()) {
            $this->redirect('/events/show/' . (int) $id);
            return;
        }

        $eventId = (int) $id;
        if ($eventId <= 0) {
            $this->redirect('/events?error=' . urlencode('Événement invalide.'));
            return;
        }

        $eventModel = new Event();
        $event = $eventModel->findById($eventId);
        if ($event === null) {
            $this->redirect('/events?error=' . urlencode('Événement introuvable.'));
            return;
        }

        $currentUser = $this->currentUser();
        $userId = (int) ($currentUser['id'] ?? 0);
        if ($userId <= 0) {
            $this->redirect('/auth/login');
            return;
        }

        $ok = $eventModel->unregister($eventId, $userId);
        if (!$ok) {
            $this->redirect('/events/show/' . $eventId . '?error=' . urlencode('Aucune inscription active trouvée.'));
            return;
        }

        $this->applyCapacityStatus($eventModel, $eventId);
        $this->redirect('/events/show/' . $eventId . '?success=' . urlencode('Inscription annulée.'));
    }

    /**
     * Display user's event registrations (frontoffice)
     */
    public function mesInscriptions(): void
    {
        $this->requireLogin();

        $currentUser = $this->currentUser();
        $userId = (int) ($currentUser['id'] ?? 0);
        if ($userId <= 0) {
            $this->redirect('/auth/login');
            return;
        }

        $eventModel = new Event();
        $inscriptions = $eventModel->getUserInscriptions($userId);

        $this->render('frontoffice/evenements/mes_inscriptions', [
            'title' => 'Mes inscriptions',
            'inscriptions' => $inscriptions,
            'success' => (string) ($_GET['success'] ?? ''),
            'error' => (string) ($_GET['error'] ?? ''),
        ]);
    }

    /**
     * Show event request form (frontoffice - for club owners to submit events)
     */
    public function createRequestForm(): void
    {
        $this->requireLogin();
        if (!$this->isRequesterRole()) {
            $this->redirect('/events');
            return;
        }

        $clubModel = new Club();
        $clubs = array_values(array_filter(
            $clubModel->findByOwner($this->currentUserId()),
            static fn (array $club): bool => (string) ($club['statut_validation'] ?? '') !== 'rejete'
        ));

        $this->render('frontoffice/evenements/request_create', [
            'title' => 'Soumettre un événement',
            'validationRules' => ValidationService::getEventValidationRules(),
            'clubs' => $clubs,
            'old' => [
                'club_id' => null,
                'titre' => '',
                'description' => '',
                'lieu' => '',
                'date_debut' => '',
                'date_fin' => '',
                'capacite' => null,
            ],
            'error' => null,
        ]);
    }

    /**
     * Submit an event request (frontoffice - for club owners to submit events)
     */
    public function createRequest(): void
    {
        $this->requireLogin();
        if (!$this->isRequesterRole()) {
            $this->redirect('/events');
            return;
        }

        if (!$this->isPostRequest()) {
            $this->redirect('/events/createRequestForm');
            return;
        }

        $clubModel = new Club();
        $ownerId = $this->currentUserId();
        $clubs = array_values(array_filter(
            $clubModel->findByOwner($ownerId),
            static fn (array $club): bool => (string) ($club['statut_validation'] ?? '') !== 'rejete'
        ));

        // Parse and normalize payload
        $payload = $this->parseEventPayload($_POST);
        $payload['statut'] = 'planifie';

        // Validate input
        $validation = ValidationService::validateEventInput($payload, Event::allowedStatuses());
        if (!$validation['valid']) {
            $error = reset($validation['errors']);
            $this->render('frontoffice/evenements/request_create', [
                'title' => 'Soumettre un événement',
                'validationRules' => ValidationService::getEventValidationRules(),
                'clubs' => $clubs,
                'old' => $payload,
                'error' => $error,
            ]);
            return;
        }

        // Create event
        $eventModel = new Event();
        $createdId = $eventModel->createForClubOwner($payload, $ownerId);
        if ($createdId === false) {
            $this->render('frontoffice/evenements/request_create', [
                'title' => 'Soumettre un événement',
                'validationRules' => ValidationService::getEventValidationRules(),
                'clubs' => $clubs,
                'old' => $payload,
                'error' => 'Impossible de soumettre la demande (club non autorisé ?).',
            ]);
            return;
        }

        $this->redirect('/events?success=' . urlencode('Demande d\'événement envoyée pour validation.'));
    }

    // ===== PRIVATE HELPER METHODS =====
    
    /**
     * Parse event payload from POST data
     */
    private function parseEventPayload(array $source): array
    {
        $clubIdRaw = trim((string) ($source['club_id'] ?? ''));
        $capaciteRaw = trim((string) ($source['capacite'] ?? ''));
        $dateDebut = ValidationService::normalizeDateTimeInput((string) ($source['date_debut'] ?? ''));
        $dateFin = ValidationService::normalizeDateTimeInput((string) ($source['date_fin'] ?? ''));

        return [
            'club_id' => $clubIdRaw === '' ? null : (int) $clubIdRaw,
            'titre' => trim((string) ($source['titre'] ?? '')),
            'description' => trim((string) ($source['description'] ?? '')),
            'lieu' => trim((string) ($source['lieu'] ?? '')),
            'date_debut' => $dateDebut ?? '',
            'date_fin' => $dateFin ?? '',
            'capacite' => $capaciteRaw === '' ? null : (int) $capaciteRaw,
            'statut' => trim((string) ($source['statut'] ?? 'planifie')),
        ];
    }
}
