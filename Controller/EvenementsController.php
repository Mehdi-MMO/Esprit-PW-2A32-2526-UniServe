<?php

declare(strict_types=1);

require_once __DIR__ . '/../Model/Event.php';
require_once __DIR__ . '/../Model/Club.php';
require_once __DIR__ . '/../Model/NotificationModel.php';
require_once __DIR__ . '/../Model/User.php';
require_once __DIR__ . '/../Model/Database.php';

class EvenementsController extends Controller
{
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

    private function notifyUser(int $userId, string $message, ?string $lien = null): void
    {
        if ($userId <= 0 || trim($message) === '') {
            return;
        }

        (new NotificationModel())->create($userId, $message, $lien);
    }

    private function notifyAllStaff(string $message, ?string $lien = null): void
    {
        $notif = new NotificationModel();
        foreach ((new User())->findStaffAndAdminsActifs() as $row) {
            $uid = (int) ($row['id'] ?? 0);
            if ($uid > 0) {
                $notif->create($uid, $message, $lien);
            }
        }
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
        return $this->isAdminOrStaff() ? '/evenements/manage' : '/evenements';
    }

    private function clubListPathForCurrentUser(): string
    {
        return $this->isAdminOrStaff() ? '/evenements/manageClubs' : '/evenements/clubs';
    }

    private function parseEventPayload(array $source): array
    {
        $clubIdRaw = trim((string) ($source['club_id'] ?? ''));
        $capaciteRaw = trim((string) ($source['capacite'] ?? ''));
        $dateDebut = $this->normalizeDateTimeInput((string) ($source['date_debut'] ?? ''));
        $dateFin = $this->normalizeDateTimeInput((string) ($source['date_fin'] ?? ''));

        return [
            'club_id' => $clubIdRaw === '' ? null : (int) $clubIdRaw,
            'titre' => trim((string) ($source['titre'] ?? '')),
            'description' => trim((string) ($source['description'] ?? '')),
            'lieu' => trim((string) ($source['lieu'] ?? '')),
            'date_debut' => $dateDebut ?? '',
            'date_fin' => $dateFin ?? '',
            'capacite' => $capaciteRaw === '' ? null : (int) $capaciteRaw,
            'prix_ticket' => round(max(0.0, (float) ($source['prix_ticket'] ?? 0)), 2),
            'statut' => trim((string) ($source['statut'] ?? 'planifie')),
        ];
    }

    private function normalizeDateTimeInput(string $value): ?string
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $trimmed);
        if ($date instanceof \DateTimeImmutable) {
            return $date->format('Y-m-d H:i:s');
        }

        $fallback = strtotime($trimmed);
        if ($fallback === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $fallback);
    }

    private function validateEventPayload(array $payload, Club $clubModel): ?string
    {
        if ($payload['titre'] === '') {
            return 'Le titre est obligatoire.';
        }

        if ($payload['date_debut'] === '' || $payload['date_fin'] === '') {
            return 'Les dates de debut et de fin sont obligatoires.';
        }

        $dateDebut = strtotime((string) $payload['date_debut']);
        $dateFin = strtotime((string) $payload['date_fin']);
        if ($dateDebut === false || $dateFin === false) {
            return 'Format de date invalide.';
        }

        if ($dateFin < $dateDebut) {
            return 'La date de fin doit etre posterieure ou egale a la date de debut.';
        }

        $clubId = $payload['club_id'];
        if ($clubId !== null && $clubId > 0 && $clubModel->findById($clubId) === null) {
            return 'Club introuvable.';
        }

        $capacite = $payload['capacite'];
        if ($capacite !== null && $capacite <= 0) {
            return 'La capacite doit etre un nombre positif.';
        }

        if (!in_array((string) $payload['statut'], Event::allowedStatuses(), true)) {
            return 'Statut d evenement invalide.';
        }

        return null;
    }

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

    private function absoluteUrl(string $path): string
    {
        $https = (string) ($_SERVER['HTTPS'] ?? '');
        $forwardedProto = (string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '');
        $isHttps = $https === 'on' || $https === '1' || strtolower($forwardedProto) === 'https';
        $scheme = $isHttps ? 'https' : 'http';
        $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');

        return $scheme . '://' . $host . $this->url($path);
    }

    private function createStripeCheckoutSession(array $event, int $userId): ?string
    {
        Database::ensureEnvLoaded();
        $secretKey = trim((string) (getenv('STRIPE_SECRET_KEY') ?: ''));
        if ($secretKey === '') {
            return null;
        }

        $eventId = (int) ($event['id'] ?? 0);
        $eventTitle = trim((string) ($event['titre'] ?? 'Evenement UniServe'));
        $eventModel = new Event();
        $ticketPrice = $eventModel->ticketPrice($event);
        $amount = (int) round($ticketPrice * 100);
        if ($amount <= 0) {
            return null;
        }

        $currency = strtolower(trim((string) (getenv('STRIPE_CURRENCY') ?: 'usd')));
        if ($currency === '') {
            $currency = 'usd';
        }

        $successUrl = $this->absoluteUrl(
            '/evenements/paymentSuccess?event_id=' . $eventId . '&session_id={CHECKOUT_SESSION_ID}'
        );
        $cancelUrl = $this->absoluteUrl('/evenements/paymentCancel?event_id=' . $eventId);

        $payload = http_build_query([
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'line_items[0][price_data][currency]' => $currency,
            'line_items[0][price_data][product_data][name]' => 'Inscription - ' . $eventTitle,
            'line_items[0][price_data][unit_amount]' => (string) $amount,
            'line_items[0][quantity]' => '1',
            'metadata[event_id]' => (string) $eventId,
            'metadata[user_id]' => (string) $userId,
        ]);

        if (!function_exists('curl_init')) {
            return null;
        }

        $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $secretKey,
                'Content-Type: application/x-www-form-urlencoded',
            ],
            CURLOPT_TIMEOUT => 25,
        ]);
        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if (!is_string($response) || $response === '' || $status >= 400 || $curlErr !== '') {
            return null;
        }

        $decoded = json_decode($response, true);
        $checkoutUrl = (string) (is_array($decoded) ? ($decoded['url'] ?? '') : '');

        return $checkoutUrl !== '' ? $checkoutUrl : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchStripeSession(string $sessionId): ?array
    {
        Database::ensureEnvLoaded();
        $secretKey = trim((string) (getenv('STRIPE_SECRET_KEY') ?: ''));
        if ($secretKey === '' || trim($sessionId) === '') {
            return null;
        }

        if (!function_exists('curl_init')) {
            return null;
        }

        $ch = curl_init('https://api.stripe.com/v1/checkout/sessions/' . rawurlencode($sessionId));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPGET => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $secretKey,
            ],
            CURLOPT_TIMEOUT => 25,
        ]);
        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if (!is_string($response) || $response === '' || $status >= 400 || $curlErr !== '') {
            return null;
        }

        $decoded = json_decode($response, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function notifyOrganizerOnRegistration(int $eventId, array $event, int $registrantUserId): void
    {
        $organizerId = (int) ($event['cree_par'] ?? 0);
        if ($organizerId <= 0 || $organizerId === $registrantUserId) {
            return;
        }

        $titreEvt = (string) ($event['titre'] ?? '');
        $this->notifyUser(
            $organizerId,
            'Nouvelle inscription à votre événement « ' . $titreEvt . ' ».',
            '/evenements/show/' . $eventId
        );
    }

    /**
     * @return array{total: int, pending: int, active: int}
     */
    private function getClubStats(): array
    {
        $clubModel = new Club();

        $allClubs = $clubModel->getAllAdmin();
        $totalClubs = count($allClubs);

        $pendingClubs = $clubModel->getPendingForAdmin();
        $pendingCount = count($pendingClubs);

        $activeClubs = array_filter(
            $allClubs,
            static fn (array $club): bool =>
                (string) ($club['actif'] ?? '0') === '1'
                && (string) ($club['statut_validation'] ?? '') === 'approuve'
        );
        $activeCount = count($activeClubs);

        return [
            'total' => $totalClubs,
            'pending' => $pendingCount,
            'active' => $activeCount,
        ];
    }

    /**
     * @return array{total: int, active: int, pending: int, total_registrations: int, upcoming: int}
     */
    private function getEventStats(): array
    {
        $eventModel = new Event();

        $allEvents = $eventModel->getAllAdmin();
        $totalEvents = count($allEvents);

        $activeEvents = array_filter(
            $allEvents,
            static fn (array $event): bool => (string) ($event['statut'] ?? '') === 'ouvert'
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

    public function landing(): void
    {
        $this->requireLogin();

        $role = (string) ($_SESSION['user']['role'] ?? '');
        if (in_array($role, ['staff', 'admin'], true)) {
            $this->manage();
            return;
        }

        $this->index();
    }

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

    public function show(int|string $id): void
    {
        $this->requireLogin();

        $eventId = (int) $id;
        if ($eventId <= 0) {
            $this->redirect('/evenements?error=' . urlencode('Evenement invalide.'));
            return;
        }

        $eventModel = new Event();
        $event = $eventModel->findById($eventId);
        if ($event === null) {
            $this->redirect('/evenements?error=' . urlencode('Evenement introuvable.'));
            return;
        }

        $currentUser = $this->currentUser();
        $userId = (int) ($currentUser['id'] ?? 0);
        $isRegistered = $userId > 0 ? $eventModel->isUserRegistered($eventId, $userId) : false;
        $registrations = $eventModel->countInscriptions($eventId);

        Database::ensureEnvLoaded();
        $ticketPrice = $eventModel->ticketPrice($event);
        $stripeReady = trim((string) (getenv('STRIPE_SECRET_KEY') ?: '')) !== '';
        $usdToTndRate = max(0.0, (float) (getenv('USD_TO_TND_RATE') ?: 3.10));

        $this->render('frontoffice/evenements/show', [
            'title' => 'Détail de l’événement',
            'event' => $event,
            'registrations' => $registrations,
            'isRegistered' => $isRegistered,
            'ticket_price' => $ticketPrice,
            'stripe_ready' => $stripeReady,
            'usd_to_tnd_rate' => $usdToTndRate,
            'success' => (string) ($_GET['success'] ?? ''),
            'error' => (string) ($_GET['error'] ?? ''),
        ]);
    }

    public function register(int|string $id): void
    {
        $this->requireLogin();

        if (!$this->isPostRequest()) {
            $this->redirect('/evenements/show/' . (int) $id);
            return;
        }

        $eventId = (int) $id;
        if ($eventId <= 0) {
            $this->redirect('/evenements?error=' . urlencode('Evenement invalide.'));
            return;
        }

        $eventModel = new Event();
        $event = $eventModel->findById($eventId);
        if ($event === null) {
            $this->redirect('/evenements?error=' . urlencode('Evenement introuvable.'));
            return;
        }

        $currentUser = $this->currentUser();
        $userId = (int) ($currentUser['id'] ?? 0);
        if ($userId <= 0) {
            $this->redirect('/auth/login');
            return;
        }

        if ($eventModel->isUserRegistered($eventId, $userId)) {
            $this->redirect('/evenements/show/' . $eventId . '?error=' . urlencode('Vous etes deja inscrit a cet evenement.'));
            return;
        }

        $statut = (string) ($event['statut'] ?? 'planifie');
        if ($statut !== 'ouvert') {
            $this->redirect('/evenements/show/' . $eventId . '?error=' . urlencode('Les inscriptions sont fermees pour cet evenement.'));
            return;
        }

        $capacite = isset($event['capacite']) ? (int) $event['capacite'] : null;
        $inscriptions = $eventModel->countInscriptions($eventId);
        if ($capacite !== null && $capacite > 0 && $inscriptions >= $capacite) {
            $eventModel->update($eventId, ['statut' => 'complet']);
            $this->redirect('/evenements/show/' . $eventId . '?error=' . urlencode('Capacite maximale atteinte.'));
            return;
        }

        $ticketPrice = $eventModel->ticketPrice($event);
        if ($ticketPrice > 0) {
            Database::ensureEnvLoaded();
            if (trim((string) (getenv('STRIPE_SECRET_KEY') ?: '')) === '') {
                $this->redirect(
                    '/evenements/show/' . $eventId . '?error=' . urlencode(
                        'Paiement indisponible. Verifiez STRIPE_SECRET_KEY dans .env ou mettez le prix du ticket a 0.'
                    )
                );
                return;
            }

            $checkoutUrl = $this->createStripeCheckoutSession($event, $userId);
            if ($checkoutUrl === null) {
                $this->redirect(
                    '/evenements/show/' . $eventId . '?error=' . urlencode('Paiement indisponible (Stripe ou reseau).')
                );
                return;
            }

            $this->redirect($checkoutUrl);
            return;
        }

        $registered = $eventModel->register($eventId, $userId);
        if (!$registered) {
            $this->redirect('/evenements/show/' . $eventId . '?error=' . urlencode('Inscription impossible.'));
            return;
        }

        $this->notifyOrganizerOnRegistration($eventId, $event, $userId);

        $this->applyCapacityStatus($eventModel, $eventId);
        $this->redirect('/evenements/show/' . $eventId . '?success=' . urlencode('Inscription confirmee.'));
    }

    public function paymentSuccess(): void
    {
        $this->requireLogin();

        $eventId = (int) ($_GET['event_id'] ?? 0);
        $sessionId = trim((string) ($_GET['session_id'] ?? ''));
        if ($eventId <= 0 || $sessionId === '') {
            $this->redirect('/evenements?error=' . urlencode('Retour paiement invalide.'));
            return;
        }

        $currentUser = $this->currentUser();
        $userId = (int) ($currentUser['id'] ?? 0);
        if ($userId <= 0) {
            $this->redirect('/auth/login');
            return;
        }

        $stripeSession = $this->fetchStripeSession($sessionId);
        if ($stripeSession === null) {
            $this->redirect('/evenements/show/' . $eventId . '?error=' . urlencode('Verification paiement impossible.'));
            return;
        }

        $paymentStatus = (string) ($stripeSession['payment_status'] ?? '');
        $metaUserId = (int) ($stripeSession['metadata']['user_id'] ?? 0);
        $metaEventId = (int) ($stripeSession['metadata']['event_id'] ?? 0);
        if ($paymentStatus !== 'paid' || $metaUserId !== $userId || $metaEventId !== $eventId) {
            $this->redirect('/evenements/show/' . $eventId . '?error=' . urlencode('Paiement non valide.'));
            return;
        }

        $eventModel = new Event();
        $event = $eventModel->findById($eventId);
        if ($event === null) {
            $this->redirect('/evenements?error=' . urlencode('Evenement introuvable.'));
            return;
        }

        if (!$eventModel->isUserRegistered($eventId, $userId)) {
            $ok = $eventModel->register($eventId, $userId);
            if (!$ok) {
                $this->redirect('/evenements/show/' . $eventId . '?error=' . urlencode('Inscription impossible apres paiement.'));
                return;
            }
            $event = $eventModel->findById($eventId) ?? $event;
            $this->notifyOrganizerOnRegistration($eventId, $event, $userId);
        }

        $this->applyCapacityStatus($eventModel, $eventId);
        $this->redirect('/evenements/show/' . $eventId . '?success=' . urlencode('Paiement confirme. Inscription validee.'));
    }

    public function paymentCancel(): void
    {
        $this->requireLogin();

        $eventId = (int) ($_GET['event_id'] ?? 0);
        if ($eventId <= 0) {
            $this->redirect('/evenements?error=' . urlencode('Paiement annule.'));
            return;
        }

        $this->redirect('/evenements/show/' . $eventId . '?error=' . urlencode('Paiement annule. Inscription non effectuee.'));
    }

    public function unregister(int|string $id): void
    {
        $this->requireLogin();

        if (!$this->isPostRequest()) {
            $this->redirect('/evenements/show/' . (int) $id);
            return;
        }

        $eventId = (int) $id;
        if ($eventId <= 0) {
            $this->redirect('/evenements?error=' . urlencode('Evenement invalide.'));
            return;
        }

        $eventModel = new Event();
        $event = $eventModel->findById($eventId);
        if ($event === null) {
            $this->redirect('/evenements?error=' . urlencode('Evenement introuvable.'));
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
            $this->redirect('/evenements/show/' . $eventId . '?error=' . urlencode('Aucune inscription active trouvee.'));
            return;
        }

        $this->applyCapacityStatus($eventModel, $eventId);
        $this->redirect('/evenements/show/' . $eventId . '?success=' . urlencode('Inscription annulee.'));
    }

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

    public function clubs(): void
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

    public function clubShow(int|string $id): void
    {
        $this->requireLogin();

        $clubId = (int) $id;
        if ($clubId <= 0) {
            $this->redirect('/evenements/clubs?error=' . urlencode('Club invalide.'));
            return;
        }

        $clubModel = new Club();
        $club = $clubModel->findById($clubId);
        if ($club === null || (int) ($club['actif'] ?? 0) !== 1) {
            $this->redirect('/evenements/clubs?error=' . urlencode('Club introuvable.'));
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

    public function createForm(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        $clubModel = new Club();
        $clubs = $clubModel->getAllAdmin();

        $this->render('backoffice/evenements/create', [
            'title' => 'Créer un événement',
            'clubs' => $clubs,
            'old' => [
                'club_id' => null,
                'titre' => '',
                'description' => '',
                'lieu' => '',
                'date_debut' => '',
                'date_fin' => '',
                'capacite' => null,
                'prix_ticket' => 0.0,
                'statut' => 'planifie',
            ],
            'error' => null,
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->isPostRequest()) {
            $this->redirect('/evenements/createForm');
            return;
        }

        $clubModel = new Club();
        $clubs = $clubModel->getAllAdmin();
        $payload = $this->parseEventPayload($_POST);
        $error = $this->validateEventPayload($payload, $clubModel);

        if ($error !== null) {
            $this->render('backoffice/evenements/create', [
                'title' => 'Créer un événement',
                'clubs' => $clubs,
                'old' => $payload,
                'error' => $error,
            ]);
            return;
        }

        $currentUser = $this->currentUser();
        $payload['cree_par'] = (int) ($currentUser['id'] ?? 0);

        $eventModel = new Event();
        $eventId = $eventModel->create($payload);
        if ($eventId === false) {
            $this->render('backoffice/evenements/create', [
                'title' => 'Créer un événement',
                'clubs' => $clubs,
                'old' => $payload,
                'error' => 'Impossible de creer l evenement.',
            ]);
            return;
        }

        $this->applyCapacityStatus($eventModel, (int) $eventId);
        $this->redirect('/evenements/manage?success=' . urlencode('Evenement cree avec succes.'));
    }

    public function editForm(int|string $id): void
    {
        $this->requireLogin();

        $eventId = (int) $id;
        if ($eventId <= 0) {
            $this->redirect($this->eventListPathForCurrentUser() . '?error=' . urlencode('Evenement invalide.'));
            return;
        }

        if (!$this->canManageEvent($eventId)) {
            $this->redirect('/evenements?error=' . urlencode('Acces non autorise.'));
            return;
        }

        $eventModel = new Event();
        $event = $eventModel->findById($eventId);
        if ($event === null) {
            $this->redirect($this->eventListPathForCurrentUser() . '?error=' . urlencode('Evenement introuvable.'));
            return;
        }

        $clubModel = new Club();
        $clubs = $this->isAdminOrStaff()
            ? $clubModel->getAllAdmin()
            : $clubModel->findByOwner($this->currentUserId());

        $this->render('backoffice/evenements/edit', [
            'title' => 'Modifier un événement',
            'clubs' => $clubs,
            'event' => $event,
            'error' => null,
        ]);
    }

    public function edit(int|string $id): void
    {
        $this->requireLogin();

        $eventId = (int) $id;
        if ($eventId <= 0) {
            $this->redirect($this->eventListPathForCurrentUser() . '?error=' . urlencode('Evenement invalide.'));
            return;
        }

        if (!$this->canManageEvent($eventId)) {
            $this->redirect('/evenements?error=' . urlencode('Acces non autorise.'));
            return;
        }

        if (!$this->isPostRequest()) {
            $this->redirect('/evenements/editForm/' . $eventId);
            return;
        }

        $eventModel = new Event();
        $existingEvent = $eventModel->findById($eventId);
        if ($existingEvent === null) {
            $this->redirect($this->eventListPathForCurrentUser() . '?error=' . urlencode('Evenement introuvable.'));
            return;
        }

        $clubModel = new Club();
        $clubs = $this->isAdminOrStaff()
            ? $clubModel->getAllAdmin()
            : $clubModel->findByOwner($this->currentUserId());
        $payload = $this->parseEventPayload($_POST);
        $error = $this->validateEventPayload($payload, $clubModel);

        if ($error !== null) {
            $event = array_merge($existingEvent, $payload);
            $this->render('backoffice/evenements/edit', [
                'title' => 'Modifier un événement',
                'clubs' => $clubs,
                'event' => $event,
                'error' => $error,
            ]);
            return;
        }

        $updated = $eventModel->update($eventId, $payload);
        if (!$updated) {
            $event = array_merge($existingEvent, $payload);
            $this->render('backoffice/evenements/edit', [
                'title' => 'Modifier un événement',
                'clubs' => $clubs,
                'event' => $event,
                'error' => 'Aucune modification enregistree.',
            ]);
            return;
        }

        $this->applyCapacityStatus($eventModel, $eventId);
        $this->redirect($this->eventListPathForCurrentUser() . '?success=' . urlencode('Evenement mis a jour.'));
    }

    public function delete(int|string $id): void
    {
        $this->requireLogin();

        $eventId = (int) $id;
        if ($eventId <= 0) {
            $this->redirect($this->eventListPathForCurrentUser() . '?error=' . urlencode('Evenement invalide.'));
            return;
        }

        if (!$this->canManageEvent($eventId)) {
            $this->redirect('/evenements?error=' . urlencode('Acces non autorise.'));
            return;
        }

        if ($this->isPostRequest()) {
            $eventModel = new Event();
            $eventModel->delete($eventId);
        }

        $this->redirect($this->eventListPathForCurrentUser());
    }

    public function inscriptions(int|string $id): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        $eventId = (int) $id;
        if ($eventId <= 0) {
            $this->redirect('/evenements/manage?error=' . urlencode('Evenement invalide.'));
            return;
        }

        $eventModel = new Event();
        $event = $eventModel->findById($eventId);
        if ($event === null) {
            $this->redirect('/evenements/manage?error=' . urlencode('Evenement introuvable.'));
            return;
        }

        $inscriptions = $eventModel->getInscriptions($eventId);

        $this->render('backoffice/evenements/inscriptions', [
            'title' => 'Inscriptions à l’événement',
            'event' => $event,
            'inscriptions' => $inscriptions,
            'success' => (string) ($_GET['success'] ?? ''),
            'error' => (string) ($_GET['error'] ?? ''),
        ]);
    }

    public function checkIn(int|string $eventId, int|string $userId): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->isPostRequest()) {
            $this->redirect('/evenements/inscriptions/' . (int) $eventId);
            return;
        }

        $eventIdInt = (int) $eventId;
        $userIdInt = (int) $userId;

        if ($eventIdInt <= 0 || $userIdInt <= 0) {
            $this->redirect('/evenements/manage?error=' . urlencode('Parametres invalides.'));
            return;
        }

        $eventModel = new Event();
        $ok = $eventModel->checkIn($eventIdInt, $userIdInt);

        if (!$ok) {
            $this->redirect('/evenements/inscriptions/' . $eventIdInt . '?error=' . urlencode('Impossible de valider la presence.'));
            return;
        }

        $this->redirect('/evenements/inscriptions/' . $eventIdInt . '?success=' . urlencode('Presence validee.'));
    }

    public function createEventRequestForm(): void
    {
        $this->requireLogin();
        if (!$this->isRequesterRole()) {
            $this->redirect('/evenements');
            return;
        }

        $clubModel = new Club();
        $clubs = array_values(array_filter(
            $clubModel->findByOwner($this->currentUserId()),
            static fn (array $club): bool => (string) ($club['statut_validation'] ?? '') !== 'rejete'
        ));

        $this->render('frontoffice/evenements/request_create', [
            'title' => 'Soumettre un événement',
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

    public function createEventRequest(): void
    {
        $this->requireLogin();
        if (!$this->isRequesterRole()) {
            $this->redirect('/evenements');
            return;
        }

        if (!$this->isPostRequest()) {
            $this->redirect('/evenements/createEventRequestForm');
            return;
        }

        $clubModel = new Club();
        $ownerId = $this->currentUserId();
        $clubs = array_values(array_filter(
            $clubModel->findByOwner($ownerId),
            static fn (array $club): bool => (string) ($club['statut_validation'] ?? '') !== 'rejete'
        ));
        $payload = $this->parseEventPayload($_POST);
        $payload['statut'] = 'planifie';
        $payload['prix_ticket'] = 0.0;
        $error = $this->validateEventPayload($payload, $clubModel);

        if ($error !== null) {
            $this->render('frontoffice/evenements/request_create', [
                'title' => 'Soumettre un événement',
                'clubs' => $clubs,
                'old' => $payload,
                'error' => $error,
            ]);
            return;
        }

        $eventModel = new Event();
        $createdId = $eventModel->createForClubOwner($payload, $ownerId);
        if ($createdId === false) {
            $this->render('frontoffice/evenements/request_create', [
                'title' => 'Soumettre un événement',
                'clubs' => $clubs,
                'old' => $payload,
                'error' => 'Impossible de soumettre la demande (club non autorise ?).',
            ]);
            return;
        }

        $titre = (string) ($payload['titre'] ?? '');
        $this->notifyAllStaff(
            'Nouvelle demande d’événement à valider : « ' . $titre . ' ».',
            '/evenements/manage'
        );

        $this->redirect('/evenements?success=' . urlencode('Demande d evenement envoyee pour validation.'));
    }

    public function approveEvent(int|string $id): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->isPostRequest()) {
            $this->redirect('/evenements/manage');
            return;
        }

        $eventId = (int) $id;
        if ($eventId <= 0) {
            $this->redirect('/evenements/manage?error=' . urlencode('Evenement invalide.'));
            return;
        }

        $eventModel = new Event();
        $event = $eventModel->findById($eventId);
        $eventModel->approve($eventId, $this->currentUserId());
        if (is_array($event)) {
            $creatorId = (int) ($event['cree_par'] ?? 0);
            $titreEvt = (string) ($event['titre'] ?? '');
            $this->notifyUser(
                $creatorId,
                'Votre événement « ' . $titreEvt . ' » a été approuvé et est ouvert aux inscriptions.',
                '/evenements/show/' . $eventId
            );
        }
        $this->redirect('/evenements/manage?success=' . urlencode('Evenement approuve.'));
    }

    public function rejectEvent(int|string $id): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->isPostRequest()) {
            $this->redirect('/evenements/manage');
            return;
        }

        $eventId = (int) $id;
        if ($eventId <= 0) {
            $this->redirect('/evenements/manage?error=' . urlencode('Evenement invalide.'));
            return;
        }

        $eventModel = new Event();
        $event = $eventModel->findById($eventId);
        $eventModel->reject($eventId, $this->currentUserId());
        if (is_array($event)) {
            $creatorId = (int) ($event['cree_par'] ?? 0);
            $titreEvt = (string) ($event['titre'] ?? '');
            $this->notifyUser(
                $creatorId,
                'Votre événement « ' . $titreEvt . ' » a été refusé (statut annulé).',
                '/evenements'
            );
        }
        $this->redirect('/evenements/manage?success=' . urlencode('Evenement rejete.'));
    }

    public function manageClubs(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        $clubModel = new Club();
        $pendingClubs = $clubModel->getPendingForAdmin();
        $clubs = array_values(array_filter(
            $clubModel->getAllAdmin(),
            static fn (array $club): bool => (string) ($club['statut_validation'] ?? '') !== 'en_attente'
        ));
        $q = trim((string) ($_GET['q'] ?? ''));

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

    public function createClubForm(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        $this->render('backoffice/clubs/create', [
            'title' => 'Créer un club',
            'old' => [
                'nom' => '',
                'description' => '',
                'email_contact' => '',
                'actif' => 1,
            ],
            'error' => null,
        ]);
    }

    public function createClub(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->isPostRequest()) {
            $this->redirect('/evenements/createClubForm');
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

        if ($payload['nom'] === '') {
            $this->render('backoffice/clubs/create', [
                'title' => 'Créer un club',
                'old' => $payload,
                'error' => 'Le nom du club est obligatoire.',
            ]);
            return;
        }

        $clubModel = new Club();
        $createdId = $clubModel->create($payload);
        if ($createdId === false) {
            $this->render('backoffice/clubs/create', [
                'title' => 'Créer un club',
                'old' => $payload,
                'error' => 'Impossible de creer le club.',
            ]);
            return;
        }

        $this->redirect('/evenements/manageClubs?success=' . urlencode('Club cree avec succes.'));
    }

    public function createClubRequestForm(): void
    {
        $this->requireLogin();
        if (!$this->isRequesterRole()) {
            $this->redirect('/evenements/clubs');
            return;
        }

        $this->render('frontoffice/clubs/request_create', [
            'title' => 'Demander un club',
            'old' => [
                'nom' => '',
                'description' => '',
                'email_contact' => '',
            ],
            'error' => null,
        ]);
    }

    public function createClubRequest(): void
    {
        $this->requireLogin();
        if (!$this->isRequesterRole()) {
            $this->redirect('/evenements/clubs');
            return;
        }

        if (!$this->isPostRequest()) {
            $this->redirect('/evenements/createClubRequestForm');
            return;
        }

        $payload = [
            'nom' => trim((string) ($_POST['nom'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'email_contact' => trim((string) ($_POST['email_contact'] ?? '')),
        ];

        if ($payload['nom'] === '') {
            $this->render('frontoffice/clubs/request_create', [
                'title' => 'Demander un club',
                'old' => $payload,
                'error' => 'Le nom du club est obligatoire.',
            ]);
            return;
        }

        $clubModel = new Club();
        $createdId = $clubModel->createWithOwner($payload, $this->currentUserId());
        if ($createdId === false) {
            $this->render('frontoffice/clubs/request_create', [
                'title' => 'Demander un club',
                'old' => $payload,
                'error' => 'Impossible de soumettre la demande.',
            ]);
            return;
        }

        $this->notifyAllStaff(
            'Nouvelle demande de club à valider : « ' . $payload['nom'] . ' ».',
            '/evenements/manageClubs'
        );

        $this->redirect('/evenements/clubs?success=' . urlencode('Demande de club envoyee pour validation.'));
    }

    public function approveClub(int|string $id): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->isPostRequest()) {
            $this->redirect('/evenements/manageClubs');
            return;
        }

        $clubId = (int) $id;
        if ($clubId <= 0) {
            $this->redirect($this->clubListPathForCurrentUser() . '?error=' . urlencode('Club invalide.'));
            return;
        }

        $clubModel = new Club();
        $club = $clubModel->findById($clubId);
        $clubModel->approve($clubId, $this->currentUserId());
        if (is_array($club)) {
            $ownerId = (int) ($club['cree_par'] ?? 0);
            $nomClub = (string) ($club['nom'] ?? '');
            $this->notifyUser(
                $ownerId,
                'Votre club « ' . $nomClub . ' » a été approuvé.',
                '/evenements/clubs'
            );
        }
        $this->redirect('/evenements/manageClubs?success=' . urlencode('Club approuve.'));
    }

    public function rejectClub(int|string $id): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->isPostRequest()) {
            $this->redirect('/evenements/manageClubs');
            return;
        }

        $clubId = (int) $id;
        if ($clubId <= 0) {
            $this->redirect('/evenements/manageClubs?error=' . urlencode('Club invalide.'));
            return;
        }

        $clubModel = new Club();
        $club = $clubModel->findById($clubId);
        $clubModel->reject($clubId, $this->currentUserId());
        if (is_array($club)) {
            $ownerId = (int) ($club['cree_par'] ?? 0);
            $nomClub = (string) ($club['nom'] ?? '');
            $this->notifyUser(
                $ownerId,
                'Votre club « ' . $nomClub . ' » a été refusé.',
                '/evenements/clubs'
            );
        }
        $this->redirect('/evenements/manageClubs?success=' . urlencode('Club rejete.'));
    }

    public function editClubForm(int|string $id): void
    {
        $this->requireLogin();

        $clubId = (int) $id;
        if ($clubId <= 0) {
            $this->redirect('/evenements/manageClubs?error=' . urlencode('Club invalide.'));
            return;
        }

        if (!$this->canManageClub($clubId)) {
            $this->redirect('/evenements/clubs?error=' . urlencode('Acces non autorise.'));
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
            'club' => $club,
            'error' => null,
        ]);
    }

    public function editClub(int|string $id): void
    {
        $this->requireLogin();

        $clubId = (int) $id;
        if ($clubId <= 0) {
            $this->redirect($this->clubListPathForCurrentUser() . '?error=' . urlencode('Club invalide.'));
            return;
        }

        if (!$this->canManageClub($clubId)) {
            $this->redirect('/evenements/clubs?error=' . urlencode('Acces non autorise.'));
            return;
        }

        if (!$this->isPostRequest()) {
            $this->redirect('/evenements/editClubForm/' . $clubId);
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

        if ($payload['nom'] === '') {
            $this->render('backoffice/clubs/edit', [
                'title' => 'Modifier un club',
                'club' => array_merge($club, $payload),
                'error' => 'Le nom du club est obligatoire.',
            ]);
            return;
        }

        $updated = $clubModel->update($clubId, $payload);
        if (!$updated) {
            $this->render('backoffice/clubs/edit', [
                'title' => 'Modifier un club',
                'club' => array_merge($club, $payload),
                'error' => 'Aucune modification enregistree.',
            ]);
            return;
        }

        $this->redirect($this->clubListPathForCurrentUser() . '?success=' . urlencode('Club mis a jour.'));
    }

    public function deleteClub(int|string $id): void
    {
        $this->requireLogin();

        $clubId = (int) $id;
        if ($clubId <= 0) {
            $this->redirect($this->clubListPathForCurrentUser() . '?error=' . urlencode('Club invalide.'));
            return;
        }

        if (!$this->canManageClub($clubId)) {
            $this->redirect('/evenements/clubs?error=' . urlencode('Acces non autorise.'));
            return;
        }

        if ($this->isPostRequest()) {
            $clubModel = new Club();
            try {
                if (!$clubModel->delete($clubId)) {
                    $this->redirect($this->clubListPathForCurrentUser() . '?error=' . urlencode('Club introuvable ou deja supprime.'));
                    return;
                }
            } catch (\Throwable $e) {
                $this->redirect($this->clubListPathForCurrentUser() . '?error=' . urlencode('Suppression impossible : des donnees liees bloquent encore l operation.'));
                return;
            }
        }

        $this->redirect($this->clubListPathForCurrentUser());
    }
}
