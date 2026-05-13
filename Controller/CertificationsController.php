<?php

declare(strict_types=1);

require_once __DIR__ . '/../Model/DocacCours.php';
require_once __DIR__ . '/../Model/DocacCertificat.php';
require_once __DIR__ . '/../Model/DocacQuiz.php';
require_once __DIR__ . '/../Model/DocacDemandeCertification.php';
require_once __DIR__ . '/../Model/DocacQuizAiService.php';
require_once __DIR__ . '/../Model/DocacSchema.php';
require_once __DIR__ . '/../Model/NotificationModel.php';
require_once __DIR__ . '/../Model/Model.php';
require_once __DIR__ . '/../Model/AppUploads.php';

class CertificationsController extends Controller
{
    private function isPost(): bool
    {
        return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST';
    }

    private function docacSchemaReady(): bool
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        try {
            DocacSchema::ensureTables();
            $statement = (new Model())->query("SHOW TABLES LIKE 'demandes_certification'");
            $cache = (bool) $statement->fetch();
        } catch (Throwable $e) {
            $cache = false;
        }

        return $cache;
    }

    private function requireDocacOrSetup(): bool
    {
        if ($this->docacSchemaReady()) {
            return true;
        }

        $this->render('certifications/setup', [
            'title' => 'Certifications — configuration MySQL',
        ], 'landing');

        return false;
    }

    private function setCertFlash(string $type, string $message): void
    {
        $_SESSION['certifications_flash'] = ['type' => $type, 'message' => $message];
    }

    private function popCertFlash(): ?array
    {
        if (!isset($_SESSION['certifications_flash'])) {
            return null;
        }

        $f = $_SESSION['certifications_flash'];
        unset($_SESSION['certifications_flash']);

        return is_array($f) ? $f : null;
    }

    private function projectRoot(): string
    {
        return dirname(__DIR__);
    }

    private function uploadDir(string $sub): string
    {
        return AppUploads::sub($sub);
    }

    private function currentUserId(): int
    {
        return (int) ($_SESSION['user']['id'] ?? 0);
    }

    private function isStaffOrAdmin(): bool
    {
        return in_array((string) ($_SESSION['user']['role'] ?? ''), ['staff', 'admin'], true);
    }

    private function isStudentLike(): bool
    {
        return in_array((string) ($_SESSION['user']['role'] ?? ''), ['etudiant', 'enseignant'], true);
    }

    private function safeBasename(string $name): string
    {
        $name = str_replace(['..', '/', '\\'], '', $name);

        return basename($name);
    }

    public function landing(): void
    {
        $this->index();
    }

    /**
     * Student / teacher hub (DOCAC front).
     */
    public function index(): void
    {
        $this->requireLogin();
        if (!$this->requireDocacOrSetup()) {
            return;
        }

        if ($this->isStaffOrAdmin()) {
            $this->redirect('/certifications/manage');
            return;
        }

        if (!$this->isStudentLike()) {
            $this->redirectByUserRole((string) ($_SESSION['user']['role'] ?? ''));
            return;
        }

        $coursModel = new DocacCours();
        $certModel = new DocacCertificat();
        $demModel = new DocacDemandeCertification();
        $quizModel = new DocacQuiz();

        $uid = $this->currentUserId();
        $demandes = $demModel->getAllByUser($uid);
        foreach ($demandes as &$d) {
            $d['quiz'] = $quizModel->getByDemandeId((int) ($d['id'] ?? 0));
        }
        unset($d);

        $quizNotif = $_SESSION['notif_quiz'] ?? null;
        unset($_SESSION['notif_quiz']);

        $this->render('certifications/student_index', [
            'title' => 'Certifications & cours',
            'cours' => $coursModel->getAllCours(),
            'certificats' => $certModel->getAllCertificats(),
            'demandes' => $demandes,
            'quiz_notif' => is_array($quizNotif) ? $quizNotif : null,
            'flash' => $this->popCertFlash(),
        ], 'frontoffice');
    }

    /**
     * Staff backoffice (DOCAC BackofficeDocuments).
     */
    public function manage(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->requireDocacOrSetup()) {
            return;
        }

        set_time_limit(120);

        $demModel = new DocacDemandeCertification();
        $quizModel = new DocacQuiz();
        $demandes = $demModel->getAll();

        foreach ($demandes as &$d) {
            $quiz = $quizModel->getByDemandeId((int) ($d['id'] ?? 0));
            $d['quiz'] = $quiz;
            if ($quiz && in_array((string) ($quiz['statut'] ?? ''), ['accepte', 'refuse'], true)) {
                if ((string) ($d['statut'] ?? '') === 'quiz_envoye') {
                    $demModel->updateStatut(
                        (int) $d['id'],
                        (string) $quiz['statut'],
                        'Quiz passé — score : ' . (int) ($quiz['score'] ?? 0) . '/5'
                    );
                    $d['statut'] = $quiz['statut'];
                }
            }
        }
        unset($d);

        $this->render('certifications/manage', [
            'title' => 'Certifications (admin)',
            'cours' => (new DocacCours())->getAllCours(),
            'certificats' => (new DocacCertificat())->getAllCertificats(),
            'demandes' => $demandes,
            'nb_en_attente' => $demModel->countByStatut('en_attente'),
            'flash' => $this->popCertFlash(),
        ], 'backoffice');
    }

    public function envoyerQuiz(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->docacSchemaReady()) {
            $this->redirect('/certifications/manage');
            return;
        }

        if (!$this->isPost()) {
            $this->redirect('/certifications/manage');
            return;
        }

        set_time_limit(300);
        ini_set('max_execution_time', '300');

        $id = (int) ($_POST['id'] ?? 0);
        $demModel = new DocacDemandeCertification();
        $demande = $demModel->getById($id);
        if ($demande === null) {
            $this->setCertFlash('danger', 'Demande introuvable.');
            $this->redirect('/certifications/manage#demandes');
            return;
        }

        $coursTitre = (string) ($demande['titre_cours'] ?? $demande['nom_certificat'] ?? '');
        $cours = (new DocacCours())->getCoursParTitre($coursTitre);
        if ($cours === null) {
            $this->setCertFlash(
                'danger',
                'Aucun cours en base avec le titre « ' . $coursTitre . ' ». Créez un cours avec exactement ce titre (ou corrigez la demande).'
            );
            $this->redirect('/certifications/manage#demandes');
            return;
        }

        $description = trim((string) ($cours['description'] ?? ''));
        $contenu = trim((string) ($cours['contenu'] ?? ''));
        $fichiers = !empty($cours['fichiers']) && is_array($cours['fichiers'])
            ? $cours['fichiers']
            : [];

        $ai = new DocacQuizAiService();
        $pdfFiles = $ai->collectPdfSnippets($fichiers);
        $questions = $ai->generateFiveQuestions(
            $coursTitre,
            $description,
            $contenu,
            (string) ($demande['nom_certificat'] ?? ''),
            $pdfFiles,
            [
                'id' => $id,
                'statut' => (string) ($demande['statut'] ?? ''),
                'date_souhaitee' => (string) ($demande['date_souhaitee'] ?? ''),
                'organisation' => (string) ($demande['organisation'] ?? ''),
            ]
        );

        (new DocacQuiz())->create($id, $coursTitre, $questions);
        $demModel->markQuizEnvoye($id);

        $uid = (int) ($demande['utilisateur_id'] ?? 0);
        if ($uid > 0) {
            (new NotificationModel())->create(
                $uid,
                'Un quiz de certification est disponible pour votre demande « ' . (string) ($demande['nom_certificat'] ?? '') . ' ».',
                '/certifications'
            );
        }

        $this->setCertFlash('success', 'Quiz généré et envoyé à l’étudiant.');
        $this->redirect('/certifications/manage#demandes');
    }

    public function accepterDemande(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);
        if (!$this->docacSchemaReady()) {
            $this->redirect('/certifications/manage');
            return;
        }

        if (!$this->isPost()) {
            $this->redirect('/certifications/manage');
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $commentaire = trim((string) ($_POST['commentaire'] ?? ''));
        if ($id > 0) {
            $demModel = new DocacDemandeCertification();
            $demModel->updateStatut($id, 'accepte', $commentaire);
            $demande = $demModel->getById($id);
            $uid = (int) ($demande['utilisateur_id'] ?? 0);
            if ($uid > 0) {
                (new NotificationModel())->create(
                    $uid,
                    'Votre demande de certification « ' . (string) ($demande['nom_certificat'] ?? '') . ' » a été acceptée.',
                    '/certifications'
                );
            }
            $this->setCertFlash('success', 'Demande acceptée.');
        }

        $this->redirect('/certifications/manage#demandes');
    }

    public function refuserDemande(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);
        if (!$this->docacSchemaReady()) {
            $this->redirect('/certifications/manage');
            return;
        }

        if (!$this->isPost()) {
            $this->redirect('/certifications/manage');
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $commentaire = trim((string) ($_POST['commentaire'] ?? ''));
        if ($id > 0) {
            $demModel = new DocacDemandeCertification();
            $demModel->updateStatut($id, 'refuse', $commentaire);
            $demande = $demModel->getById($id);
            $uid = (int) ($demande['utilisateur_id'] ?? 0);
            if ($uid > 0) {
                (new NotificationModel())->create(
                    $uid,
                    'Votre demande de certification « ' . (string) ($demande['nom_certificat'] ?? '') . ' » a été refusée.',
                    '/certifications'
                );
            }
            $this->setCertFlash('success', 'Demande refusée.');
        }

        $this->redirect('/certifications/manage#demandes');
    }

    public function storeCours(): void
    {
        $this->requireLogin();
        if (!$this->isStaffOrAdmin()) {
            $this->setCertFlash('warning', 'La gestion des cours (création / modification) est réservée au personnel dans le back-office.');
            $this->redirect('/certifications');
            return;
        }

        if (!$this->docacSchemaReady()) {
            $this->redirect('/certifications/manage');
            return;
        }

        if (!$this->isPost()) {
            $this->redirect('/certifications/manage');
            return;
        }

        $titreNew = trim((string) ($_POST['titre'] ?? ''));
        if ($titreNew === '') {
            $this->setCertFlash('danger', 'Le titre du cours est obligatoire.');
            $this->redirect('/certifications/manage');
            return;
        }

        $coursModel = new DocacCours();
        if ($coursModel->coursExiste($titreNew)) {
            $this->setCertFlash('danger', 'Un cours avec ce titre existe déjà. Choisissez un autre titre.');
            $this->redirect('/certifications/manage');
            return;
        }

        $imagePath = $this->uploadCoursImage();
        $fichiers = $this->uploadCoursFichiers();
        $coursModel->createCours($_POST, $imagePath, $fichiers);
        $this->setCertFlash('success', 'Cours enregistré.');

        $this->redirect('/certifications/manage');
    }

    public function editCours(): void
    {
        $this->requireLogin();
        if (!$this->isStaffOrAdmin()) {
            $this->setCertFlash('warning', 'La gestion des cours est réservée au personnel dans le back-office.');
            $this->redirect('/certifications');
            return;
        }

        if (!$this->docacSchemaReady()) {
            $this->redirect('/certifications/manage');
            return;
        }

        if (!$this->isPost()) {
            $this->redirect('/certifications/manage');
            return;
        }

        $oldTitre = trim((string) ($_POST['old_titre'] ?? ''));
        if ($oldTitre === '') {
            $this->setCertFlash('danger', 'Cours introuvable (titre d’origine manquant).');
            $this->redirect('/certifications/manage');
            return;
        }

        $imagePath = $this->uploadCoursImage();
        $newFiles = $this->uploadCoursFichiers();
        $existing = [];
        if (!empty($_POST['fichiers_existants'])) {
            $existing = json_decode((string) $_POST['fichiers_existants'], true) ?: [];
        }
        if (!is_array($existing)) {
            $existing = [];
        }
        $fichiers = array_merge($existing, $newFiles);

        $ok = (new DocacCours())->updateCours($_POST, $oldTitre, $imagePath, $fichiers);
        $this->setCertFlash($ok ? 'success' : 'danger', $ok ? 'Cours mis à jour.' : 'Mise à jour impossible (vérifiez le titre).');

        $this->redirect('/certifications/manage');
    }

    public function deleteCours(): void
    {
        $this->requireLogin();
        if (!$this->isStaffOrAdmin()) {
            $this->setCertFlash('warning', 'La suppression des cours est réservée au personnel.');
            $this->redirect('/certifications');
            return;
        }

        if (!$this->docacSchemaReady()) {
            $this->redirect('/certifications/manage');
            return;
        }

        if (!$this->isPost()) {
            $this->redirect('/certifications/manage');
            return;
        }

        $titre = trim((string) ($_POST['titre'] ?? ''));
        if ($titre !== '') {
            $ok = (new DocacCours())->deleteCours($titre);
            $this->setCertFlash($ok ? 'success' : 'danger', $ok ? 'Cours supprimé.' : 'Suppression impossible.');
        }

        $this->redirect('/certifications/manage');
    }

    public function storeCertificat(): void
    {
        $this->requireLogin();
        if (!$this->isStaffOrAdmin()) {
            $this->setCertFlash('warning', 'La gestion du catalogue de certificats est réservée au personnel.');
            $this->redirect('/certifications');
            return;
        }

        if (!$this->docacSchemaReady()) {
            $this->redirect('/certifications/manage');
            return;
        }

        if (!$this->isPost()) {
            $this->redirect('/certifications/manage');
            return;
        }

        $nom = trim((string) ($_POST['nom_certificat'] ?? ''));
        if ($nom === '') {
            $this->setCertFlash('danger', 'Le nom du certificat est obligatoire.');
            $this->redirect('/certifications/manage');
            return;
        }

        $titreCours = (string) ($_POST['titre_cours'] ?? '');
        $fileName = $this->uploadCertificatFile();
        (new DocacCertificat())->addCertificat(
            $nom,
            (string) ($_POST['date_obtention'] ?? ''),
            (string) ($_POST['organisation'] ?? ''),
            $fileName,
            $titreCours
        );
        $this->setCertFlash('success', 'Certificat enregistré.');

        $this->redirect('/certifications/manage');
    }

    public function editCertificat(): void
    {
        $this->requireLogin();
        if (!$this->isStaffOrAdmin()) {
            $this->setCertFlash('warning', 'La modification du catalogue de certificats est réservée au personnel.');
            $this->redirect('/certifications');
            return;
        }

        if (!$this->docacSchemaReady()) {
            $this->redirect('/certifications/manage');
            return;
        }

        if (!$this->isPost()) {
            $this->redirect('/certifications/manage');
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $titreCours = (string) ($_POST['titre_cours'] ?? '');
        $fileName = '';
        if (isset($_FILES['certif_file']) && (int) ($_FILES['certif_file']['error'] ?? 1) === 0) {
            $fileName = $this->uploadCertificatFile();
        }

        $ok = (new DocacCertificat())->updateCertificat(
            $id,
            (string) ($_POST['nom_certificat'] ?? ''),
            (string) ($_POST['date_obtention'] ?? ''),
            (string) ($_POST['organisation'] ?? ''),
            $fileName,
            $titreCours
        );
        $this->setCertFlash($ok ? 'success' : 'danger', $ok ? 'Certificat mis à jour.' : 'Mise à jour impossible.');

        $this->redirect('/certifications/manage');
    }

    public function deleteCertificat(): void
    {
        $this->requireLogin();
        if (!$this->isStaffOrAdmin()) {
            $this->setCertFlash('warning', 'La suppression du catalogue de certificats est réservée au personnel.');
            $this->redirect('/certifications');
            return;
        }

        if (!$this->docacSchemaReady()) {
            $this->redirect('/certifications/manage');
            return;
        }

        if (!$this->isPost()) {
            $this->redirect('/certifications/manage');
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $ok = (new DocacCertificat())->deleteCertificat($id);
            $this->setCertFlash($ok ? 'success' : 'danger', $ok ? 'Certificat supprimé.' : 'Suppression impossible.');
        }

        $this->redirect('/certifications/manage');
    }

    public function demanderCertification(): void
    {
        $this->requireLogin();
        if (!$this->isStudentLike()) {
            $this->redirectByUserRole((string) ($_SESSION['user']['role'] ?? ''));

            return;
        }

        if (!$this->docacSchemaReady()) {
            $this->redirect('/certifications');
            return;
        }

        if (!$this->isPost()) {
            $this->redirect('/certifications');
            return;
        }

        $nom = trim((string) ($_POST['nom_certificat'] ?? ''));
        $date = trim((string) ($_POST['date_souhaitee'] ?? $_POST['date_obtention'] ?? ''));
        if ($nom === '' || $date === '') {
            $this->setCertFlash('danger', 'Certificat visé et date souhaitée sont obligatoires.');
            $this->redirect('/certifications');
            return;
        }

        $titreCours = trim((string) ($_POST['titre_cours'] ?? ''));
        if ($titreCours === '') {
            $this->setCertFlash('danger', 'Choisissez le cours du catalogue lié à votre demande (requis pour le quiz DOCAC).');
            $this->redirect('/certifications#us-parcours-demande');
            return;
        }

        if (!(new DocacCours())->coursExiste($titreCours)) {
            $this->setCertFlash('danger', 'Le cours sélectionné n’existe pas dans le catalogue. Rechargez la page et choisissez un cours valide.');
            $this->redirect('/certifications#us-parcours-demande');
            return;
        }

        $fileName = '';
        if (isset($_FILES['certif_file']) && (int) ($_FILES['certif_file']['error'] ?? 1) === 0) {
            $fileName = $this->uploadCertificatFile();
        }

        (new DocacDemandeCertification())->store($this->currentUserId(), [
            'nom_certificat' => $nom,
            'titre_cours' => $titreCours,
            'organisation' => trim((string) ($_POST['organisation'] ?? 'UniServe')),
            'date_souhaitee' => $date,
            'heure_preferee' => trim((string) ($_POST['heure_preferee'] ?? '')),
            'notes' => trim((string) ($_POST['notes'] ?? '')),
            'fichier_path' => $fileName !== '' ? $fileName : null,
        ]);

        $this->setCertFlash('success', 'Demande enregistrée.');
        $this->redirect('/certifications');
    }

    public function passerQuiz(): void
    {
        $this->requireLogin();
        if (!$this->isStudentLike()) {
            $this->redirectByUserRole((string) ($_SESSION['user']['role'] ?? ''));

            return;
        }

        if (!$this->docacSchemaReady()) {
            $this->redirect('/certifications');
            return;
        }

        if (!$this->isPost()) {
            $this->redirect('/certifications');
            return;
        }

        $quizId = (int) ($_POST['quiz_id'] ?? 0);
        $quizModel = new DocacQuiz();
        $quiz = $quizModel->getById($quizId);
        if ($quiz === null || (string) ($quiz['statut'] ?? '') !== 'en_attente') {
            $this->setCertFlash('danger', 'Quiz invalide ou déjà complété.');
            $this->redirect('/certifications');
            return;
        }

        $demande = (new DocacDemandeCertification())->getById((int) ($quiz['demande_id'] ?? 0));
        if ($demande === null || (int) ($demande['utilisateur_id'] ?? 0) !== $this->currentUserId()) {
            $this->setCertFlash('danger', 'Accès refusé à ce quiz.');
            $this->redirect('/certifications');
            return;
        }

        $questions = $quiz['questions'] ?? [];
        $score = 0;
        foreach ($questions as $i => $q) {
            $userAnswer = isset($_POST['answer_' . $i]) ? (int) $_POST['answer_' . $i] : -1;
            if ($userAnswer === (int) ($q['correct'] ?? -1)) {
                $score++;
            }
        }

        $quizModel->submit($quizId, $score);
        $statut = $score >= 3 ? 'accepte' : 'refuse';
        (new DocacDemandeCertification())->updateStatut(
            (int) $quiz['demande_id'],
            $statut,
            'Quiz passé — score : ' . $score . '/5'
        );

        $_SESSION['notif_quiz'] = [
            'statut' => $statut,
            'score' => $score,
            'cours' => (string) ($quiz['cours_titre'] ?? ''),
        ];

        $this->redirect('/certifications');
    }

    /**
     * Secure download: type = cours|cert|demande
     */
    public function download(string $type, string $filename): void
    {
        $this->requireLogin();

        if (!$this->docacSchemaReady()) {
            http_response_code(503);
            exit('Service indisponible.');
        }

        $filename = $this->safeBasename($filename);
        if ($filename === '' || $filename === '.' || $filename === '..') {
            http_response_code(404);
            exit('Fichier invalide.');
        }

        $sub = match ($type) {
            'cours' => 'cours',
            'cert' => 'certifications',
            'demande' => 'certifications',
            default => '',
        };
        if ($sub === '') {
            http_response_code(404);
            exit('Type invalide.');
        }

        $path = $this->uploadDir($sub) . '/' . $filename;
        if (!is_file($path)) {
            http_response_code(404);
            exit('Introuvable.');
        }

        if ($type === 'cert' && !$this->isStaffOrAdmin()) {
            http_response_code(403);
            exit('Accès refusé.');
        }

        if ($type === 'demande' && !$this->isStaffOrAdmin()) {
            $demModel = new DocacDemandeCertification();
            $allowed = false;
            foreach ($demModel->getAllByUser($this->currentUserId()) as $d) {
                if ((string) ($d['fichier_path'] ?? '') === $filename) {
                    $allowed = true;
                    break;
                }
            }
            if (!$allowed) {
                http_response_code(403);
                exit('Accès refusé.');
            }
        }

        $mime = mime_content_type($path) ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Length: ' . (string) filesize($path));
        readfile($path);
        exit;
    }

    private function uploadCoursImage(): string
    {
        if (!isset($_FILES['cours_image']) || (int) ($_FILES['cours_image']['error'] ?? 1) !== 0) {
            return '';
        }

        $uploadDir = $this->uploadDir('cours');
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $tmp = (string) ($_FILES['cours_image']['tmp_name'] ?? '');
        $fileType = $tmp !== '' ? (string) (mime_content_type($tmp) ?: '') : '';
        if (!in_array($fileType, $allowed, true)) {
            return '';
        }
        if ((int) ($_FILES['cours_image']['size'] ?? 0) > 5 * 1024 * 1024) {
            return '';
        }

        $ext = pathinfo((string) ($_FILES['cours_image']['name'] ?? ''), PATHINFO_EXTENSION);
        $fileName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (move_uploaded_file($tmp, $uploadDir . '/' . $fileName)) {
            return $fileName;
        }

        return '';
    }

    /**
     * @return list<array{nom: string, path: string}>
     */
    private function uploadCoursFichiers(): array
    {
        if (!isset($_FILES['cours_fichiers']['name']) || !is_array($_FILES['cours_fichiers']['name'])) {
            return [];
        }

        $uploadDir = $this->uploadDir('cours');
        $allowed = [
            'application/pdf', 'image/jpeg', 'image/png',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];
        $result = [];
        $count = count($_FILES['cours_fichiers']['name']);
        for ($i = 0; $i < $count; $i++) {
            if ((int) ($_FILES['cours_fichiers']['error'][$i] ?? 1) !== 0) {
                continue;
            }
            if ((int) ($_FILES['cours_fichiers']['size'][$i] ?? 0) > 20 * 1024 * 1024) {
                continue;
            }
            $tmpName = (string) ($_FILES['cours_fichiers']['tmp_name'][$i] ?? '');
            $origName = basename((string) ($_FILES['cours_fichiers']['name'][$i] ?? ''));
            $mt = $tmpName !== '' ? (string) (mime_content_type($tmpName) ?: '') : '';
            if (!in_array($mt, $allowed, true)) {
                continue;
            }
            $fileName = time() . '_' . bin2hex(random_bytes(4)) . '_' . $origName;
            if (move_uploaded_file($tmpName, $uploadDir . '/' . $fileName)) {
                $result[] = ['nom' => $origName, 'path' => $fileName];
            }
        }

        return $result;
    }

    private function uploadCertificatFile(): string
    {
        if (!isset($_FILES['certif_file']) || (int) ($_FILES['certif_file']['error'] ?? 1) !== 0) {
            return '';
        }

        $uploadDir = $this->uploadDir('certifications');
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        $tmp = (string) ($_FILES['certif_file']['tmp_name'] ?? '');
        $fileType = $tmp !== '' ? (string) (mime_content_type($tmp) ?: '') : '';
        if (!in_array($fileType, $allowedTypes, true)) {
            return '';
        }
        if ((int) ($_FILES['certif_file']['size'] ?? 0) > 5 * 1024 * 1024) {
            return '';
        }

        $fileName = time() . '_' . bin2hex(random_bytes(4)) . '_' . basename((string) ($_FILES['certif_file']['name'] ?? ''));
        if (move_uploaded_file($tmp, $uploadDir . '/' . $fileName)) {
            return $fileName;
        }

        return '';
    }
}
