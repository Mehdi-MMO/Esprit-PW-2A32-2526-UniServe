<?php

declare(strict_types=1);

class FrontofficeController extends Controller
{
    public function landing(): void
    {
        $this->dashboard();
    }

    public function dashboard(): void
    {
        $this->requireLogin();
        $this->requireRole(['etudiant', 'enseignant']);

        $currentUser = $this->currentUser();
        $userId = (int) ($currentUser['id'] ?? 0);
        $role = (string) ($currentUser['role'] ?? 'etudiant');

        $fromDate = (new DateTime('first day of this month'))->modify('-7 days')->format('Y-m-d 00:00:00');
        $toDate = (new DateTime('last day of this month'))->modify('+14 days')->format('Y-m-d 23:59:59');

        $calendarService = new CalendarService();
        $calendarFeed = $calendarService->getCalendarFeedForUser(
            $userId,
            $role,
            $fromDate,
            $toDate
        );

        $this->render('frontoffice/dashboard', [
            'calendarEvents' => $calendarFeed['events'] ?? [],
            'calendarMeta' => $calendarFeed['meta'] ?? [],
        ]);
    }
}

