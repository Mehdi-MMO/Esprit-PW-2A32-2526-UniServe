<?php

declare(strict_types=1);

require_once __DIR__ . '/../Model/CategorieService.php';
require_once __DIR__ . '/../Model/DemandeDeService.php';
require_once __DIR__ . '/../Model/DemandeTextModeration.php';
require_once __DIR__ . '/../Model/DemandeDescriptionAiService.php';
require_once __DIR__ . '/../Model/DemandeStaffAiCheckService.php';
require_once __DIR__ . '/../Model/UserAiSnapshot.php';
require_once __DIR__ . '/../Model/NotificationModel.php';
require_once __DIR__ . '/../Model/PieceJointeDemande.php';
require_once __DIR__ . '/../Model/User.php';

class DemandesController extends Controller
{
    private const MAX_PIECES_PER_DEMANDE = 12;
    private const MAX_BYTES_PER_PIECE = 5242880;

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

    /** Étudiant ou enseignant : portail « Mes demandes » (DEMANDE_MODULE front-office). */
    private function isDemandeRequester(): bool
    {
        return in_array($this->currentRole(), ['etudiant', 'enseignant'], true);
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

    private function pieceMimeWhitelist(): array
    {
        return [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
    }

    private function safeUploadBasename(string $name): string
    {
        $name = str_replace(['..', '/', '\\'], '', $name);

        return basename($name);
    }

    private function notifyAllStaff(string $message, ?string $lien = null): void
    {
        if (trim($message) === '') {
            return;
        }

        $notif = new NotificationModel();
        foreach ((new User())->findStaffAndAdminsActifs() as $row) {
            $uid = (int) ($row['id'] ?? 0);
            if ($uid > 0) {
                $notif->create($uid, $message, $lien);
            }
        }
    }

    /**
     * @return array<int, list<array<string, mixed>>>
     */
    private function piecesByDemandeIds(array $demandeRows): array
    {
        $pj = new PieceJointeDemande();
        $out = [];
        foreach ($demandeRows as $d) {
            $did = (int) ($d['id'] ?? 0);
            if ($did > 0) {
                $out[$did] = $pj->findByDemandeId($did);
            }
        }

        return $out;
    }

    /**
     * Appends uploaded files from $_FILES['pieces'] to an existing demande. Returns an error message or null.
     */
    private function appendUploadedPieces(int $demandeId): ?string
    {
        if ($demandeId <= 0 || !isset($_FILES['pieces'])) {
            return null;
        }

        $pj = new PieceJointeDemande();
        $existing = $pj->countForDemande($demandeId);

        $names = $_FILES['pieces']['name'] ?? null;
        $tmps = $_FILES['pieces']['tmp_name'] ?? null;
        $sizes = $_FILES['pieces']['size'] ?? null;
        $errs = $_FILES['pieces']['error'] ?? null;

        if (!is_array($names)) {
            $names = [$names];
            $tmps = [$tmps];
            $sizes = [$sizes];
            $errs = [$errs];
        }

        $allowed = $this->pieceMimeWhitelist();
        $uploadDir = $pj->uploadBaseDir();
        $toProcess = [];

        $n = count($names);
        for ($i = 0; $i < $n; $i++) {
            $err = (int) ($errs[$i] ?? UPLOAD_ERR_NO_FILE);
            if ($err === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ($err !== UPLOAD_ERR_OK) {
                return 'Échec du téléversement d’au moins un fichier.';
            }

            $tmp = (string) ($tmps[$i] ?? '');
            $size = (int) ($sizes[$i] ?? 0);
            if ($tmp === '' || !is_uploaded_file($tmp)) {
                return 'Fichier invalide.';
            }
            if ($size > self::MAX_BYTES_PER_PIECE) {
                return 'Chaque fichier doit faire au plus 5 Mo.';
            }

            $orig = $this->safeUploadBasename((string) ($names[$i] ?? ''));
            if ($orig === '') {
                return 'Nom de fichier invalide.';
            }

            $mime = (string) (mime_content_type($tmp) ?: '');
            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            if (!in_array($mime, $allowed, true)) {
                $byExt = [
                    'pdf' => 'application/pdf',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'doc' => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ];
                if (isset($byExt[$ext])) {
                    $mime = $byExt[$ext];
                }
            }
            if (!in_array($mime, $allowed, true)) {
                return 'Type de fichier non autorisé (PDF, images JPEG/PNG, Word).';
            }

            $toProcess[] = ['tmp' => $tmp, 'orig' => $orig, 'mime' => $mime];
        }

        if ($toProcess === []) {
            return null;
        }

        if ($existing + count($toProcess) > self::MAX_PIECES_PER_DEMANDE) {
            return 'Nombre maximal de pièces jointes atteint (' . self::MAX_PIECES_PER_DEMANDE . ').';
        }

        foreach ($toProcess as $item) {
            $stored = time() . '_' . bin2hex(random_bytes(6)) . '_' . $item['orig'];
            $dest = $uploadDir . '/' . $stored;
            if (!move_uploaded_file($item['tmp'], $dest)) {
                return 'Enregistrement du fichier impossible.';
            }

            $ins = $pj->create($demandeId, $item['orig'], $stored, $item['mime']);
            if ($ins === false) {
                @unlink($dest);

                return 'Enregistrement en base impossible.';
            }
        }

        return null;
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
                'pieces_by_demande' => $this->piecesByDemandeIds($demandes),
                'demande_staff_ai_check_enabled' => DemandeStaffAiCheckService::isEnabled(),
            ]);
            return;
        }

        if ($this->isDemandeRequester()) {
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
                'pieces_by_demande' => $this->piecesByDemandeIds($demandes),
            ]);
            return;
        }

        $this->redirectByUserRole($this->currentRole());
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

    /**
     * @return array{demande_ai_description_enabled: bool, demande_ai_description_url: string, demande_ai_exclude_demande_id: int}
     */
    private function demandeAiDescriptionViewData(?int $excludeDemandeId = null): array
    {
        $ex = $excludeDemandeId !== null && $excludeDemandeId > 0 ? $excludeDemandeId : 0;

        return [
            'demande_ai_description_enabled' => DemandeDescriptionAiService::isEnabled(),
            'demande_ai_description_url' => $this->url('/demandes/aiSuggestDescription'),
            'demande_ai_exclude_demande_id' => $ex,
        ];
    }

    /** Contexte « Mes demandes » + autres modules (tronqué) pour l’IA description. */
    private function buildPortailContextForDemandeAi(int $userId, int $excludeDemandeId): string
    {
        if ($userId <= 0) {
            return '';
        }

        $role = $this->currentRole();
        $brief = '';
        try {
            $snap = UserAiSnapshot::build($userId, $role);
            $brief = UserAiSnapshot::toFrenchBriefLines($snap);
        } catch (\Throwable) {
            $brief = '';
        }

        $lines = [];
        try {
            foreach ((new DemandeDeService())->findAllForStudent($userId) as $row) {
                $id = (int) ($row['id'] ?? 0);
                if ($excludeDemandeId > 0 && $id === $excludeDemandeId) {
                    continue;
                }
                if (count($lines) >= 10) {
                    break;
                }
                $titre = trim((string) ($row['titre'] ?? ''));
                $desc = trim((string) preg_replace('/\s+/u', ' ', (string) ($row['description'] ?? '')));
                if (function_exists('mb_strlen') && function_exists('mb_substr')) {
                    $titre = mb_strlen($titre) > 120 ? mb_substr($titre, 0, 117) . '…' : $titre;
                    $desc = mb_strlen($desc) > 450 ? mb_substr($desc, 0, 447) . '…' : $desc;
                } else {
                    $titre = strlen($titre) > 120 ? substr($titre, 0, 117) . '…' : $titre;
                    $desc = strlen($desc) > 450 ? substr($desc, 0, 447) . '…' : $desc;
                }
                $lines[] = sprintf(
                    '#%d « %s » — %s — %s%s',
                    $id,
                    $titre,
                    (string) ($row['statut'] ?? ''),
                    trim((string) ($row['categorie_nom'] ?? '')),
                    $desc !== '' ? "\n  Notes enregistrées : " . $desc : ''
                );
            }
        } catch (\Throwable) {
        }

        $detail = $lines !== [] ? "Mes demandes de service (historique récent, textes tronqués) :\n" . implode("\n\n", $lines) : '';
        $merged = trim($brief . ($brief !== '' && $detail !== '' ? "\n\n" : '') . $detail);
        if ($merged === '') {
            return '';
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr') && mb_strlen($merged) > 5200) {
            return mb_substr($merged, 0, 5197) . '…';
        }

        return strlen($merged) > 5200 ? substr($merged, 0, 5197) . '…' : $merged;
    }

    public function createForm(): void
    {
        $this->requireLogin();
        if (!$this->isDemandeRequester()) {
            $this->redirectByUserRole($this->currentRole());
            return;
        }

        $categories = (new CategorieService())->findAllActive();

        $preCat = (int) ($_GET['categorie_id'] ?? 0);
        $preCatOk = '';
        if ($preCat > 0) {
            foreach ($categories as $c) {
                if ((int) ($c['id'] ?? 0) === $preCat) {
                    $preCatOk = (string) $preCat;
                    break;
                }
            }
        }

        $this->render('frontoffice/demandes/create', array_merge([
            'title' => 'Nouvelle demande',
            'categories' => $categories,
            'old' => [
                'categorie_id' => $preCatOk,
                'titre' => '',
                'description' => '',
            ],
            'error' => null,
        ], $this->demandeAiDescriptionViewData()));
    }

    public function aiSuggestDescription(): void
    {
        $this->requireLogin();
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->isDemandeRequester()) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Accès refusé.'], JSON_UNESCAPED_UNICODE);

            return;
        }

        if (!$this->isPost()) {
            http_response_code(405);
            echo json_encode(['ok' => false, 'error' => 'Méthode non autorisée.'], JSON_UNESCAPED_UNICODE);

            return;
        }

        if (!DemandeDescriptionAiService::isEnabled()) {
            http_response_code(503);
            echo json_encode(['ok' => false, 'error' => 'Suggestion IA non disponible (clé Groq ou option désactivée).'], JSON_UNESCAPED_UNICODE);

            return;
        }

        $raw = file_get_contents('php://input');
        $payload = json_decode((string) $raw, true);
        if (!is_array($payload)) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'Corps JSON invalide.'], JSON_UNESCAPED_UNICODE);

            return;
        }

        $titre = trim((string) ($payload['titre'] ?? ''));
        $notes = trim((string) ($payload['description'] ?? ''));
        $categorieId = (int) ($payload['categorie_id'] ?? 0);
        $excludeDemandeId = (int) ($payload['exclude_demande_id'] ?? 0);
        if ($excludeDemandeId > 0) {
            $demEx = (new DemandeDeService())->findById($excludeDemandeId);
            if ($demEx === null || (int) ($demEx['etudiant_id'] ?? 0) !== $this->currentUserId()) {
                $excludeDemandeId = 0;
            }
        }

        $catNom = null;
        if ($categorieId > 0) {
            $row = (new CategorieService())->findById($categorieId);
            if ($row !== null && (int) ($row['actif'] ?? 0) === 1) {
                $n = trim((string) ($row['nom'] ?? ''));
                $catNom = $n !== '' ? $n : null;
            }
        }

        $portalContext = $this->buildPortailContextForDemandeAi($this->currentUserId(), $excludeDemandeId);

        if ($catNom === null && $titre === '' && $notes === '' && $portalContext === '') {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'Aucun contexte disponible. Ouvrez « Mes demandes » ou saisissez un titre / notes.'], JSON_UNESCAPED_UNICODE);

            return;
        }

        $text = DemandeDescriptionAiService::suggest($titre, $notes, $catNom, $portalContext);
        if ($text === null || $text === '') {
            http_response_code(502);
            echo json_encode(['ok' => false, 'error' => 'La suggestion IA n’a pas pu être générée. Réessayez ou rédigez manuellement.'], JSON_UNESCAPED_UNICODE);

            return;
        }

        echo json_encode(['ok' => true, 'description' => $text], JSON_UNESCAPED_UNICODE);
    }

    public function create(): void
    {
        $this->requireLogin();
        if (!$this->isDemandeRequester()) {
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
            $this->render('frontoffice/demandes/create', array_merge([
                'title' => 'Nouvelle demande',
                'categories' => $catModel->findAllActive(),
                'old' => $payload,
                'error' => 'Catégorie invalide ou inactive.',
            ], $this->demandeAiDescriptionViewData()));
            return;
        }

        $moderationError = DemandeTextModeration::validate(
            $payload['titre'],
            $payload['description'],
            trim((string) ($cat['nom'] ?? ''))
        );
        if ($moderationError !== null) {
            $this->render('frontoffice/demandes/create', array_merge([
                'title' => 'Nouvelle demande',
                'categories' => $catModel->findAllActive(),
                'old' => $payload,
                'error' => $moderationError,
            ], $this->demandeAiDescriptionViewData()));
            return;
        }

        $demModel = new DemandeDeService();
        $newId = $demModel->create($this->currentUserId(), $payload);
        if ($newId === false) {
            $this->render('frontoffice/demandes/create', array_merge([
                'title' => 'Nouvelle demande',
                'categories' => $catModel->findAllActive(),
                'old' => $payload,
                'error' => 'Veuillez remplir tous les champs obligatoires.',
            ], $this->demandeAiDescriptionViewData()));
            return;
        }

        $pieceErr = $this->appendUploadedPieces($newId);
        $etu = trim((string) (($_SESSION['user']['prenom'] ?? '') . ' ' . ($_SESSION['user']['nom'] ?? '')));
        if ($etu === '') {
            $etu = $this->currentRole() === 'enseignant' ? 'Enseignant' : 'Étudiant';
        }
        $this->notifyAllStaff(
            'Nouvelle demande de service : « ' . $payload['titre'] . ' » (' . $etu . ').',
            '/demandes'
        );

        if ($pieceErr !== null) {
            $this->setFlash('warning', 'Demande enregistrée. ' . $pieceErr . ' Vous pouvez compléter les pièces depuis « Modifier ».');
        } else {
            $this->setFlash('success', 'Demande enregistrée.');
        }
        $this->redirect('/demandes');
    }

    public function editForm(int|string $id): void
    {
        $this->requireLogin();
        if (!$this->isDemandeRequester()) {
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

        $this->render('frontoffice/demandes/edit', array_merge([
            'title' => 'Modifier la demande',
            'demande' => $demande,
            'categories' => $catModel->findAllActive(),
            'pieces' => (new PieceJointeDemande())->findByDemandeId($demId),
            'error' => null,
        ], $this->demandeAiDescriptionViewData($demId)));
    }

    public function update(int|string $id): void
    {
        $this->requireLogin();
        if (!$this->isDemandeRequester()) {
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
            $this->render('frontoffice/demandes/edit', array_merge([
                'title' => 'Modifier la demande',
                'demande' => array_merge($demandeRow, [
                    'categorie_id' => $payload['categorie_id'],
                    'titre' => $payload['titre'],
                    'description' => $payload['description'],
                ]),
                'categories' => $catModel->findAllActive(),
                'pieces' => (new PieceJointeDemande())->findByDemandeId($demId),
                'error' => 'Catégorie invalide ou inactive.',
            ], $this->demandeAiDescriptionViewData($demId)));
            return;
        }

        $moderationError = DemandeTextModeration::validate(
            $payload['titre'],
            $payload['description'],
            trim((string) ($cat['nom'] ?? ''))
        );
        if ($moderationError !== null) {
            $this->render('frontoffice/demandes/edit', array_merge([
                'title' => 'Modifier la demande',
                'demande' => array_merge($demandeRow, [
                    'categorie_id' => $payload['categorie_id'],
                    'titre' => $payload['titre'],
                    'description' => $payload['description'],
                ]),
                'categories' => $catModel->findAllActive(),
                'pieces' => (new PieceJointeDemande())->findByDemandeId($demId),
                'error' => $moderationError,
            ], $this->demandeAiDescriptionViewData($demId)));
            return;
        }

        $ok = $demModel->updateByStudent($demId, $this->currentUserId(), $payload);
        if (!$ok) {
            $demande = $demModel->findById($demId);
            $this->render('frontoffice/demandes/edit', array_merge([
                'title' => 'Modifier la demande',
                'demande' => array_merge($demande ?? [], [
                    'categorie_id' => $payload['categorie_id'],
                    'titre' => $payload['titre'],
                    'description' => $payload['description'],
                ]),
                'categories' => $catModel->findAllActive(),
                'pieces' => (new PieceJointeDemande())->findByDemandeId($demId),
                'error' => 'Modification impossible (demande introuvable ou déjà traitée).',
            ], $this->demandeAiDescriptionViewData($demId)));
            return;
        }

        $pieceErr = $this->appendUploadedPieces($demId);
        if ($pieceErr !== null) {
            $this->setFlash('danger', $pieceErr);
        } else {
            $this->setFlash('success', 'Demande mise à jour.');
        }
        $this->redirect('/demandes');
    }

    public function delete(int|string $id): void
    {
        $this->requireLogin();
        if (!$this->isDemandeRequester()) {
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
        $before = $demModel->findById((int) $id);
        $ok = $demModel->updateStatut((int) $id, $statut);

        if ($ok && is_array($before)) {
            $old = (string) ($before['statut'] ?? '');
            if ($old !== $statut) {
                $etudiantId = (int) ($before['etudiant_id'] ?? 0);
                $titre = (string) ($before['titre'] ?? '');
                if ($etudiantId > 0) {
                    $labels = $this->statutLabels();
                    $label = $labels[$statut] ?? $statut;
                    $msg = 'Votre demande « ' . $titre . ' » : statut « ' . $label . ' ».';
                    (new NotificationModel())->create($etudiantId, $msg, '/demandes');
                }
            }
        }

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
            if ($u === null) {
                $this->setFlash('danger', 'Utilisateur staff/admin introuvable.');
                $this->redirect('/demandes');
                return;
            }
            $role = (string) ($u['role'] ?? '');
            if (!in_array($role, ['staff', 'admin'], true)) {
                $this->setFlash('danger', 'Utilisateur staff/admin introuvable.');
                $this->redirect('/demandes');
                return;
            }
        }

        $demModel = new DemandeDeService();
        $before = $demModel->findById((int) $id);
        $ok = $demModel->updateAssignee((int) $id, $assigneeId);

        if ($ok && is_array($before) && $assigneeId !== null) {
            $oldAssign = (int) ($before['assigne_a'] ?? 0);
            if ($oldAssign !== $assigneeId) {
                $titre = (string) ($before['titre'] ?? '');
                (new NotificationModel())->create(
                    $assigneeId,
                    'Demande de service assignée : « ' . $titre . ' ».',
                    '/demandes'
                );
            }
        }

        $this->setFlash($ok ? 'success' : 'danger', $ok ? 'Assignation enregistrée.' : 'Assignation impossible.');

        $this->redirect('/demandes');
    }

    public function downloadPiece(int|string $id): void
    {
        $this->requireLogin();

        $pieceId = (int) $id;
        $pjModel = new PieceJointeDemande();
        $piece = $pjModel->findById($pieceId);
        if ($piece === null) {
            http_response_code(404);
            exit('Pièce jointe introuvable.');
        }

        $demandeId = (int) ($piece['demande_service_id'] ?? 0);
        $demande = (new DemandeDeService())->findById($demandeId);
        if ($demande === null) {
            http_response_code(404);
            exit('Demande introuvable.');
        }

        $uid = $this->currentUserId();
        if ($this->isDemandeRequester()) {
            if ((int) ($demande['etudiant_id'] ?? 0) !== $uid) {
                http_response_code(403);
                exit('Accès refusé.');
            }
        } elseif (!$this->isStaffOrAdmin()) {
            $this->redirectByUserRole($this->currentRole());
            return;
        }

        $rel = (string) ($piece['chemin_fichier'] ?? '');
        if ($rel === '' || strpbrk($rel, '/\\') !== false) {
            http_response_code(404);
            exit('Fichier invalide.');
        }

        $path = $pjModel->uploadBaseDir() . '/' . $rel;
        if (!is_file($path)) {
            http_response_code(404);
            exit('Fichier absent sur le serveur.');
        }

        $downloadName = (string) ($piece['nom_fichier'] ?? 'piece-jointe');
        $mime = (string) ($piece['type_mime'] ?? '');
        if ($mime === '') {
            $mime = 'application/octet-stream';
        }

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . str_replace('"', '', $downloadName) . '"');
        header('Content-Length: ' . (string) filesize($path));
        readfile($path);
        exit;
    }

    public function deletePiece(int|string $id): void
    {
        $this->requireLogin();

        if (!$this->isPost()) {
            $this->redirect('/demandes');
            return;
        }

        $pieceId = (int) $id;
        $pj = new PieceJointeDemande();
        $row = $pj->findById($pieceId);
        if ($row === null) {
            $this->setFlash('danger', 'Pièce jointe introuvable.');
            $this->redirect('/demandes');
            return;
        }

        $demandeId = (int) ($row['demande_service_id'] ?? 0);
        $dem = (new DemandeDeService())->findById($demandeId);
        if ($dem === null) {
            $this->setFlash('danger', 'Demande introuvable.');
            $this->redirect('/demandes');
            return;
        }

        if ($this->isStaffOrAdmin()) {
            // staff may remove any attachment
        } elseif ($this->isDemandeRequester()) {
            if ((int) ($dem['etudiant_id'] ?? 0) !== $this->currentUserId()
                || (string) ($dem['statut'] ?? '') !== 'en_attente'
            ) {
                $this->setFlash('danger', 'Suppression non autorisée.');
                $this->redirect('/demandes');
                return;
            }
        } else {
            $this->redirectByUserRole($this->currentRole());
            return;
        }

        $rel = (string) ($row['chemin_fichier'] ?? '');
        $pj->unlinkStored($rel);
        $pj->deleteById($pieceId);

        $this->setFlash('success', 'Pièce jointe supprimée.');
        if ($this->isDemandeRequester()) {
            $this->redirect('/demandes/editForm/' . $demandeId);
            return;
        }

        $this->redirect('/demandes');
    }

    public function aiStaffCheck(int|string $id): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        header('Content-Type: application/json; charset=utf-8');
        $jf = JSON_UNESCAPED_UNICODE;
        if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
            $jf |= JSON_INVALID_UTF8_SUBSTITUTE;
        }

        if (!$this->isPost()) {
            http_response_code(405);
            echo json_encode(['ok' => false, 'error' => 'Méthode non autorisée.'], $jf);

            return;
        }

        if (!DemandeStaffAiCheckService::isEnabled()) {
            http_response_code(503);
            echo json_encode(['ok' => false, 'error' => 'Vérification IA non disponible (clé Groq ou option désactivée).'], $jf);

            return;
        }

        $dem = (new DemandeDeService())->findById((int) $id);
        if ($dem === null) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Demande introuvable.'], $jf);

            return;
        }

        $result = DemandeStaffAiCheckService::analyze($dem);
        if ($result === null) {
            http_response_code(502);
            echo json_encode(['ok' => false, 'error' => 'Analyse IA impossible. Réessayez plus tard.'], $jf);

            return;
        }

        $payload = json_encode(['ok' => true] + $result, $jf);
        if ($payload === false || $payload === '') {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Encodage de la réponse impossible.'], $jf);

            return;
        }

        echo $payload;
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
