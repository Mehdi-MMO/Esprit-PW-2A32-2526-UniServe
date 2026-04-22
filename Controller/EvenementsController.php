<?php

declare(strict_types=1);

class EvenementsController extends Controller
{
    private function viewPath(): string
    {
        $role = (string) ($_SESSION['user']['role'] ?? '');
        if (in_array($role, ['staff', 'admin'], true)) {
            return 'backoffice/evenements/index';
        }

        return 'frontoffice/evenements/index';
    }

    public function landing(): void
    {
        $this->render($this->viewPath(), [
            'title' => 'Événements',
        ]);
    }

    public function index(): void
    {
        $this->landing();
    }
}

