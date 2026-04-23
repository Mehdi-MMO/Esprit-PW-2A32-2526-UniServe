<?php

declare(strict_types=1);

class DocumentsController extends Controller
{
    private function viewPath(): string
    {
        $role = (string) ($_SESSION['user']['role'] ?? '');
        if (in_array($role, ['staff', 'admin'], true)) {
            return 'backoffice/documents/index';
        }

        return 'frontoffice/documents/index';
    }

    public function landing(): void
    {
        $this->render($this->viewPath(), [
            'title' => 'Documents académiques',
        ]);
    }

    public function index(): void
    {
        $this->landing();
    }
}

