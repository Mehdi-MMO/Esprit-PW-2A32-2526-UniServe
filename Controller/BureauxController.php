<?php

declare(strict_types=1);

require_once __DIR__ . '/../Model/Bureau.php';

class BureauxController extends Controller
{
    /**
     * Staff CRUD for physical service desks (bureaux) students book via rendez-vous.
     * Default route `/bureaux` resolves to {@see landing} (App front controller convention).
     */
    public function landing(): void
    {
        $this->index();
    }

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

    /**
     * @return array{nom: string, localisation: string, type_service: string, actif: int}|null
     */
    private function parsePayload(array $source): ?array
    {
        $nom = trim((string) ($source['nom'] ?? ''));
        $localisation = trim((string) ($source['localisation'] ?? ''));
        $typeService = trim((string) ($source['type_service'] ?? ''));
        $actif = isset($source['actif']) && (string) $source['actif'] === '1' ? 1 : 0;

        if ($nom === '' || $typeService === '') {
            return null;
        }

        return [
            'nom' => $nom,
            'localisation' => $localisation,
            'type_service' => $typeService,
            'actif' => $actif,
        ];
    }

    public function index(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        $this->redirect('/rendezvous?tab=bureaux');
    }

    public function delete(int|string $id): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->isPost()) {
            $this->redirect('/rendezvous?tab=bureaux');
            return;
        }

        $bid = (int) $id;
        $bureauModel = new Bureau();
        if ($bureauModel->countRendezVousForBureau($bid) > 0) {
            $this->setFlash('error', 'Impossible de supprimer ce bureau : des rendez-vous y sont liés.');
            $this->redirect('/rendezvous?tab=bureaux');
            return;
        }

        if (!$bureauModel->deleteById($bid)) {
            $this->setFlash('error', 'Suppression impossible.');
            $this->redirect('/rendezvous?tab=bureaux');
            return;
        }

        $this->setFlash('success', 'Bureau supprimé.');
        $this->redirect('/rendezvous?tab=bureaux');
    }

    public function createForm(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        $this->render('backoffice/bureaux/create', [
            'title' => 'Nouveau bureau',
            'old' => [
                'nom' => '',
                'localisation' => '',
                'type_service' => '',
                'actif' => '1',
            ],
            'error' => null,
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->isPost()) {
            $this->redirect('/bureaux/createForm');
            return;
        }

        $payload = $this->parsePayload($_POST);
        if ($payload === null) {
            $this->render('backoffice/bureaux/create', [
                'title' => 'Nouveau bureau',
                'old' => [
                    'nom' => (string) ($_POST['nom'] ?? ''),
                    'localisation' => (string) ($_POST['localisation'] ?? ''),
                    'type_service' => (string) ($_POST['type_service'] ?? ''),
                    'actif' => (string) ($_POST['actif'] ?? '0'),
                ],
                'error' => 'Nom et type de service sont obligatoires.',
            ]);
            return;
        }

        (new Bureau())->create($payload);
        $this->setFlash('success', 'Bureau créé.');
        $this->redirect('/rendezvous?tab=bureaux');
    }

    public function editForm(int|string $id): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        $bid = (int) $id;
        $row = (new Bureau())->findById($bid);
        if ($row === null) {
            $this->setFlash('error', 'Bureau introuvable.');
            $this->redirect('/rendezvous?tab=bureaux');
            return;
        }

        $this->render('backoffice/bureaux/edit', [
            'title' => 'Modifier le bureau',
            'bureau' => $row,
            'error' => null,
        ]);
    }

    public function update(int|string $id): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        $bid = (int) $id;
        $bureauModel = new Bureau();
        $row = $bureauModel->findById($bid);
        if ($row === null) {
            $this->setFlash('error', 'Bureau introuvable.');
            $this->redirect('/rendezvous?tab=bureaux');
            return;
        }

        if (!$this->isPost()) {
            $this->redirect('/bureaux/editForm/' . $bid);
            return;
        }

        $payload = $this->parsePayload($_POST);
        if ($payload === null) {
            $this->render('backoffice/bureaux/edit', [
                'title' => 'Modifier le bureau',
                'bureau' => array_merge($row, [
                    'nom' => (string) ($_POST['nom'] ?? ''),
                    'localisation' => (string) ($_POST['localisation'] ?? ''),
                    'type_service' => (string) ($_POST['type_service'] ?? ''),
                    'actif' => isset($_POST['actif']) && (string) $_POST['actif'] === '1' ? 1 : 0,
                ]),
                'error' => 'Nom et type de service sont obligatoires.',
            ]);
            return;
        }

        $bureauModel->update($bid, $payload);
        $this->setFlash('success', 'Bureau mis à jour.');
        $this->redirect('/rendezvous?tab=bureaux');
    }
}
