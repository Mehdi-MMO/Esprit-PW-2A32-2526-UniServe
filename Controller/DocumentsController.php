<?php

declare(strict_types=1);

/**
 * Demandes de documents académiques (attestations, etc.).
 *
 * Hors périmètre : le pipeline « certification / quiz » DOCAC est exposé sous `/certifications`
 * (voir CertificationsController). Ce contrôleur couvre les demandes de documents académiques classiques.
 */
class DocumentsController extends Controller
{
    private function isPost(): bool
    {
        return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST';
    }

    private function currentUserId(): int
    {
        return (int) ($_SESSION['user']['id'] ?? 0);
    }

    private function currentRole(): string
    {
        return (string) ($_SESSION['user']['role'] ?? '');
    }

    private function isEtudiant(): bool
    {
        return $this->currentRole() === 'etudiant';
    }

    private function isStaffOrAdmin(): bool
    {
        return in_array($this->currentRole(), ['staff', 'admin'], true);
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return array{total: int, en_attente: int, en_validation: int, valide: int, rejete: int, livre: int}
     */
    private function statsFromRows(array $rows): array
    {
        $counts = [
            'en_attente' => 0,
            'en_validation' => 0,
            'valide' => 0,
            'rejete' => 0,
            'livre' => 0,
        ];
        foreach ($rows as $r) {
            $s = (string) ($r['statut'] ?? '');
            if (isset($counts[$s])) {
                $counts[$s]++;
            }
        }

        return array_merge(['total' => count($rows)], $counts);
    }

    /**
     * @return array<string, string>
     */
    private function statutLabels(): array
    {
        return [
            'en_attente' => 'En attente',
            'en_validation' => 'En validation',
            'valide' => 'Validé',
            'rejete' => 'Rejeté',
            'livre' => 'Livré',
        ];
    }

    private function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    public function landing(): void
    {
        $this->index();
    }

    public function index(): void
    {
        $this->requireLogin();

        $demModel = new DemandeDocument();

        if ($this->isStaffOrAdmin()) {
            $statut = trim((string) ($_GET['statut'] ?? ''));
            $q = trim((string) ($_GET['q'] ?? ''));
            $filters = [];
            if ($statut !== '') {
                $filters['statut'] = $statut;
            }
            if ($q !== '') {
                $filters['q'] = $q;
            }

            $demandes = $demModel->findForAdmin($filters);
            $stats = $this->statsFromRows($demandes);

            $this->render('backoffice/documents/index', [
                'title' => 'Documents académiques',
                'demandes' => $demandes,
                'stats' => $stats,
                'statut_filter' => $statut,
                'q' => $q,
                'statut_labels' => $this->statutLabels(),
            ]);

            return;
        }

        if ($this->isEtudiant()) {
            $uid = $this->currentUserId();
            $demandes = $demModel->findForStudent($uid);
            $stats = $this->statsFromRows($demandes);

            $this->render('frontoffice/documents/index', [
                'title' => 'Mes documents académiques',
                'demandes' => $demandes,
                'stats' => $stats,
                'statut_labels' => $this->statutLabels(),
                'teacher_notice' => false,
            ]);

            return;
        }

        $this->render('frontoffice/documents/index', [
            'title' => 'Documents académiques',
            'demandes' => [],
            'stats' => [
                'total' => 0,
                'en_attente' => 0,
                'en_validation' => 0,
                'valide' => 0,
                'rejete' => 0,
                'livre' => 0,
            ],
            'statut_labels' => $this->statutLabels(),
            'teacher_notice' => true,
        ]);
    }

    public function createForm(): void
    {
        $this->requireLogin();
        if (!$this->isEtudiant()) {
            $this->redirectByUserRole($this->currentRole());

            return;
        }

        $types = (new TypeDocument())->findAllActive();

        $this->render('frontoffice/documents/create', [
            'title' => 'Nouvelle demande de document',
            'types' => $types,
            'old' => ['type_document_id' => ''],
            'error' => null,
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();
        if (!$this->isEtudiant()) {
            $this->redirectByUserRole($this->currentRole());

            return;
        }

        if (!$this->isPost()) {
            $this->redirect('/documents/createForm');

            return;
        }

        $typeId = (int) ($_POST['type_document_id'] ?? 0);
        $typeModel = new TypeDocument();
        $type = $typeModel->findById($typeId);

        if ($type === null || (int) ($type['actif'] ?? 0) !== 1) {
            $this->render('frontoffice/documents/create', [
                'title' => 'Nouvelle demande de document',
                'types' => $typeModel->findAllActive(),
                'old' => ['type_document_id' => (string) $typeId],
                'error' => 'Type de document invalide ou indisponible.',
            ]);

            return;
        }

        $demModel = new DemandeDocument();
        $newId = $demModel->create($this->currentUserId(), $typeId);
        if ($newId === false) {
            $this->render('frontoffice/documents/create', [
                'title' => 'Nouvelle demande de document',
                'types' => $typeModel->findAllActive(),
                'old' => ['type_document_id' => (string) $typeId],
                'error' => 'Enregistrement impossible. Réessayez.',
            ]);

            return;
        }

        $this->setFlash('success', 'Demande enregistrée.');
        $this->redirect('/documents');
    }

    public function updateStatut(int|string $id): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->isPost()) {
            $this->redirect('/documents');

            return;
        }

        $demId = (int) $id;
        $statut = trim((string) ($_POST['statut'] ?? ''));
        $note = trim((string) ($_POST['note_validation'] ?? ''));

        if (!in_array($statut, DemandeDocument::allowedStatuts(), true)) {
            $this->setFlash('danger', 'Statut invalide.');
            $this->redirect('/documents');

            return;
        }

        $demModel = new DemandeDocument();
        $ok = $demModel->applyStatutChange($demId, $statut, $this->currentUserId(), $note !== '' ? $note : null);

        if (!$ok) {
            $this->setFlash('danger', 'Transition impossible (vérifiez le flux ou ajoutez un motif de rejet).');
            $this->redirect('/documents');

            return;
        }

        $this->setFlash('success', 'Demande mise à jour.');
        $this->redirect('/documents');
    }
}
