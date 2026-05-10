<?php

declare(strict_types=1);

class BackofficeController extends Controller
{
    public function landing(): void
    {
        $this->dashboard();
    }

    public function dashboard(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        $dashboardService = new DashboardService();
        $demoService = new CalendarDemoService();
        $allStats = $dashboardService->getAllStats();
        $demoCalendar = $demoService->getSummary();

        $this->render('backoffice/dashboard', [
            'title' => 'Tableau de bord',
            'stats' => $allStats,
            'demoCalendar' => $demoCalendar,
            'demoNotice' => (string) ($_GET['demo_notice'] ?? ''),
            'demoError' => (string) ($_GET['demo_error'] ?? ''),
        ]);
    }

    public function agendaDemo(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->redirect('/backoffice/dashboard');
        }

        $task = trim((string) ($_POST['task'] ?? ''));
        $demoService = new CalendarDemoService();
        $currentUser = $this->currentUser() ?? [];
        $creatorId = (int) ($currentUser['id'] ?? 0);

        if ($task === 'seed') {
            $result = $demoService->seedDemoAgenda($creatorId > 0 ? $creatorId : null);
            $query = $result['ok']
                ? [
                    'demo_notice' => 'Agenda de démonstration généré.',
                    'demo_created' => (string) ($result['created'] ?? 0),
                    'demo_cleared' => (string) ($result['cleared'] ?? 0),
                ]
                : [
                    'demo_error' => (string) ($result['message'] ?? 'Impossible de générer les données de démonstration.'),
                ];

            $this->redirect('/backoffice/dashboard?' . http_build_query($query));
        }

        if ($task === 'clear') {
            $cleared = $demoService->clearDemoAgenda();
            $this->redirect('/backoffice/dashboard?' . http_build_query([
                'demo_notice' => 'Agenda de démonstration vidé.',
                'demo_cleared' => (string) $cleared,
            ]));
        }

        $this->redirect('/backoffice/dashboard?demo_error=' . urlencode('Action démo inconnue.'));
    }
}
