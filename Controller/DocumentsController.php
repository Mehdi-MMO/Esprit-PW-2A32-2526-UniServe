<?php
declare(strict_types=1);

class DocumentsController extends Controller
{
    public function index(): void
    {
        // TEMPORAIRE — à supprimer après test
        $_SESSION['user'] = ['role' => 'etudiant', 'nom' => 'Test', 'prenom' => 'User'];

        $this->render('documents/index', [
            'title'        => 'Gestion des Documents Académiques',
            'cours'        => $this->model('Cours')->getAllCours(),
            'certificats'  => $this->model('Certificat')->getAllCertificats(),
        ]);
    }

    // ================= SECTION COURS =================

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

    // ================= DEMANDE DE CERTIFICATION (NEW) =================

    /**
     * POST — student submits a certification exam request
     */
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
            'organisation'   => trim($_POST['organisation']   ?? ''),
            'date_souhaitee' => trim($_POST['date_obtention'] ?? ''),
            'heure_preferee' => trim($_POST['heure_preferee'] ?? ''),
            'notes'          => trim($_POST['notes']          ?? ''),
            'fichier_path'   => $fileName,
        ]);

        header('Location: /UniServe/documents/index?success=1');
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
            if (!in_array($fileType, $allowedTypes)) {
                return '';
            }
            if ($_FILES['certif_file']['size'] > 5 * 1024 * 1024) {
                return '';
            }
            $fileName   = time() . '_' . bin2hex(random_bytes(4)) . '_' . basename($_FILES['certif_file']['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['certif_file']['tmp_name'], $targetPath)) {
                return $fileName;
            }
        }
        return '';
    }
}