<?php

declare(strict_types=1);

require_once __DIR__ . '/../Model/CategorieService.php';

/**
 * Admin / catalogue "services" = rows in categories_service (THEMODULES DEMANDE parity).
 */
class ServicesController extends Controller
{
    private function isPost(): bool
    {
        return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST';
    }

    private function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    private function popFlash(): ?array
    {
        if (!isset($_SESSION['flash'])) {
            return null;
        }

        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);

        return is_array($f) ? $f : null;
    }

    private function currentRole(): string
    {
        return (string) ($_SESSION['user']['role'] ?? '');
    }

    private function isStaffOrAdmin(): bool
    {
        return in_array($this->currentRole(), ['staff', 'admin'], true);
    }

    public function landing(): void
    {
        $this->index();
    }

    public function index(): void
    {
        $this->requireLogin();

        $catModel = new CategorieService();

        if ($this->isStaffOrAdmin()) {
            $this->render('backoffice/services/index', [
                'title' => 'Services (catégories)',
                'services' => $catModel->findAll(),
                'flash' => $this->popFlash(),
                'is_admin_view' => true,
            ]);
            return;
        }

        $this->render('frontoffice/services/index', [
            'title' => 'Types de demandes de service',
            'services' => $catModel->findAllActive(),
        ]);
    }

    public function createForm(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        $this->render('backoffice/services/create', [
            'title' => 'Nouveau service',
            'old' => ['nom' => '', 'description' => '', 'actif' => '1'],
            'error' => null,
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->isPost()) {
            $this->redirect('/services/createForm');
            return;
        }

        $nom = trim((string) ($_POST['nom'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $actif = isset($_POST['actif']) && (string) $_POST['actif'] === '1' ? 1 : 0;

        if ($nom === '' || $description === '' || strlen($description) < 10) {
            $this->render('backoffice/services/create', [
                'title' => 'Nouveau service',
                'old' => [
                    'nom' => (string) ($_POST['nom'] ?? ''),
                    'description' => (string) ($_POST['description'] ?? ''),
                    'actif' => isset($_POST['actif']) ? '1' : '0',
                ],
                'error' => 'Nom et description (au moins 10 caractères) sont obligatoires.',
            ]);
            return;
        }

        $id = (new CategorieService())->create($nom, $description, $actif);
        if ($id === false) {
            $this->setFlash('danger', 'Création impossible.');
            $this->redirect('/services/createForm');
            return;
        }

        $this->setFlash('success', 'Service créé.');
        $this->redirect('/services');
    }

    public function editForm(int|string $id): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        $row = (new CategorieService())->findById((int) $id);
        if ($row === null) {
            $this->setFlash('danger', 'Service introuvable.');
            $this->redirect('/services');
            return;
        }

        $this->render('backoffice/services/edit', [
            'title' => 'Modifier le service',
            'service' => $row,
            'error' => null,
        ]);
    }

    public function update(int|string $id): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        $sid = (int) $id;
        $catModel = new CategorieService();
        $row = $catModel->findById($sid);
        if ($row === null) {
            $this->setFlash('danger', 'Service introuvable.');
            $this->redirect('/services');
            return;
        }

        if (!$this->isPost()) {
            $this->redirect('/services/editForm/' . $sid);
            return;
        }

        $nom = trim((string) ($_POST['nom'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $actif = isset($_POST['actif']) && (string) $_POST['actif'] === '1' ? 1 : 0;

        if ($nom === '' || $description === '' || strlen($description) < 10) {
            $this->render('backoffice/services/edit', [
                'title' => 'Modifier le service',
                'service' => array_merge($row, [
                    'nom' => (string) ($_POST['nom'] ?? ''),
                    'description' => (string) ($_POST['description'] ?? ''),
                    'actif' => $actif,
                ]),
                'error' => 'Nom et description (au moins 10 caractères) sont obligatoires.',
            ]);
            return;
        }

        $ok = $catModel->update($sid, $nom, $description, $actif);
        $this->setFlash($ok ? 'success' : 'danger', $ok ? 'Service mis à jour.' : 'Mise à jour impossible.');
        $this->redirect('/services');
    }

    public function delete(int|string $id): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->isPost()) {
            $this->redirect('/services');
            return;
        }

        $result = (new CategorieService())->deleteOrDeactivate((int) $id);
        $this->setFlash($result['ok'] ? 'success' : 'danger', $result['message']);
        $this->redirect('/services');
    }
}
