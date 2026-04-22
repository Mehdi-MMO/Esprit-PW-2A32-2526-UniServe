<?php

class RendezvousController extends Controller {

    private $db;
    private $bureauModel;
    private $rdvModel;

    public function __construct() {
        $database          = new Database();
        $this->db          = $database->getConnection();
        $this->bureauModel = new BureauModel($this->db);
        $this->rdvModel    = new RendezVousModel($this->db);
    }

    // Force le layout frontoffice
    private function setFrontLayout(): void {
        $_SESSION['user']['role'] = 'etudiant';
    }

    // Force le layout backoffice
    private function setBackLayout(): void {
        $_SESSION['user']['role'] = 'staff';
    }

    // -------------------------------------------------------
    // FRONT OFFICE
    // -------------------------------------------------------

    public function index(): void {
        $this->setFrontLayout();
        $stmtBureaux = $this->bureauModel->getAll();
        $stmt        = $this->rdvModel->getAll();
        $success     = !empty($_GET['success']);
        $this->render('rendez-vous/front/index', compact('stmtBureaux', 'stmt', 'success'));
    }

    public function bookForm(): void {
        $this->setFrontLayout();
        $errors      = [];
        $stmtBureaux = $this->bureauModel->getAll();
        $this->render('rendez-vous/front/book', compact('errors', 'stmtBureaux'));
    }

    public function storeBooking(): void {
        $this->setFrontLayout();
        $errors      = [];
        $stmtBureaux = $this->bureauModel->getAll();

        $nom = trim($_POST['nom_etudiant'] ?? '');
        if ($nom === '') {
            $errors['nom_etudiant'] = "Le nom de l'étudiant est obligatoire.";
        } elseif (strlen($nom) < 2) {
            $errors['nom_etudiant'] = "Le nom doit contenir au moins 2 caractères.";
        }

        $id_bureau = $_POST['id_bureau'] ?? '';
        if ($id_bureau === '') {
            $errors['id_bureau'] = "Veuillez sélectionner un bureau.";
        } elseif (!is_numeric($id_bureau)) {
            $errors['id_bureau'] = "Bureau invalide.";
        }

        $objet = trim($_POST['objet'] ?? '');
        if ($objet === '') {
            $errors['objet'] = "Le sujet est obligatoire.";
        }

        $date_rdv = trim($_POST['date_rdv'] ?? '');
        if ($date_rdv === '') {
            $errors['date_rdv'] = "La date est obligatoire.";
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_rdv)) {
            $errors['date_rdv'] = "Format requis : AAAA-MM-JJ (ex : 2026-06-15).";
        } else {
            list($y, $m, $d) = explode('-', $date_rdv);
            if (!checkdate((int)$m, (int)$d, (int)$y)) {
                $errors['date_rdv'] = "La date saisie n'est pas valide.";
            }
        }

        $heure_rdv = trim($_POST['heure_rdv'] ?? '');
        if ($heure_rdv === '') {
            $errors['heure_rdv'] = "L'heure est obligatoire.";
        } elseif (!preg_match('/^\d{2}:\d{2}$/', $heure_rdv)) {
            $errors['heure_rdv'] = "Format requis : HH:MM (ex : 09:30).";
        } else {
            list($h, $min) = explode(':', $heure_rdv);
            if ((int)$h < 0 || (int)$h > 23 || (int)$min < 0 || (int)$min > 59) {
                $errors['heure_rdv'] = "Heure invalide (00:00 – 23:59).";
            }
        }

        if (empty($errors)) {
            $this->rdvModel->nom_etudiant = htmlspecialchars(strip_tags($nom));
            $this->rdvModel->id_bureau    = (int)$id_bureau;
            $this->rdvModel->objet        = htmlspecialchars(strip_tags($objet));
            $this->rdvModel->date_rdv     = $date_rdv;
            $this->rdvModel->heure_rdv    = $heure_rdv;

            if ($this->rdvModel->create()) {
                $this->redirect('index.php?success=1');
            } else {
                $errors['general'] = "Une erreur est survenue. Veuillez réessayer.";
            }
        }

        $this->render('rendez-vous/front/book', compact('errors', 'stmtBureaux'));
    }

    // -------------------------------------------------------
    // BACK OFFICE — Vue combinée (Rendez-vous + Bureaux)
    // -------------------------------------------------------

    public function list(): void {
        $this->setBackLayout();
        $stmt        = $this->rdvModel->getAll();
        $stmtBureaux = $this->bureauModel->getAll();
        $error       = $_GET['error']   ?? null;
        $success     = $_GET['success'] ?? null;
        $count       = isset($_GET['count']) ? (int)$_GET['count'] : 0;
        $this->render('rendez-vous/back/combined', compact('stmt', 'stmtBureaux', 'error', 'success', 'count'));
    }

    // -------------------------------------------------------
    // BACK OFFICE — Changer statut RDV
    // -------------------------------------------------------

    public function updateStatus(): void {
        if (isset($_GET['id']) && isset($_GET['status'])) {
            $allowed = ['confirmed', 'cancelled', 'pending'];
            if (in_array($_GET['status'], $allowed, true)) {
                $this->rdvModel->updateStatus((int)$_GET['id'], $_GET['status']);
            }
        }
        $this->redirect('index.php?page=back&module=appointments&tab=appointments');
    }

    // -------------------------------------------------------
    // BACK OFFICE — Bureaux
    // -------------------------------------------------------

    public function bureaux(): void {
        $this->redirect('index.php?page=back&module=appointments&tab=offices');
    }

    public function createBureau(): void {
        $this->setBackLayout();
        $this->render('rendez-vous/back/bureaux/create', []);
    }

    public function storeBureau(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom          = trim($_POST['nom'] ?? '');
            $localisation = trim($_POST['localisation'] ?? '');

            if ($nom === '' || $localisation === '') {
                die("Erreur : veuillez remplir tous les champs obligatoires.");
            }

            $this->bureauModel->nom          = htmlspecialchars(strip_tags($nom));
            $this->bureauModel->localisation = htmlspecialchars(strip_tags($localisation));
            $this->bureauModel->responsable  = htmlspecialchars(strip_tags($_POST['responsable'] ?? ''));

            if ($this->bureauModel->create()) {
                $this->redirect('index.php?page=back&module=appointments&tab=offices&success=created');
            }
        }
    }

    public function editBureau(): void {
        if (isset($_GET['id'])) {
            $id     = (int)$_GET['id'];
            $bureau = $this->bureauModel->getOne($id);
            if ($bureau) {
                $this->setBackLayout();
                $this->render('rendez-vous/back/bureaux/edit', ['bureau' => $bureau]);
            } else {
                $this->redirect('index.php?page=back&module=appointments&tab=offices&error=notfound');
            }
        } else {
            $this->redirect('index.php?page=back&module=appointments&tab=offices');
        }
    }

    public function updateBureau(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->bureauModel->id           = (int)$_POST['id'];
            $this->bureauModel->nom          = htmlspecialchars(strip_tags($_POST['nom'] ?? ''));
            $this->bureauModel->localisation = htmlspecialchars(strip_tags($_POST['localisation'] ?? ''));
            $this->bureauModel->responsable  = htmlspecialchars(strip_tags($_POST['responsable'] ?? ''));

            if ($this->bureauModel->update()) {
                // Redirection vers la vue combinée onglet bureaux avec message succès
                $this->redirect('index.php?page=back&module=appointments&tab=offices&success=updated');
            }
        }
    }

    public function deleteBureau(): void {
        if (isset($_GET['id'])) {
            $id    = (int)$_GET['id'];
            $count = $this->bureauModel->countRendezVous($id);

            if ($count > 0) {
                $this->redirect('index.php?page=back&module=appointments&tab=offices&error=has_rdv&count=' . $count);
            }

            if ($this->bureauModel->delete($id)) {
                $this->redirect('index.php?page=back&module=appointments&tab=offices&success=deleted');
            }
        }
    }
}
?>