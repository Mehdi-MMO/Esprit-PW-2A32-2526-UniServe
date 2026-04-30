<?php
declare(strict_types=1);

// Load Ollama config
$_ollamaConfigPath = __DIR__ . '/../config/anthropic.php';
if (file_exists($_ollamaConfigPath)) {
    require_once $_ollamaConfigPath;
}

class BackofficeDocumentsController extends Controller
{
    public function index(): void
    {
        $_SESSION['user'] = ['role' => 'admin', 'nom' => 'Test', 'prenom' => 'Admin'];

        $demModel  = $this->model('DemandeCertification');
        $quizModel = $this->model('Quiz');
        $demandes  = $demModel->getAll();

        // Synchroniser le statut de chaque demande avec son quiz
        foreach ($demandes as &$d) {
            $quiz = $quizModel->getByDemandeId((int) $d['id']);
            $d['quiz'] = $quiz;
            if ($quiz && in_array($quiz['statut'], ['accepte', 'refuse'], true)) {
                if ($d['statut'] === 'quiz_envoye') {
                    $demModel->updateStatut(
                        (int) $d['id'],
                        $quiz['statut'],
                        "Quiz passé — score : {$quiz['score']}/5"
                    );
                    $d['statut'] = $quiz['statut'];
                }
            }
        }
        unset($d);

        $this->render('documents/backoffice', [
            'title'         => 'Backoffice — Gestion des Documents',
            'cours'         => $this->model('Cours')->getAllCours(),
            'certificats'   => $this->model('Certificat')->getAllCertificats(),
            'demandes'      => $demandes,
            'nb_en_attente' => $demModel->countByStatut('en_attente'),
        ]);
    }

    // ── Quiz IA : génération + envoi ──────────────────────────────

    public function envoyerQuiz(): void
    {
        set_time_limit(300);
        ini_set('max_execution_time', '300');
        $_SESSION['user'] = ['role' => 'admin', 'nom' => 'Test', 'prenom' => 'Admin'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /UniServe/backofficeDocuments/index');
            exit;
        }

        $id      = (int) ($_POST['id'] ?? 0);
        $demande = $this->model('DemandeCertification')->getById($id);

        if (!$demande) {
            header('Location: /UniServe/backofficeDocuments/index');
            exit;
        }

        $coursTitre  = $demande['titre_cours'] ?? $demande['nom_certificat'];
        $cours       = $this->model('Cours')->getCoursParTitre($coursTitre);
        $description = trim($cours['description'] ?? '');
        $contenu     = trim($cours['contenu']     ?? '');

        // Récupère les fichiers PDF du cours et extrait le texte
        $fichiers = !empty($cours['fichiers'])
            ? $cours['fichiers']
            : (!empty($cours['fichiers_json'])
                ? (json_decode($cours['fichiers_json'], true) ?? [])
                : []);
        $pdfFiles = $this->getPdfFilesForAPI($fichiers);

        // Génération via Ollama (qwen2.5:14b)
        $questions = $this->generateQuizWithAI(
            $coursTitre, $description, $contenu, $demande['nom_certificat'], $pdfFiles
        );

        $this->model('Quiz')->create($id, $coursTitre, $questions);
        $this->model('DemandeCertification')->markQuizEnvoye($id);

        header('Location: /UniServe/backofficeDocuments/index#demandes');
        exit;
    }

    // ── Extraction de texte depuis les PDF du cours ─────────────

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
                $pdfs[] = [
                    'name' => $f['nom'] ?? basename($path),
                    'text' => substr($text, 0, 3000),
                ];
            }
            if (count($pdfs) >= 3) break;
        }
        return $pdfs;
    }

    private function extractPdfText(string $path): string
    {
        // Méthode 1 : pdftotext si installé (Linux/Mac)
        $escaped = escapeshellarg($path);
        $out = @shell_exec("pdftotext {$escaped} - 2>/dev/null");
        if (!empty($out) && strlen(trim($out)) > 30) {
            return trim(substr($out, 0, 5000));
        }

        // Méthode 2 : pdftotext Windows
        $winPaths = [
            'C:\poppler\Library\bin\pdftotext.exe',
            'C:\poppler\bin\pdftotext.exe',
            'C:\Program Files\poppler\bin\pdftotext.exe',
        ];
        foreach ($winPaths as $pdftotext) {
            if (file_exists($pdftotext)) {
                $cmd = '"' . $pdftotext . '" ' . $escaped . ' -';
                $out = @shell_exec($cmd);
                if (!empty($out) && strlen(trim($out)) > 30) {
                    return trim(substr($out, 0, 5000));
                }
            }
        }

        // Méthode 3 : extraction PHP pure (fallback)
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
                                $clean = preg_replace('/[^ -~À-ÿ]/', ' ', $str);
                                if (strlen(trim($clean)) > 2) $text .= trim($clean) . ' ';
                            }
                        }
                        if (preg_match_all('/\[([^\]]+)\]\s*TJ/s', $block, $tj)) {
                            foreach ($tj[1] as $arr) {
                                if (preg_match_all('/\(([^)]{1,200})\)/', $arr, $parts)) {
                                    foreach ($parts[1] as $p) {
                                        $clean = preg_replace('/[^ -~À-ÿ]/', ' ', $p);
                                        if (strlen(trim($clean)) > 2) $text .= trim($clean) . ' ';
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if (strlen(trim($text)) < 50) {
            preg_match_all('/[a-zA-ZÀ-ÿ][a-zA-ZÀ-ÿ0-9\s\.\,\:\;\-\+\=]{15,}/', $content, $m2);
            $text = implode(' ', array_slice($m2[0], 0, 200));
        }

        $text = preg_replace('/\s+/', ' ', $text);
        return trim(substr($text, 0, 5000));
    }

    // ── Génération de quiz via Ollama (qwen2.5:14b en local — gratuit) ──

    private function generateQuizWithAI(
        string $coursTitre,
        string $description,
        string $contenu,
        string $nomCertif,
        array  $pdfFiles = []
    ): array {
        $ollamaUrl   = defined('OLLAMA_URL')   ? OLLAMA_URL   : 'http://localhost:11434';
        $ollamaModel = defined('OLLAMA_MODEL') ? OLLAMA_MODEL : 'qwen2.5:14b';

        // Construction du contexte
        $context = '';
        if (!empty($description)) $context .= "DESCRIPTION : {$description}\n\n";
        if (!empty($contenu))     $context .= "CONTENU DU COURS :\n{$contenu}\n\n";
        foreach ($pdfFiles as $pdf) {
            if (!empty($pdf['text'])) {
                $context .= "=== DOCUMENT : {$pdf['name']} ===\n{$pdf['text']}\n\n";
            }
        }
        $hasContent = !empty($context);

        $prompt  = "Tu es un formateur expert. Génère exactement 5 questions QCM en français.\n\n";
        $prompt .= "COURS : {$coursTitre}\n";
        $prompt .= "CERTIFICAT VISÉ : {$nomCertif}\n\n";

        if ($hasContent) {
            $prompt .= "CONTENU SUR LEQUEL BASER LES QUESTIONS :\n{$context}";
            $prompt .= "RÈGLE ABSOLUE : Chaque question DOIT porter sur un élément PRÉCIS du contenu ci-dessus.\n";
            $prompt .= "Questions impossibles à répondre sans avoir étudié ce cours spécifique.\n\n";
        }

        $prompt .= "STYLE EXIGÉ :\n";
        $prompt .= "- Questions précises : faits concrets, valeurs exactes, commandes, formules, définitions\n";
        $prompt .= "- PAS de questions génériques comme 'quel est l'objectif' ou 'quelle approche'\n";
        $prompt .= "- 4 options par question, 1 seule correcte, les 3 autres plausibles\n";
        $prompt .= "- Varie les types : définition exacte, valeur numérique, commande, comparaison, erreur courante\n\n";
        $prompt .= "Réponds UNIQUEMENT avec ce JSON valide (sans markdown, sans texte avant ou après) :\n";
        $prompt .= '[{"question":"...","options":["A","B","C","D"],"correct":0},{"question":"...","options":["A","B","C","D"],"correct":1},{"question":"...","options":["A","B","C","D"],"correct":2},{"question":"...","options":["A","B","C","D"],"correct":0},{"question":"...","options":["A","B","C","D"],"correct":3}]';

        $payload = [
            'model'   => $ollamaModel,
            'prompt'  => $prompt,
            'stream'  => false,
            'format'  => 'json',
            'options' => [
                'temperature' => 0.3,
                'num_predict' => 2000,
            ],
        ];

        $ch = curl_init($ollamaUrl . '/api/generate');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 300,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            error_log("Ollama connection failed: {$curlErr} — Ollama est-il démarré ?");
            return $this->fallbackQuestions($coursTitre);
        }

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            $raw  = $data['response'] ?? '';

            $raw = preg_replace('/```json|```/', '', $raw);
            $raw = trim($raw);

            if (preg_match('/\[.*\]/s', $raw, $m)) {
                $raw = $m[0];
            }

            $questions = json_decode($raw, true);
            if (is_array($questions) && count($questions) >= 5) {
                return array_slice($questions, 0, 5);
            }
            if (is_array($questions) && count($questions) > 0) {
                error_log("Ollama returned only " . count($questions) . " questions, padding with fallback");
                $fallback = $this->fallbackQuestions($coursTitre);
                return array_slice(array_merge($questions, $fallback), 0, 5);
            }
        }

        error_log("Ollama failed HTTP:{$httpCode} — " . substr((string) $response, 0, 200));
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

    // ── Acceptation / Refus direct (sans quiz) ──────────────────

    public function accepterDemande(): void
    {
        $_SESSION['user'] = ['role' => 'admin', 'nom' => 'Test', 'prenom' => 'Admin'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id          = (int) ($_POST['id'] ?? 0);
            $commentaire = trim($_POST['commentaire'] ?? '');
            if ($id > 0) {
                $this->model('DemandeCertification')->updateStatut($id, 'accepte', $commentaire);
                $demande = $this->model('DemandeCertification')->getById($id);
                $_SESSION['notif_etudiant'] = ['type' => 'accepte', 'cert' => $demande['nom_certificat'] ?? ''];
            }
        }
        header('Location: /UniServe/backofficeDocuments/index#demandes');
        exit;
    }

    public function refuserDemande(): void
    {
        $_SESSION['user'] = ['role' => 'admin', 'nom' => 'Test', 'prenom' => 'Admin'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id          = (int) ($_POST['id'] ?? 0);
            $commentaire = trim($_POST['commentaire'] ?? '');
            if ($id > 0) {
                $this->model('DemandeCertification')->updateStatut($id, 'refuse', $commentaire);
                $demande = $this->model('DemandeCertification')->getById($id);
                $_SESSION['notif_etudiant'] = ['type' => 'refuse', 'cert' => $demande['nom_certificat'] ?? ''];
            }
        }
        header('Location: /UniServe/backofficeDocuments/index#demandes');
        exit;
    }

    // ── CRUD COURS ──────────────────────────────────────────────

    public function storeCours(): void
    {
        $_SESSION['user'] = ['role' => 'admin', 'nom' => 'Test', 'prenom' => 'Admin'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $imagePath = $this->uploadCoursImage();
            $fichiers  = $this->uploadCoursFichiers();
            $this->model('Cours')->createCours($_POST, $imagePath, $fichiers);
            header('Location: /UniServe/backofficeDocuments/index');
            exit;
        }
    }

    public function editCours(): void
    {
        $_SESSION['user'] = ['role' => 'admin', 'nom' => 'Test', 'prenom' => 'Admin'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $oldTitre  = $_POST['old_titre'] ?? '';
            $imagePath = $this->uploadCoursImage();
            $newFiles  = $this->uploadCoursFichiers();

            // Repart des fichiers existants + ajoute les nouveaux
            $existing = [];
            if (!empty($_POST['fichiers_existants'])) {
                $existing = json_decode($_POST['fichiers_existants'], true) ?? [];
            }
            $fichiers = array_merge($existing, $newFiles);

            $this->model('Cours')->updateCours($_POST, $oldTitre, $imagePath, $fichiers);
            header('Location: /UniServe/backofficeDocuments/index');
            exit;
        }
    }

    public function deleteCours(): void
    {
        $_SESSION['user'] = ['role' => 'admin', 'nom' => 'Test', 'prenom' => 'Admin'];
        $titre = trim($_POST['titre'] ?? '');
        if (!empty($titre)) $this->model('Cours')->deleteCours($titre);
        header('Location: /UniServe/backofficeDocuments/index');
        exit;
    }

    // ── Helpers upload cours ───────────────────────────────────

    private function uploadCoursImage(): string
    {
        if (!isset($_FILES['cours_image']) || $_FILES['cours_image']['error'] !== 0) return '';
        $uploadDir = 'public/uploads/cours/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $allowed  = ['image/jpeg','image/png','image/gif','image/webp'];
        $fileType = mime_content_type($_FILES['cours_image']['tmp_name']);
        if (!in_array($fileType, $allowed, true)) return '';
        if ($_FILES['cours_image']['size'] > 5 * 1024 * 1024) return '';
        $ext      = pathinfo($_FILES['cours_image']['name'], PATHINFO_EXTENSION);
        $fileName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (move_uploaded_file($_FILES['cours_image']['tmp_name'], $uploadDir . $fileName)) {
            return $fileName;
        }
        return '';
    }

    private function uploadCoursFichiers(): array
    {
        if (!isset($_FILES['cours_fichiers']) || empty($_FILES['cours_fichiers']['name'][0])) return [];
        $uploadDir = 'public/uploads/cours/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $allowed = [
            'application/pdf','image/jpeg','image/png',
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

    // ── CRUD CERTIFICATS ───────────────────────────────────────

    public function storeCertificat(): void
    {
        $_SESSION['user'] = ['role' => 'admin', 'nom' => 'Test', 'prenom' => 'Admin'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titreCours = $_POST['titre_cours'] ?? '';
            $fileName   = $this->uploadFile();
            $this->model('Certificat')->addCertificat(
                $_POST['nom_certificat'], $_POST['date_obtention'],
                $_POST['organisation'], $fileName, $titreCours
            );
            header('Location: /UniServe/backofficeDocuments/index');
            exit;
        }
    }

    public function editCertificat(): void
    {
        $_SESSION['user'] = ['role' => 'admin', 'nom' => 'Test', 'prenom' => 'Admin'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id         = (int) ($_POST['id'] ?? 0);
            $titreCours = $_POST['titre_cours'] ?? '';
            $fileName   = '';
            if (isset($_FILES['certif_file']) && $_FILES['certif_file']['error'] === 0) {
                $fileName = $this->uploadFile();
            }
            $this->model('Certificat')->updateCertificat(
                $id, $_POST['nom_certificat'], $_POST['date_obtention'],
                $_POST['organisation'], $fileName, $titreCours
            );
            header('Location: /UniServe/backofficeDocuments/index');
            exit;
        }
    }

    public function deleteCertificat(): void
    {
        $_SESSION['user'] = ['role' => 'admin', 'nom' => 'Test', 'prenom' => 'Admin'];
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) $this->model('Certificat')->deleteCertificat($id);
        header('Location: /UniServe/backofficeDocuments/index');
        exit;
    }

    private function uploadFile(): string
    {
        if (isset($_FILES['certif_file']) && $_FILES['certif_file']['error'] === 0) {
            $uploadDir = 'public/uploads/certificats/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
            $fileType     = mime_content_type($_FILES['certif_file']['tmp_name']);
            if (!in_array($fileType, $allowedTypes, true)) return '';
            if ($_FILES['certif_file']['size'] > 5 * 1024 * 1024) return '';
            $fileName   = time() . '_' . bin2hex(random_bytes(4)) . '_' . basename($_FILES['certif_file']['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['certif_file']['tmp_name'], $targetPath)) return $fileName;
        }
        return '';
    }
}