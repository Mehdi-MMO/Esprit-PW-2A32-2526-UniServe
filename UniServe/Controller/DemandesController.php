<?php
declare(strict_types=1);

require_once __DIR__ . '/../Model/Demande.php';
require_once __DIR__ . '/../Model/Service.php';

class DemandesController extends Controller
{
    private Demande $demandeModel;
    private Service $serviceModel;

    public function __construct()
    {
        $this->demandeModel = new Demande();
        $this->serviceModel = new Service();
        
        // Ensure dummy users exist to avoid Foreign Key errors
        $db = Database::connect();
        $db->exec("INSERT IGNORE INTO utilisateurs (id, nom, prenom, email, mot_de_passe_hash, role) VALUES 
            (1, 'Admin', 'Test', 'admin@test.com', 'hash', 'admin'), 
            (2, 'Etudiant', 'Test', 'etu@test.com', 'hash', 'etudiant')");
    }

    public function landing(): void { $this->frontoffice(); }
    public function index(): void { $this->frontoffice(); }

    public function frontoffice(): void
    {
        $_SESSION['user'] = ['id' => 2, 'nom' => 'Simulé', 'prenom' => 'Etudiant', 'role' => 'etudiant'];
        $demandes = $this->demandeModel->getAllByUser(2);
        $services = $this->serviceModel->getAll();
        
        $stats = [
            'total' => count($demandes),
            'attente' => count(array_filter($demandes, fn($d) => $d['statut'] === 'en_attente')),
            'traitees' => count(array_filter($demandes, fn($d) => $d['statut'] === 'traite')),
            'rejetees' => count(array_filter($demandes, fn($d) => $d['statut'] === 'rejete')),
            'services' => count(array_filter($services, fn($s) => $s['actif'] == 1))
        ];

        $this->render('demandes/frontoffice', ['title' => 'Mes Demandes', 'demandes' => $demandes, 'services' => $services, 'stats' => $stats]);
    }

    public function backoffice(): void
    {
        $_SESSION['user'] = ['id' => 1, 'nom' => 'Simulé', 'prenom' => 'Admin', 'role' => 'admin'];
        $demandes = $this->demandeModel->getAll();
        $services = $this->serviceModel->getAll();

        $stats = [
            'total' => count($demandes),
            'attente' => count(array_filter($demandes, fn($d) => $d['statut'] === 'en_attente')),
            'traitees' => count(array_filter($demandes, fn($d) => $d['statut'] === 'traite')),
            'rejetees' => count(array_filter($demandes, fn($d) => $d['statut'] === 'rejete')),
            'services' => count(array_filter($services, fn($s) => $s['actif'] == 1))
        ];

        $this->render('demandes/backoffice', ['title' => 'Toutes les demandes', 'demandes' => $demandes, 'services' => $services, 'stats' => $stats]);
    }

    public function create(): void
    {
        $_SESSION['user'] = ['id' => 2, 'nom' => 'Simulé', 'prenom' => 'Etudiant', 'role' => 'etudiant'];
        $services = array_filter($this->serviceModel->getAll(), fn($s) => $s['actif'] == 1);
        $this->render('demandes/create', ['title' => 'Nouvelle Demande', 'services' => $services]);
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $service_id = (int)($_POST['service_id'] ?? 0);
            $titre = trim($_POST['titre'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telephone = trim($_POST['telephone'] ?? '');
            if ($service_id > 0 && $titre !== '' && $description !== '' && $email !== '' && preg_match('/^[0-9]{8}$/', $telephone)) {
                $this->demandeModel->create([
                    'utilisateur_id' => 2, 
                    'service_id' => $service_id, 
                    'titre' => $titre, 
                    'description' => $description,
                    'email' => $email,
                    'telephone' => $telephone
                ]);
            }
        }
        $this->redirect('/demandes/frontoffice');
    }

    public function edit(string $id): void
    {
        $_SESSION['user'] = ['id' => 2, 'nom' => 'Simulé', 'prenom' => 'Etudiant', 'role' => 'etudiant'];
        $demande = $this->demandeModel->getById((int)$id);
        if (!$demande || $demande['utilisateur_id'] != 2 || $demande['statut'] !== 'en_attente') {
            $this->redirect('/demandes/frontoffice');
        }
        $services = array_filter($this->serviceModel->getAll(), fn($s) => $s['actif'] == 1);
        $this->render('demandes/edit', ['title' => 'Modifier la Demande', 'demande' => $demande, 'services' => $services]);
    }

    public function update(string $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $service_id = (int)($_POST['service_id'] ?? 0);
            $titre = trim($_POST['titre'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telephone = trim($_POST['telephone'] ?? '');
            $demande = $this->demandeModel->getById((int)$id);
            if ($demande && $demande['utilisateur_id'] == 2 && $demande['statut'] === 'en_attente') {
                if ($service_id > 0 && $titre !== '' && $description !== '' && $email !== '' && preg_match('/^[0-9]{8}$/', $telephone)) {
                    $this->demandeModel->update((int)$id, [
                        'service_id' => $service_id, 
                        'titre' => $titre, 
                        'description' => $description,
                        'email' => $email,
                        'telephone' => $telephone
                    ]);
                }
            }
        }
        $this->redirect('/demandes/frontoffice');
    }

    public function delete(string $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $demande = $this->demandeModel->getById((int)$id);
            if ($demande && $demande['utilisateur_id'] == 2) {
                $this->demandeModel->delete((int)$id);
            }
        }
        $this->redirect('/demandes/frontoffice');
    }

    public function updateStatut(string $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $statut = $_POST['statut'] ?? '';
            $valid_statuts = ['en_attente', 'en_cours', 'traite', 'rejete'];
            if (in_array($statut, $valid_statuts)) {
                $this->demandeModel->updateStatut((int)$id, $statut);
            }
        }
        $this->redirect('/demandes/backoffice');
    }

    public function delete_back(string $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->demandeModel->delete((int)$id);
        }
        $this->redirect('/demandes/backoffice');
    }
}
