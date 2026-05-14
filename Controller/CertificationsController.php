<?php
declare(strict_types=1);

/**
 * CertificationsController
 *
 * Front-office (students): /certifications/index  — view courses, certs, submit quiz demande, pass quiz
 * Back-office (admin):     /certifications/manage — full CRUD + quiz IA via BackofficeDocumentsController
 *
 * This controller handles the student-facing routes only.
 * Admin CRUD is delegated to BackofficeDocumentsController (manage route is its index action).
 */
class CertificationsController extends Controller
{
    // ── Default entry point ───────────────────────────────────────

    public function landing(): void
    {
        $role = (string) ($_SESSION['user']['role'] ?? '');
        if (in_array($role, ['admin', 'staff'], true)) {
            $this->redirect('/certifications/manage');
        } else {
            $this->redirect('/certifications/index');
        }
    }

    // ── Student front index ───────────────────────────────────────

    public function index(): void
    {
        $demModel  = new DemandeCertification();
        $quizModel = new Quiz();
        $demandes  = $demModel->getAll();

        // Attach quiz to each demande for the "Quiz à passer" section
        foreach ($demandes as &$d) {
            $quiz      = $quizModel->getByDemandeId((int) $d['id']);
            $d['quiz'] = $quiz;
        }
        unset($d);

        $this->render('certifications/index', [
            'title'       => 'Mes Certifications',
            'cours'       => (new Cours())->getAllCours(),
            'certificats' => (new Certificat())->getAllCertificats(),
            'demandes'    => $demandes,
        ]);
    }

    // ── Admin backoffice manage ───────────────────────────────────

    public function manage(): void
    {
        $demModel  = new DemandeCertification();
        $quizModel = new Quiz();
        $demandes  = $demModel->getAll();

        foreach ($demandes as &$d) {
            $quiz      = $quizModel->getByDemandeId((int) $d['id']);
            $d['quiz'] = $quiz;
            if ($quiz && in_array($quiz['statut'], ['accepte', 'refuse'], true)) {
                if ($d['statut'] === 'quiz_envoye') {
                    $demModel->updateStatut(
                        (int) $d['id'],
                        $quiz['statut'],
                        'Quiz passé — score : ' . $quiz['score'] . '/5'
                    );
                    $d['statut'] = $quiz['statut'];
                }
            }
        }
        unset($d);

        $this->render('certifications/manage', [
            'title'         => 'Gestion des Certifications',
            'cours'         => (new Cours())->getAllCours(),
            'certificats'   => (new Certificat())->getAllCertificats(),
            'demandes'      => $demandes,
            'nb_en_attente' => $demModel->countByStatut('en_attente'),
        ]);
    }

    // ── Demande de certification (student submits) ────────────────

    public function demanderCertification(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/certifications/index');
        }

        $fileName = '';
        if (isset($_FILES['certif_file']) && $_FILES['certif_file']['error'] === 0) {
            $fileName = $this->uploadCertifFile();
        }

        // Use session user_id if logged in, else default to demo student id 2
        $userId = (int) ($_SESSION['user']['id'] ?? 2);

        (new DemandeCertification())->storeWithUser([
            'utilisateur_id' => $userId,
            'nom_certificat' => trim($_POST['nom_certificat'] ?? ''),
            'titre_cours'    => trim($_POST['titre_cours']    ?? ''),
            'organisation'   => trim($_POST['organisation']   ?? 'UniServe'),
            'date_souhaitee' => trim($_POST['date_obtention'] ?? ''),
            'heure_preferee' => trim($_POST['heure_preferee'] ?? ''),
            'notes'          => trim($_POST['notes']          ?? ''),
            'fichier_path'   => $fileName,
        ]);

        $this->redirect('/certifications/index?success=1');
    }

    // ── Student: passer le quiz ───────────────────────────────────

    public function passerQuiz(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/certifications/index');
        }

        $quizId = (int) ($_POST['quiz_id'] ?? 0);
        if ($quizId <= 0) {
            $this->redirect('/certifications/index');
        }

        $quizModel = new Quiz();
        $quiz      = $quizModel->getById($quizId);

        if (!$quiz || $quiz['statut'] !== 'en_attente') {
            $this->redirect('/certifications/index');
        }

        // Compute score
        $questions = $quiz['questions'] ?? [];
        $score     = 0;
        foreach ($questions as $i => $q) {
            $answer = isset($_POST["answer_{$i}"]) ? (int) $_POST["answer_{$i}"] : -1;
            if ($answer === (int) ($q['correct'] ?? -1)) $score++;
        }

        $quizModel->submit($quizId, $score);

        $statut = $score >= 3 ? 'accepte' : 'refuse';
        (new DemandeCertification())->updateStatut(
            (int) $quiz['demande_id'],
            $statut,
            'Quiz passé — score : ' . $score . '/5'
        );

        $_SESSION['notif_quiz'] = [
            'statut' => $statut,
            'score'  => $score,
            'cours'  => $quiz['cours_titre'] ?? '',
        ];

        $this->redirect('/certifications/index');
    }

    // ── Admin: envoyer Quiz IA (Groq) ─────────────────────────────

    public function envoyerQuiz(): void
    {
        set_time_limit(120);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/certifications/manage');
        }

        $id      = (int) ($_POST['id'] ?? 0);
        $demande = (new DemandeCertification())->getById($id);

        if (!$demande) {
            $this->redirect('/certifications/manage');
        }

        $coursTitre  = $demande['titre_cours'] ?? $demande['nom_certificat'];
        $cours       = (new Cours())->getCoursParTitre($coursTitre);
        $description = trim($cours['description'] ?? '');
        $contenu     = trim($cours['contenu']     ?? '');

        $fichiers = !empty($cours['fichiers'])
            ? $cours['fichiers']
            : (!empty($cours['fichiers_json'])
                ? (json_decode($cours['fichiers_json'], true) ?? [])
                : []);

        $pdfFiles  = $this->getPdfFilesForAPI($fichiers);
        $questions = $this->generateQuizWithGroq(
            $coursTitre, $description, $contenu, $demande['nom_certificat'], $pdfFiles
        );

        (new Quiz())->create($id, $coursTitre, $questions);
        (new DemandeCertification())->markQuizEnvoye($id);

        $this->redirect('/certifications/manage#demandes');
    }

    // ── Admin: accepter / refuser demande ─────────────────────────

    public function accepterDemande(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id          = (int) ($_POST['id'] ?? 0);
            $commentaire = trim($_POST['commentaire'] ?? '');
            if ($id > 0) {
                $demModel = new DemandeCertification();
                $demModel->updateStatut($id, 'accepte', $commentaire);
                $demande = $demModel->getById($id);
                $_SESSION['notif_etudiant'] = ['type' => 'accepte', 'cert' => $demande['nom_certificat'] ?? ''];
            }
        }
        $this->redirect('/certifications/manage#demandes');
    }

    public function refuserDemande(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id          = (int) ($_POST['id'] ?? 0);
            $commentaire = trim($_POST['commentaire'] ?? '');
            if ($id > 0) {
                $demModel = new DemandeCertification();
                $demModel->updateStatut($id, 'refuse', $commentaire);
                $demande = $demModel->getById($id);
                $_SESSION['notif_etudiant'] = ['type' => 'refuse', 'cert' => $demande['nom_certificat'] ?? ''];
            }
        }
        $this->redirect('/certifications/manage#demandes');
    }

    // ── Admin: CRUD Cours ─────────────────────────────────────────

    public function storeCours(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $imagePath = $this->uploadCoursImage();
            $fichiers  = $this->uploadCoursFichiers();
            (new Cours())->createCours($_POST, $imagePath, $fichiers);
        }
        $this->redirect('/certifications/manage');
    }

    public function editCours(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $oldTitre  = $_POST['old_titre'] ?? '';
            $imagePath = $this->uploadCoursImage();
            $newFiles  = $this->uploadCoursFichiers();
            $existing  = [];
            if (!empty($_POST['fichiers_existants'])) {
                $existing = json_decode($_POST['fichiers_existants'], true) ?? [];
            }
            $fichiers = array_merge($existing, $newFiles);
            (new Cours())->updateCours($_POST, $oldTitre, $imagePath, $fichiers);
        }
        $this->redirect('/certifications/manage');
    }

    public function deleteCours(): void
    {
        $titre = trim($_POST['titre'] ?? '');
        if (!empty($titre)) (new Cours())->deleteCours($titre);
        $this->redirect('/certifications/manage');
    }

    // ── Admin: CRUD Certificats ───────────────────────────────────

    public function storeCertificat(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titreCours = $_POST['titre_cours'] ?? '';
            $fileName   = $this->uploadCertifFile();
            (new Certificat())->addCertificat(
                $_POST['nom_certificat'], $_POST['date_obtention'],
                $_POST['organisation'], $fileName, $titreCours
            );
        }
        $this->redirect('/certifications/manage');
    }

    public function editCertificat(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id         = (int) ($_POST['id'] ?? 0);
            $titreCours = $_POST['titre_cours'] ?? '';
            $fileName   = '';
            if (isset($_FILES['certif_file']) && $_FILES['certif_file']['error'] === 0) {
                $fileName = $this->uploadCertifFile();
            }
            (new Certificat())->updateCertificat(
                $id, $_POST['nom_certificat'], $_POST['date_obtention'],
                $_POST['organisation'], $fileName, $titreCours
            );
        }
        $this->redirect('/certifications/manage');
    }

    public function deleteCertificat(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) (new Certificat())->deleteCertificat($id);
        $this->redirect('/certifications/manage');
    }

    // ── Upload helpers ────────────────────────────────────────────

    private function uploadCertifFile(): string
    {
        if (!isset($_FILES['certif_file']) || $_FILES['certif_file']['error'] !== 0) return '';
        $uploadDir    = 'public/uploads/certificats/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        $fileType     = mime_content_type($_FILES['certif_file']['tmp_name']);
        if (!in_array($fileType, $allowedTypes, true)) return '';
        if ($_FILES['certif_file']['size'] > 5 * 1024 * 1024) return '';
        $fileName   = time() . '_' . bin2hex(random_bytes(4)) . '_' . basename($_FILES['certif_file']['name']);
        $targetPath = $uploadDir . $fileName;
        return move_uploaded_file($_FILES['certif_file']['tmp_name'], $targetPath) ? $fileName : '';
    }

    private function uploadCoursImage(): string
    {
        if (!isset($_FILES['cours_image']) || $_FILES['cours_image']['error'] !== 0) return '';
        $uploadDir = 'public/uploads/cours/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $allowed   = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType  = mime_content_type($_FILES['cours_image']['tmp_name']);
        if (!in_array($fileType, $allowed, true)) return '';
        if ($_FILES['cours_image']['size'] > 5 * 1024 * 1024) return '';
        $ext      = pathinfo($_FILES['cours_image']['name'], PATHINFO_EXTENSION);
        $fileName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        return move_uploaded_file($_FILES['cours_image']['tmp_name'], $uploadDir . $fileName) ? $fileName : '';
    }

    private function uploadCoursFichiers(): array
    {
        if (!isset($_FILES['cours_fichiers']) || empty($_FILES['cours_fichiers']['name'][0])) return [];
        $uploadDir = 'public/uploads/cours/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $allowed = [
            'application/pdf', 'image/jpeg', 'image/png',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];
        $result = [];
        $count  = count($_FILES['cours_fichiers']['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($_FILES['cours_fichiers']['error'][$i] !== 0) continue;
            if ($_FILES['cours_fichiers']['size'][$i] > 20 * 1024 * 1024) continue;
            $tmpName  = $_FILES['cours_fichiers']['tmp_name'][$i];
            $origName = basename($_FILES['cours_fichiers']['name'][$i]);
            if (!in_array(mime_content_type($tmpName), $allowed, true)) continue;
            $fileName = time() . '_' . bin2hex(random_bytes(4)) . '_' . $origName;
            if (move_uploaded_file($tmpName, $uploadDir . $fileName)) {
                $result[] = ['nom' => $origName, 'path' => $fileName];
            }
        }
        return $result;
    }

    // ── PDF text extraction ───────────────────────────────────────

    private function getPdfFilesForAPI(array $fichiers): array
    {
        $pdfs    = [];
        $baseDir = 'public/uploads/cours/';
        foreach ($fichiers as $f) {
            $path = $baseDir . ($f['path'] ?? '');
            $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (!file_exists($path)) continue;
            $text = '';
            if ($ext === 'pdf') {
                $text = $this->extractPdfText($path);
            } elseif (in_array($ext, ['txt', 'md'], true)) {
                $text = @file_get_contents($path) ?: '';
            }
            if (!empty($text)) {
                $pdfs[] = ['name' => $f['nom'] ?? basename($path), 'text' => substr($text, 0, 3000)];
            }
            if (count($pdfs) >= 3) break;
        }
        return $pdfs;
    }

    private function extractPdfText(string $path): string
    {
        $escaped = escapeshellarg($path);
        $out     = @shell_exec("pdftotext {$escaped} - 2>/dev/null");
        if (!empty($out) && strlen(trim($out)) > 30) return trim(substr($out, 0, 5000));
        foreach (['C:\\poppler\\Library\\bin\\pdftotext.exe', 'C:\\poppler\\bin\\pdftotext.exe'] as $p) {
            if (file_exists($p)) {
                $out = @shell_exec('"' . $p . '" ' . $escaped . ' -');
                if (!empty($out) && strlen(trim($out)) > 30) return trim(substr($out, 0, 5000));
            }
        }
        $content = @file_get_contents($path);
        if (!$content) return '';
        $text = '';
        if (preg_match_all('/stream\s*(.*?)\s*endstream/s', $content, $streams)) {
            foreach ($streams[1] as $stream) {
                $decoded = @gzuncompress($stream);
                $data    = $decoded !== false ? $decoded : $stream;
                if (preg_match_all('/BT\s*(.*?)\s*ET/s', $data, $m)) {
                    foreach ($m[1] as $block) {
                        if (preg_match_all('/\(([^)]{1,500})\)\s*Tj/s', $block, $s)) {
                            foreach ($s[1] as $str) {
                                $clean = preg_replace('/[^ -~\xC0-\xFF]/', ' ', $str);
                                if (strlen(trim($clean)) > 2) $text .= trim($clean) . ' ';
                            }
                        }
                    }
                }
            }
        }
        if (strlen(trim($text)) < 50) {
            preg_match_all('/[a-zA-Z\xC0-\xFF][a-zA-Z\xC0-\xFF0-9\s\.,\:\;\-\+\=]{15,}/', $content, $m2);
            $text = implode(' ', array_slice($m2[0], 0, 200));
        }
        return trim(substr(preg_replace('/\s+/', ' ', $text), 0, 5000));
    }

    // ── Groq quiz generation ──────────────────────────────────────

    private function generateQuizWithGroq(
        string $coursTitre,
        string $description,
        string $contenu,
        string $nomCertif,
        array  $pdfFiles = []
    ): array {
        $apiKey = defined('GROQ_QUIZ_API_KEY') ? GROQ_QUIZ_API_KEY : (string) (getenv('GROQ_API_KEY') ?: '');
        $model  = defined('GROQ_QUIZ_MODEL')   ? GROQ_QUIZ_MODEL  : (string) (getenv('GROQ_MODEL') ?: 'llama-3.3-70b-versatile');

        if (empty($apiKey)) {
            error_log('CertificationsController::generateQuizWithGroq — GROQ_API_KEY not set');
            return $this->fallbackQuestions($coursTitre);
        }

        // Sanitize all inputs to valid UTF-8 to prevent json_encode failures
        $utf8 = fn(string $s): string => mb_convert_encoding($s, 'UTF-8', 'UTF-8');
        $coursTitre  = $utf8($coursTitre);
        $description = $utf8($description);
        $contenu     = $utf8($contenu);
        $nomCertif   = $utf8($nomCertif);

        $context = '';
        if (!empty($description)) $context .= "DESCRIPTION : {$description}\n\n";
        if (!empty($contenu))     $context .= "CONTENU DU COURS :\n{$contenu}\n\n";
        foreach ($pdfFiles as $pdf) {
            if (!empty($pdf['text'])) $context .= "=== DOCUMENT : {$utf8($pdf['name'])} ===\n{$utf8($pdf['text'])}\n\n";
        }

        $userPrompt  = "Génère exactement 5 questions QCM en français.\n\n";
        $userPrompt .= "COURS : {$coursTitre}\nCERTIFICAT VISÉ : {$nomCertif}\n\n";
        if (!empty($context)) {
            $userPrompt .= "CONTENU :\n{$context}";
            $userPrompt .= "RÈGLE : Chaque question DOIT porter sur un élément PRÉCIS du contenu ci-dessus.\n\n";
        }
        $userPrompt .= "STYLE : Questions précises (faits, valeurs, commandes, définitions). 4 options, 1 seule correcte.\n\n";
        $userPrompt .= 'Réponds UNIQUEMENT avec ce JSON (sans markdown) : [{"question":"...","options":["A","B","C","D"],"correct":0},{"question":"...","options":["A","B","C","D"],"correct":1},{"question":"...","options":["A","B","C","D"],"correct":2},{"question":"...","options":["A","B","C","D"],"correct":0},{"question":"...","options":["A","B","C","D"],"correct":3}]';

        [$httpCode, $rawBody, $curlErr] = GroqClient::postChatCompletions($apiKey, [
            'model'       => $model,
            'messages'    => [
                ['role' => 'system', 'content' => 'Tu es un formateur expert. Génère uniquement du JSON valide, sans texte autour.'],
                ['role' => 'user',   'content' => $userPrompt],
            ],
            'temperature' => 0.3,
            'max_tokens'  => 2000,
        ], 60);

        if (!empty($curlErr)) {
            error_log("Groq connection failed: {$curlErr}");
            return $this->fallbackQuestions($coursTitre);
        }
        if ($httpCode === 200 && $rawBody) {
            $data = json_decode($rawBody, true);
            $raw  = trim(preg_replace('/```json|```/', '', $data['choices'][0]['message']['content'] ?? ''));
            if (preg_match('/\[.*\]/s', $raw, $m)) $raw = $m[0];
            $questions = json_decode($raw, true);
            if (is_array($questions) && count($questions) >= 5) return array_slice($questions, 0, 5);
            if (is_array($questions) && count($questions) > 0) {
                return array_slice(array_merge($questions, $this->fallbackQuestions($coursTitre)), 0, 5);
            }
        }

        error_log("Groq quiz failed HTTP:{$httpCode}");
        return $this->fallbackQuestions($coursTitre);
    }

    private function fallbackQuestions(string $coursTitre): array
    {
        return [
            ['question' => "Quelle est la principale technologie enseignée dans \"{$coursTitre}\" ?",
             'options'  => ['La technologie principale du domaine', 'La bureautique standard', 'La gestion RH', 'La comptabilité'],
             'correct'  => 0],
            ['question' => "Quel concept est fondamental pour réussir la certification de ce cours ?",
             'options'  => ['Les bases théoriques et pratiques du domaine', 'La rédaction de rapports', 'La communication commerciale', 'La gestion budgétaire'],
             'correct'  => 0],
            ['question' => "Quelle compétence clé ce cours développe-t-il en priorité ?",
             'options'  => ['Maîtrise des outils et protocoles du domaine', 'Expression artistique', 'Comptabilité analytique', 'Droit du travail'],
             'correct'  => 0],
            ['question' => "Comment sont évalués les acquis dans ce type de certification ?",
             'options'  => ['QCM technique + cas pratiques', 'Entretien oral uniquement', 'Portfolio artistique', 'Aucune évaluation'],
             'correct'  => 0],
            ['question' => "Quel niveau de maîtrise est requis pour valider cette certification ?",
             'options'  => ['Compréhension et application des concepts clés', 'Notions très basiques', 'Aucune connaissance', 'Niveau expert mondial uniquement'],
             'correct'  => 0],
        ];
    }
}
