<?php

declare(strict_types=1);

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->requireRole(['staff', 'admin']);

        $this->redirect('/backoffice/dashboard');
        return;
    }
}
