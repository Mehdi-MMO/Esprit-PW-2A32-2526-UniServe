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
            'reserve' => 'En attente',
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
            $tab = trim((string) ($_GET['tab'] ?? 'rdv'));
            if (!in_array($tab, ['rdv', 'bureaux'], true)) {
                $tab = 'rdv';
            }

            $statut = trim((string) ($_GET['statut'] ?? ''));
            $q = trim((string) ($_GET['q'] ?? ''));
            $sort = trim((string) ($_GET['sort'] ?? 'date_desc'));
            if (!in_array($sort, ['date_desc', 'date_asc'], true)) {
                $sort = 'date_desc';
            }

            $filters = [];
            if ($statut !== '') {
                $filters['statut'] = $statut;
            }
            if ($q !== '') {
                $filters['q'] = $q;
            }

            $dashboardStats = $rdvModel->adminDashboardStats();
            $bureauxAll = $bureauModel->findAllOrdered();
            $nbBureaux = count($bureauxAll);
            $nbRdvs = (int) ($dashboardStats['total'] ?? 0);

            $bureauBq = trim((string) ($_GET['bq'] ?? ''));
            $bureauxFiltered = array_values(array_filter($bureauxAll, static function (array $b) use ($bureauBq): bool {
                if ($bureauBq === '') {
                    return true;
                }
                $hay = mb_strtolower(
                    trim((string) ($b['nom'] ?? '')) . ' ' .
                    trim((string) ($b['localisation'] ?? '')) . ' ' .
                    trim((string) ($b['type_service'] ?? '')),
                    'UTF-8'
                );

                return str_contains($hay, mb_strtolower($bureauBq, 'UTF-8'));
            }));
            $nbBureauxFiltered = count($bureauxFiltered);
            $actifsCount = 0;
            foreach ($bureauxAll as $bRow) {
                if ((int) ($bRow['actif'] ?? 0) === 1) {
                    $actifsCount++;
                }
            }
            $bureauKpi = [
                'total' => $nbBureaux,
                'actifs' => $actifsCount,
                'inactifs' => max(0, $nbBureaux - $actifsCount),
                'rdv_total' => $nbRdvs,
            ];

            $perPage = 10;
            $page = max(1, (int) ($_GET['page'] ?? 1));
            $totalFiltered = $rdvModel->countForAdmin($filters);
            $totalPages = max(1, (int) ceil($totalFiltered / $perPage));
            if ($page > $totalPages) {
                $page = $totalPages;
            }
            $offset = ($page - 1) * $perPage;
            $rdvs = $rdvModel->findAllForAdmin($filters, $sort, $perPage, $offset);

            $bureauPerPage = 8;
            $bureauPage = max(1, (int) ($_GET['bpage'] ?? 1));
            $bureauTotalPages = max(1, (int) ceil($nbBureauxFiltered / $bureauPerPage));
            if ($bureauPage > $bureauTotalPages) {
                $bureauPage = $bureauTotalPages;
            }
            $bureauOffset = ($bureauPage - 1) * $bureauPerPage;
            $bureauxPaged = array_slice($bureauxFiltered, $bureauOffset, $bureauPerPage);
            $bureauFrom = $nbBureauxFiltered === 0 ? 0 : $bureauOffset + 1;
            $bureauTo = min($nbBureauxFiltered, $bureauOffset + count($bureauxPaged));

            $flash = null;
            if (isset($_SESSION['flash'])) {
                $flash = $_SESSION['flash'];
                unset($_SESSION['flash']);
            }

            $this->render('backoffice/rendezvous/index', [
                'title' => 'Rendez-vous',
                'tab' => $tab,
                'rdvs' => $rdvs,
                'dashboard_stats' => $dashboardStats,
                'nb_rdvs' => $nbRdvs,
                'nb_bureaux' => $nbBureaux,
                'nb_bureaux_filtered' => $nbBureauxFiltered,
                'bureau_bq' => $bureauBq,
                'bureau_kpi' => $bureauKpi,
                'bureaux' => $bureauxPaged,
                'bureau_page' => $bureauPage,
                'bureau_total_pages' => $bureauTotalPages,
                'bureau_from' => $bureauFrom,
                'bureau_to' => $bureauTo,
                'statut_filter' => $statut,
                'q' => $q,
                'sort' => $sort,
                'statut_labels' => $this->statutLabels(),
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $totalFiltered,
                    'total_pages' => $totalPages,
                ],
                'flash' => $flash,
            ]);

            return;
        }

        if ($this->isEtudiant()) {
            $uid = $this->currentUserId();
            $rdvs = $rdvModel->findAllForStudent($uid);
            $stats = $this->statsFromRows($rdvs);
            $bureauxActifs = $bureauModel->findAllActive();
            $stats['bureaux_actifs'] = count($bureauxActifs);

            $bq = trim((string) ($_GET['bq'] ?? ''));
            $bFiltered = array_values(array_filter($bureauxActifs, static function (array $b) use ($bq): bool {
                if ($bq === '') {
                    return true;
                }
                $hay = mb_strtolower(
                    trim((string) ($b['nom'] ?? '')) . ' ' .
                    trim((string) ($b['localisation'] ?? '')) . ' ' .
                    trim((string) ($b['type_service'] ?? '')),
                    'UTF-8'
                );

                return str_contains($hay, mb_strtolower($bq, 'UTF-8'));
            }));

            $bPerPage = 6;
            $bPage = max(1, (int) ($_GET['bpage'] ?? 1));
            $bTotalFiltered = count($bFiltered);
            $bTotalPages = max(1, (int) ceil($bTotalFiltered / $bPerPage));
            if ($bPage > $bTotalPages) {
                $bPage = $bTotalPages;
            }
            $bOff = ($bPage - 1) * $bPerPage;
            $bureauxHub = array_slice($bFiltered, $bOff, $bPerPage);
            $bureauFrom = $bTotalFiltered === 0 ? 0 : $bOff + 1;
            $bureauTo = min($bTotalFiltered, $bOff + count($bureauxHub));

            $nonAnnules = max(1, (int) $stats['total'] - (int) $stats['annule']);
            $tauxSucces = (int) min(100, (int) round(100 * ((int) $stats['confirme'] + (int) $stats['termine']) / $nonAnnules));

            $campusPins = [];
            foreach ($bureauxActifs as $b) {
                $bid = (int) ($b['id'] ?? 0);
                if ($bid <= 0) {
                    continue;
                }
                $campusPins[] = [
                    'id' => $bid,
                    'nom' => (string) ($b['nom'] ?? ''),
                    'x' => (($bid * 61) % 78) + 8,
                    'y' => (($bid * 47) % 65) + 12,
                    'tone' => $bid % 6,
                ];
            }

            $flash = null;
            if (isset($_SESSION['flash'])) {
                $flash = $_SESSION['flash'];
                unset($_SESSION['flash']);
            }

            $this->render('frontoffice/rendezvous/index', [
                'title' => 'Rendez-vous',
                'rdvs' => $rdvs,
                'stats' => $stats,
                'statut_labels' => $this->statutLabels(),
                'teacher_notice' => false,
                'flash' => $flash,
                'hub_bq' => $bq,
                'hub_bpage' => $bPage,
                'hub_bureaux' => $bureauxHub,
                'hub_bureau_total_filtered' => $bTotalFiltered,
                'hub_bureau_total_pages' => $bTotalPages,
                'hub_bureau_from' => $bureauFrom,
                'hub_bureau_to' => $bureauTo,
                'hub_taux_succes' => $tauxSucces,
                'hub_campus_pins' => $campusPins,
            ]);

            return;
        }

        $flash = null;
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
        }

        $this->render('frontoffice/rendezvous/index', [
            'title' => 'Rendez-vous',
            'rdvs' => [],
            'stats' => ['total' => 0, 'reserve' => 0, 'confirme' => 0, 'annule' => 0, 'termine' => 0, 'bureaux_actifs' => count($bureauModel->findAllActive())],
            'statut_labels' => $this->statutLabels(),
            'teacher_notice' => true,
            'flash' => $flash,
        ]);
    }

    /**
     * Printable list (same filters as admin index) — use the browser’s « Enregistrer au format PDF ».
     */
    public function exportPrint(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        $rdvModel = new RendezVous();
        $statut = trim((string) ($_GET['statut'] ?? ''));
        $q = trim((string) ($_GET['q'] ?? ''));
        $sort = trim((string) ($_GET['sort'] ?? 'date_desc'));
        if (!in_array($sort, ['date_desc', 'date_asc'], true)) {
            $sort = 'date_desc';
        }

        $filters = [];
        if ($statut !== '') {
            $filters['statut'] = $statut;
        }
        if ($q !== '') {
            $filters['q'] = $q;
        }

        $rows = $rdvModel->findAllForAdmin($filters, $sort, null, 0);

        $this->render('backoffice/rendezvous/export_print', [
            'title' => 'Export rendez-vous',
            'rdvs' => $rows,
            'statut_labels' => $this->statutLabels(),
            'generated_at' => (new \DateTimeImmutable('now'))->format('d/m/Y H:i'),
        ], 'print');
    }

    /**
     * Liste imprimable des bureaux (filtre recherche optionnel GET bq) — PDF via navigateur.
     */
    public function exportBureauxPrint(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        $bq = trim((string) ($_GET['bq'] ?? ''));
        $all = (new Bureau())->findAllOrdered();
        $filtered = array_values(array_filter($all, static function (array $b) use ($bq): bool {
            if ($bq === '') {
                return true;
            }
            $hay = mb_strtolower(
                trim((string) ($b['nom'] ?? '')) . ' ' .
                trim((string) ($b['localisation'] ?? '')) . ' ' .
                trim((string) ($b['type_service'] ?? '')),
                'UTF-8'
            );

            return str_contains($hay, mb_strtolower($bq, 'UTF-8'));
        }));

        $this->render('backoffice/rendezvous/export_bureaux_print', [
            'title' => 'Export bureaux',
            'bureaux' => $filtered,
            'search_bq' => $bq,
            'generated_at' => (new \DateTimeImmutable('now'))->format('d/m/Y H:i'),
        ], 'print');
    }

    /**
     * Étudiant : liste imprimable de ses propres rendez-vous (PDF via navigateur).
     */
    public function exportMesPrint(): void
    {
        $this->requireLogin();
        if (!$this->isEtudiant()) {
            $this->redirectByUserRole($this->currentRole());
            return;
        }

        $rows = (new RendezVous())->findAllForStudent($this->currentUserId());

        $this->render('frontoffice/rendezvous/export_mes_print', [
            'title' => 'Mes rendez-vous — export',
            'rdvs' => $rows,
            'statut_labels' => $this->statutLabels(),
            'generated_at' => (new \DateTimeImmutable('now'))->format('d/m/Y H:i'),
        ], 'print');
    }

    public function createForm(): void
    {
        $this->requireLogin();
        if (!$this->isEtudiant()) {
            $this->redirectByUserRole($this->currentRole());
            return;
        }

        $bureaux = (new Bureau())->findAllActive();

        $preBureau = (int) ($_GET['bureau_id'] ?? $_GET['bureau'] ?? 0);
        $validIds = array_map(static fn (array $b): int => (int) ($b['id'] ?? 0), $bureaux);
        if ($preBureau > 0 && !in_array($preBureau, $validIds, true)) {
            $preBureau = 0;
        }

        $this->render('frontoffice/rendezvous/create', [
            'title' => 'Réserver un créneau',
            'bureaux' => $bureaux,
            'old' => [
                'bureau_id' => $preBureau > 0 ? (string) $preBureau : '',
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
            $this->redirect('/rendezvous?tab=rdv');
            return;
        }

        $statut = trim((string) ($_POST['statut'] ?? ''));
        if (!in_array($statut, RendezVous::allowedStatuts(), true)) {
            $this->setFlash('danger', 'Statut invalide.');
            $this->redirect('/rendezvous?tab=rdv');
            return;
        }

        $ok = (new RendezVous())->updateStatut((int) $id, $statut);
        $this->setFlash($ok ? 'success' : 'danger', $ok ? 'Statut mis à jour.' : 'Mise à jour impossible.');

        $this->redirect('/rendezvous?tab=rdv');
    }

    public function adminDelete(int|string $id): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (!$this->isPost()) {
            $this->redirect('/rendezvous?tab=rdv');
            return;
        }

        $ok = (new RendezVous())->deleteByAdmin((int) $id);
        $this->setFlash($ok ? 'success' : 'danger', $ok ? 'Rendez-vous supprimé.' : 'Suppression impossible.');

        $this->redirect('/rendezvous?tab=rdv');
    }
}
