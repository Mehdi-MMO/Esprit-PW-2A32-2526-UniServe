<?php
declare(strict_types=1);

class BackofficeDocumentsController extends Controller
{
    public function index(): void
    {
        // TEMPORAIRE — à supprimer après test
        $_SESSION['user'] = ['role' => 'admin', 'nom' => 'Test', 'prenom' => 'Admin'];

        $demModel = $this->model('DemandeCertification');

        $this->render('documents/backoffice', [
            'title'                 => 'Backoffice — Gestion des Documents',
            'cours'                 => $this->model('Cours')->getAllCours(),
            'certificats'           => $this->model('Certificat')->getAllCertificats(),
            'demandes'              => $demModel->getAll(),
            'nb_en_attente'         => $demModel->countByStatut('en_attente'),
        ]);
    }

    // ── Accept / Refuse a certification request ──────────────────

    public function accepterDemande(): void
    {
        $_SESSION['user'] = ['role' => 'admin', 'nom' => 'Test', 'prenom' => 'Admin'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id          = (int) ($_POST['id'] ?? 0);
            $commentaire = trim($_POST['commentaire'] ?? '');
            if ($id > 0) {
                $this->model('DemandeCertification')->updateStatut($id, 'accepte', $commentaire);
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
            }
        }
        header('Location: /UniServe/backofficeDocuments/index#demandes');
        exit;
    }

    // ── Cours CRUD ────────────────────────────────────────────────

    public function storeCours(): void
    {
        $_SESSION['user'] = ['role' => 'admin', 'nom' => 'Test', 'prenom' => 'Admin'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model('Cours')->createCours($_POST);
            header('Location: /UniServe/backofficeDocuments/index');
            exit;
        }
    }

    public function editCours(): void
    {
        $_SESSION['user'] = ['role' => 'admin', 'nom' => 'Test', 'prenom' => 'Admin'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $oldTitre = $_POST['old_titre'] ?? '';
            $this->model('Cours')->updateCours($_POST, $oldTitre);
            header('Location: /UniServe/backofficeDocuments/index');
            exit;
        }
    }

    public function deleteCours(): void
    {
        $_SESSION['user'] = ['role' => 'admin', 'nom' => 'Test', 'prenom' => 'Admin'];
        $titre = trim($_POST['titre'] ?? '');
        if (!empty($titre)) {
            $this->model('Cours')->deleteCours($titre);
        }
        header('Location: /UniServe/backofficeDocuments/index');
        exit;
    }

    // ── Certificats CRUD ──────────────────────────────────────────

    public function storeCertificat(): void
    {
        $_SESSION['user'] = ['role' => 'admin', 'nom' => 'Test', 'prenom' => 'Admin'];
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
                $id,
                $_POST['nom_certificat'],
                $_POST['date_obtention'],
                $_POST['organisation'],
                $fileName,
                $titreCours
            );
            header('Location: /UniServe/backofficeDocuments/index');
            exit;
        }
    }

    public function deleteCertificat(): void
    {
        $_SESSION['user'] = ['role' => 'admin', 'nom' => 'Test', 'prenom' => 'Admin'];
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model('Certificat')->deleteCertificat($id);
        }
        header('Location: /UniServe/backofficeDocuments/index');
        exit;
    }

    // ── Upload helper ─────────────────────────────────────────────

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