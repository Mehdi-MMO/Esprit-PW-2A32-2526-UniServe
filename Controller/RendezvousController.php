<?php

declare(strict_types=1);

require_once __DIR__ . '/../Model/Bureau.php';
require_once __DIR__ . '/../Model/RendezVous.php';

class RendezvousController extends Controller
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

    private function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return array{total: int, reserve: int, confirme: int, annule: int, termine: int}
     */
    private function statsFromRows(array $rows): array
    {
        $out = ['total' => count($rows), 'reserve' => 0, 'confirme' => 0, 'annule' => 0, 'termine' => 0];
        foreach ($rows as $r) {
            switch ((string) ($r['statut'] ?? '')) {
                case 'reserve':
                    $out['reserve']++;
                    break;
                case 'confirme':
                    $out['confirme']++;
                    break;
                case 'annule':
                    $out['annule']++;
                    break;
                case 'termine':
                    $out['termine']++;
                    break;
            }
        }

        return $out;
    }

    /**
     * @return array<string, string>
     */
    private function statutLabels(): array
    {
        return [
            'reserve' => 'Réservé',
            'confirme' => 'Confirmé',
            'annule' => 'Annulé',
            'termine' => 'Terminé',
        ];
    }

    private function normalizeDateTimeInput(string $value): ?string
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $trimmed);
        if ($date instanceof \DateTimeImmutable) {
            return $date->format('Y-m-d H:i:s');
        }

        $fallback = strtotime($trimmed);
        if ($fallback === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $fallback);
    }

    /**
     * @return array{bureau_id: int, motif: string, date_debut: string, date_fin: string}|null
     */
    private function parseRdvPayload(array $source): ?array
    {
        $bureauId = (int) ($source['bureau_id'] ?? 0);
        $motif = trim((string) ($source['motif'] ?? ''));
        $d1 = $this->normalizeDateTimeInput((string) ($source['date_debut'] ?? ''));
        $d2 = $this->normalizeDateTimeInput((string) ($source['date_fin'] ?? ''));

        if ($bureauId <= 0 || $d1 === null || $d2 === null) {
            return null;
        }

        if (strtotime($d2) <= strtotime($d1)) {
            return null;
        }

        return [
            'bureau_id' => $bureauId,
            'motif' => $motif,
            'date_debut' => $d1,
            'date_fin' => $d2,
        ];
    }

    public function landing(): void
    {
        $this->index();
    }

    public function index(): void
    {
        $this->requireLogin();

        $bureauModel = new Bureau();
        $rdvModel = new RendezVous();

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

            $rdvs = $rdvModel->findAllForAdmin($filters);
            $stats = $this->statsFromRows($rdvs);
            $stats['bureaux_actifs'] = count($bureauModel->findAllActive());

            $this->render('backoffice/rendezvous/index', [
                'title' => 'Rendez-vous',
                'rdvs' => $rdvs,
                'stats' => $stats,
                'statut_filter' => $statut,
                'q' => $q,
                'statut_labels' => $this->statutLabels(),
            ]);
            return;
        }

        if ($this->isEtudiant()) {
            $uid = $this->currentUserId();
            $rdvs = $rdvModel->findAllForStudent($uid);
            $stats = $this->statsFromRows($rdvs);
            $stats['bureaux_actifs'] = count($bureauModel->findAllActive());

            $this->render('frontoffice/rendezvous/index', [
                'title' => 'Mes rendez-vous',
                'rdvs' => $rdvs,
                'stats' => $stats,
                'statut_labels' => $this->statutLabels(),
                'teacher_notice' => false,
            ]);
            return;
        }

        $this->render('frontoffice/rendezvous/index', [
            'title' => 'Rendez-vous',
            'rdvs' => [],
            'stats' => ['total' => 0, 'reserve' => 0, 'confirme' => 0, 'annule' => 0, 'termine' => 0, 'bureaux_actifs' => count($bureauModel->findAllActive())],
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

        $bureaux = (new Bureau())->findAllActive();

        $this->render('frontoffice/rendezvous/create', [
            'title' => 'Réserver un créneau',
            'bureaux' => $bureaux,
            'old' => [
                'bureau_id' => '',
                'motif' => '',
                'date_debut' => '',
                'date_fin' => '',
            ],
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
            $this->redirect('/rendezvous/createForm');
            return;
        }

        $payload = $this->parseRdvPayload($_POST);
        $bureauModel = new Bureau();
        $bureaux = $bureauModel->findAllActive();

        if ($payload === null) {
            $this->render('frontoffice/rendezvous/create', [
                'title' => 'Réserver un créneau',
                'bureaux' => $bureaux,
                'old' => [
                    'bureau_id' => (int) ($_POST['bureau_id'] ?? 0),
                    'motif' => trim((string) ($_POST['motif'] ?? '')),
                    'date_debut' => trim((string) ($_POST['date_debut'] ?? '')),
                    'date_fin' => trim((string) ($_POST['date_fin'] ?? '')),
                ],
                'error' => 'Bureau, dates valides requises. La fin doit être après le début.',
            ]);
            return;
        }

        $b = $bureauModel->findById($payload['bureau_id']);
        if ($b === null || (int) ($b['actif'] ?? 0) !== 1) {
            $this->render('frontoffice/rendezvous/create', [
                'title' => 'Réserver un créneau',
                'bureaux' => $bureaux,
                'old' => array_merge($payload, [
                    'bureau_id' => $payload['bureau_id'],
                    'date_debut' => (string) ($_POST['date_debut'] ?? ''),
                    'date_fin' => (string) ($_POST['date_fin'] ?? ''),
                ]),
                'error' => 'Bureau invalide ou inactif.',
            ]);
            return;
        }

        $rdvModel = new RendezVous();
        $newId = $rdvModel->create($this->currentUserId(), $payload);
        if ($newId === false) {
            $this->render('frontoffice/rendezvous/create', [
                'title' => 'Réserver un créneau',
                'bureaux' => $bureaux,
                'old' => array_merge($payload, [
                    'bureau_id' => $payload['bureau_id'],
                    'date_debut' => (string) ($_POST['date_debut'] ?? ''),
                    'date_fin' => (string) ($_POST['date_fin'] ?? ''),
                ]),
                'error' => 'Créneau indisponible (chevauchement) ou erreur d’enregistrement.',
            ]);
            return;
        }

        $this->setFlash('success', 'Rendez-vous créé (en attente de confirmation).');
        $this->redirect('/rendezvous');
    }

    public function editForm(int|string $id): void
    {
        $this->requireLogin();
        if (!$this->isEtudiant()) {
            $this->redirectByUserRole($this->currentRole());
            return;
        }

        $rid = (int) $id;
        $rdvModel = new RendezVous();
        $rdv = $rdvModel->findById($rid);

        if (
            $rdv === null
            || (int) ($rdv['etudiant_id'] ?? 0) !== $this->currentUserId()
            || (string) ($rdv['statut'] ?? '') !== 'reserve'
        ) {
            $this->redirect('/rendezvous');
            return;
        }

        $this->render('frontoffice/rendezvous/edit', [
            'title' => 'Modifier le rendez-vous',
            'rdv' => $rdv,
            'bureaux' => (new Bureau())->findAllActive(),
            'error' => null,
        ]);
    }

    public function update(int|string $id): void
    {
        $this->requireLogin();
        if (!$this->isEtudiant()) {
            $this->redirectByUserRole($this->currentRole());
            return;
        }

        if (!$this->isPost()) {
            $this->redirect('/rendezvous/editForm/' . (int) $id);
            return;
        }

        $rid = (int) $id;
        $payload = $this->parseRdvPayload($_POST);
        $bureauModel = new Bureau();
        $bureaux = $bureauModel->findAllActive();
        $rdvModel = new RendezVous();
        $rdvRow = $rdvModel->findById($rid);

        if (
            $rdvRow === null
            || (int) ($rdvRow['etudiant_id'] ?? 0) !== $this->currentUserId()
            || (string) ($rdvRow['statut'] ?? '') !== 'reserve'
        ) {
            $this->redirect('/rendezvous');
            return;
        }

        if ($payload === null) {
            $this->render('frontoffice/rendezvous/edit', [
                'title' => 'Modifier le rendez-vous',
                'rdv' => array_merge($rdvRow, [
                    'bureau_id' => (int) ($_POST['bureau_id'] ?? 0),
                    'motif' => trim((string) ($_POST['motif'] ?? '')),
                ]),
                'bureaux' => $bureaux,
                'error' => 'Dates invalides : la fin doit être après le début.',
            ]);
            return;
        }

        $b = $bureauModel->findById($payload['bureau_id']);
        if ($b === null || (int) ($b['actif'] ?? 0) !== 1) {
            $this->render('frontoffice/rendezvous/edit', [
                'title' => 'Modifier le rendez-vous',
                'rdv' => array_merge($rdvRow, $payload),
                'bureaux' => $bureaux,
                'error' => 'Bureau invalide ou inactif.',
            ]);
            return;
        }

        $ok = $rdvModel->updateByStudent($rid, $this->currentUserId(), $payload);
        if (!$ok) {
            $this->render('frontoffice/rendezvous/edit', [
                'title' => 'Modifier le rendez-vous',
                'rdv' => array_merge($rdvRow, [
                    'bureau_id' => $payload['bureau_id'],
                    'motif' => $payload['motif'],
                    'date_debut' => $payload['date_debut'],
                    'date_fin' => $payload['date_fin'],
                ]),
                'bureaux' => $bureaux,
                'error' => 'Mise à jour impossible (créneau occupé ou demande déjà traitée).',
            ]);
            return;
        }

        $this->setFlash('success', 'Rendez-vous mis à jour.');
        $this->redirect('/rendezvous');
    }

    public function cancel(int|string $id): void
    {
        $this->requireLogin();
        if (!$this->isEtudiant()) {
            $this->redirectByUserRole($this->currentRole());
            return;
        }

        if (!$this->isPost()) {
            $this->redirect('/rendezvous');
            return;
        }

        $ok = (new RendezVous())->cancelByStudent((int) $id, $this->currentUserId());
        $this->setFlash($ok ? 'success' : 'danger', $ok ? 'Rendez-vous annulé.' : 'Annulation impossible.');

        $this->redirect('/rendezvous');
    }

    public function updateStatut(int|string $id): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->isPost()) {
            $this->redirect('/rendezvous');
            return;
        }

        $statut = trim((string) ($_POST['statut'] ?? ''));
        if (!in_array($statut, RendezVous::allowedStatuts(), true)) {
            $this->setFlash('danger', 'Statut invalide.');
            $this->redirect('/rendezvous');
            return;
        }

        $ok = (new RendezVous())->updateStatut((int) $id, $statut);
        $this->setFlash($ok ? 'success' : 'danger', $ok ? 'Statut mis à jour.' : 'Mise à jour impossible.');

        $this->redirect('/rendezvous');
    }

    public function adminDelete(int|string $id): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->isPost()) {
            $this->redirect('/rendezvous');
            return;
        }

        $ok = (new RendezVous())->deleteByAdmin((int) $id);
        $this->setFlash($ok ? 'success' : 'danger', $ok ? 'Rendez-vous supprimé.' : 'Suppression impossible.');

        $this->redirect('/rendezvous');
    }
}
