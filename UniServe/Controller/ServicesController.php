<?php
declare(strict_types=1);

require_once __DIR__ . '/../Model/Service.php';

class ServicesController extends Controller
{
    private Service $serviceModel;

    public function __construct()
    {
        $this->serviceModel = new Service();
    }

    public function landing(): void
    {
        $this->frontoffice();
    }

    public function index(): void
    {
        $this->frontoffice();
    }

    public function backoffice(): void
    {
        $_SESSION['user'] = ['id' => 1, 'nom' => 'Simulé', 'prenom' => 'Admin', 'role' => 'admin'];
        $services = $this->serviceModel->getAll();
        $this->render('services/backoffice', ['title' => 'Gestion des Services (Backoffice)', 'services' => $services]);
    }

    public function frontoffice(): void
    {
        $_SESSION['user'] = ['id' => 2, 'nom' => 'Simulé', 'prenom' => 'Etudiant', 'role' => 'etudiant'];
        $services = $this->serviceModel->getAll();
        $this->render('services/frontoffice', ['title' => 'Liste des Services', 'services' => $services]);
    }

    public function create(): void
    {
        $_SESSION['user'] = ['id' => 1, 'nom' => 'Simulé', 'prenom' => 'Admin', 'role' => 'admin'];
        $this->render('services/create', ['title' => 'Créer un service']);
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $actif = isset($_POST['actif']) ? 1 : 0;
            if ($nom !== '' && $description !== '') {
                $this->serviceModel->create(['nom' => $nom, 'description' => $description, 'actif' => $actif]);
            }
        }
        $this->redirect('/demandes/backoffice');
    }

    public function edit(string $id): void
    {
        $_SESSION['user'] = ['id' => 1, 'nom' => 'Simulé', 'prenom' => 'Admin', 'role' => 'admin'];
        $service = $this->serviceModel->getById((int)$id);
        if (!$service) { $this->redirect('/demandes/backoffice'); }
        $this->render('services/edit', ['title' => 'Modifier le service', 'service' => $service]);
    }

    public function update(string $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $actif = isset($_POST['actif']) ? 1 : 0;
            if ($nom !== '' && $description !== '') {
                $this->serviceModel->update((int)$id, ['nom' => $nom, 'description' => $description, 'actif' => $actif]);
            }
        }
        $this->redirect('/demandes/backoffice');
    }

    public function delete(string $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->serviceModel->delete((int)$id);
        }
        $this->redirect('/demandes/backoffice');
    }
}
