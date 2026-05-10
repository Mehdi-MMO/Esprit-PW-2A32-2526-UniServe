<?php

declare(strict_types=1);

class HomeController extends Controller
{
    public function landing(): void
    {
        $this->requireLogin();

        $role = (string) ($_SESSION['user']['role'] ?? '');
        if (in_array($role, ['staff', 'admin'], true)) {
            $this->redirect('/backoffice/dashboard');
            return;
        }

        if (in_array($role, ['etudiant', 'enseignant'], true)) {
            $this->redirect('/frontoffice/dashboard');
            return;
        }

        $this->redirect('/auth/login');
    }
}
