<?php
declare(strict_types=1);

class DocumentsController extends Controller
{
    public function index(): void
    {
        // TEMPORAIRE — à supprimer après mise en place de l'auth
        $_SESSION['user'] = ['role' => 'etudiant', 'nom' => 'Test', 'prenom' => 'User'];

        $demModel  = $this->model('DemandeCertification');
        $quizModel = $this->model('Quiz');
        $demandes  = $demModel->getAll();

        // Attache le quiz à chaque demande pour l'affichage de la zone "Quiz à passer"
        foreach ($demandes as &$d) {
            $quiz = $quizModel->getByDemandeId((int) $d['id']);
            $d['quiz'] = $quiz;
        }
        unset($d);

        $this->render('documents/index', [
            'title'       => 'Gestion des Documents Académiques',
            'cours'       => $this->model('Cours')->getAllCours(),
            'certificats' => $this->model('Certificat')->getAllCertificats(),
            'demandes'    => $demandes,
        ]);
    }

    // ================= SECTION COURS (étudiant ne fait que lire) =================

    public function storeCours(): void
    {
        $_SESSION['user'] = ['role' => 'etudiant', 'nom' => 'Test', 'prenom' => 'User'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model('Cours')->createCours($_POST);
            header('Location: /UniServe/documents/index');
            exit;
        }
    }

    public function editCours(): void
    {
        $_SESSION['user'] = ['role' => 'etudiant', 'nom' => 'Test', 'prenom' => 'User'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $oldTitre = $_POST['old_titre'] ?? '';
            $this->model('Cours')->updateCours($_POST, $oldTitre);
            header('Location: /UniServe/documents/index');
            exit;
        }
    }

    public function deleteCours(): void
    {
        $_SESSION['user'] = ['role' => 'etudiant', 'nom' => 'Test', 'prenom' => 'User'];
        $titre = trim($_POST['titre'] ?? '');
        if (!empty($titre)) {
            $this->model('Cours')->deleteCours($titre);
        }
        header('Location: /UniServe/documents/index');
        exit;
    }

    public function deleteCertificat(): void
    {
        $_SESSION['user'] = ['role' => 'etudiant', 'nom' => 'Test', 'prenom' => 'User'];
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model('Certificat')->deleteCertificat($id);
        }
        header('Location: /UniServe/documents/index');
        exit;
    }

    // ================= SECTION CERTIFICATS (upload) =================

    public function storeCertificat(): void
    {
        $_SESSION['user'] = ['role' => 'etudiant', 'nom' => 'Test', 'prenom' => 'User'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titreCours = $_POST['titre_cours'] ?? '';
            $fileName   = $this->uploadFile();

            $this->model('Certificat')->addCertificat(
                $_POST['nom_certificat'],
                $_POST['date_obtention'],
                $_POST['organisation'],
                $fileName,
                $titreCours
            );
            header('Location: /UniServe/documents/index');
            exit;
        }
    }

    public function editCertificat(): void
    {
        $_SESSION['user'] = ['role' => 'etudiant', 'nom' => 'Test', 'prenom' => 'User'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id         = (int) ($_POST['id'] ?? 0);
            $titreCours = $_POST['titre_cours'] ?? '';
            $fileName   = '';
            if (isset($_FILES['certif_file']) && $_FILES['certif_file']['error'] === 0) {
                $fileName = $this->uploadFile();
            }
            $this->model('Certificat')->updateCertificat(
                $id,
                $_POST['nom_certificat'],
                $_POST['date_obtention'],
                $_POST['organisation'],
                $fileName,
                $titreCours
            );
            header('Location: /UniServe/documents/index');
            exit;
        }
    }

    // ================= DEMANDE DE CERTIFICATION (passage de quiz) =================

    public function demanderCertification(): void
    {
        $_SESSION['user'] = ['role' => 'etudiant', 'nom' => 'Test', 'prenom' => 'User'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /UniServe/documents/index');
            exit;
        }

        $fileName = '';
        if (isset($_FILES['certif_file']) && $_FILES['certif_file']['error'] === 0) {
            $fileName = $this->uploadFile();
        }

        $this->model('DemandeCertification')->store([
            'nom_certificat' => trim($_POST['nom_certificat'] ?? ''),
            'titre_cours'    => trim($_POST['titre_cours']    ?? ''),
            'organisation'   => trim($_POST['organisation']   ?? 'UniServe'),
            'date_souhaitee' => trim($_POST['date_obtention'] ?? ''),
            'heure_preferee' => trim($_POST['heure_preferee'] ?? ''),
            'notes'          => trim($_POST['notes']          ?? ''),
            'fichier_path'   => $fileName,
        ]);

        header('Location: /UniServe/documents/index?success=1');
        exit;
    }

    // ================= ÉTUDIANT — PASSAGE DU QUIZ =================

    /**
     * L'étudiant soumet ses réponses au quiz.
     * Calcule le score, met à jour le quiz et la demande,
     * et stocke une notif flash pour la page d'accueil.
     */
    public function passerQuiz(): void
    {
        $_SESSION['user'] = ['role' => 'etudiant', 'nom' => 'Test', 'prenom' => 'User'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /UniServe/documents/index');
            exit;
        }

        $quizId = (int) ($_POST['quiz_id'] ?? 0);
        if ($quizId <= 0) {
            header('Location: /UniServe/documents/index');
            exit;
        }

        $quizModel = $this->model('Quiz');
        $quiz      = $quizModel->getById($quizId);

        if (!$quiz || $quiz['statut'] !== 'en_attente') {
            header('Location: /UniServe/documents/index');
            exit;
        }

        // Calcul du score
        $questions = $quiz['questions'] ?? [];
        $score     = 0;
        foreach ($questions as $i => $q) {
            $userAnswer = isset($_POST["answer_{$i}"]) ? (int) $_POST["answer_{$i}"] : -1;
            if ($userAnswer === (int) ($q['correct'] ?? -1)) {
                $score++;
            }
        }

        // Sauvegarde le score (le modèle décide accepte/refuse selon le seuil 3/5)
        $quizModel->submit($quizId, $score);

        // Met aussi à jour le statut de la demande de certification
        $statut = $score >= 3 ? 'accepte' : 'refuse';
        $this->model('DemandeCertification')->updateStatut(
            (int) $quiz['demande_id'],
            $statut,
            "Quiz passé — score : {$score}/5"
        );

        // Notif flash pour l'étudiant
        $_SESSION['notif_quiz'] = [
            'statut' => $statut,
            'score'  => $score,
            'cours'  => $quiz['cours_titre'] ?? '',
        ];

        header('Location: /UniServe/documents/index');
        exit;
    }

    // ================= UPLOAD HELPER =================

    private function uploadFile(): string
    {
        if (isset($_FILES['certif_file']) && $_FILES['certif_file']['error'] === 0) {
            $uploadDir = 'public/uploads/certificats/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
            $fileType     = mime_content_type($_FILES['certif_file']['tmp_name']);
            if (!in_array($fileType, $allowedTypes)) return '';
            if ($_FILES['certif_file']['size'] > 5 * 1024 * 1024) return '';

            $fileName   = time() . '_' . bin2hex(random_bytes(4)) . '_' . basename($_FILES['certif_file']['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['certif_file']['tmp_name'], $targetPath)) {
                return $fileName;
            }
        }
        return '';
    }
}