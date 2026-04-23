<?php

declare(strict_types=1);

class Controller
{
    protected function basePath(): string
    {
        $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
        $dir = str_replace('\\', '/', dirname($scriptName));
        $dir = $dir === '/' ? '' : rtrim($dir, '/');
        return $dir;
    }

    public function url(string $path = ''): string
    {
        $path = '/' . ltrim($path, '/');
        return $this->basePath() . ($path === '/' ? '' : $path);
    }

    public function render(string $view, array $data = [], ?string $layoutOverride = null): void
    {
        $viewPath = __DIR__ . '/../View/' . $view . '.php';
        if (!is_file($viewPath)) {
            http_response_code(404);
            exit('View not found.');
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        $layoutPath = $this->resolveLayoutPath($layoutOverride);
        require $layoutPath;
    }

    public function redirect(string $url): void
    {
        if ($url !== '' && $url[0] === '/') {
            $url = $this->url($url);
        }
        header('Location: ' . $url);
        exit;
    }

    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user']);
    }

    public function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/auth/login');
        }
    }

    public function requireRole(array $roles): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/auth/login');
        }

        $currentRole = (string) ($_SESSION['user']['role'] ?? '');

        if (!in_array($currentRole, $roles, true)) {
            $this->redirectByUserRole($currentRole);
        }
    }

    public function currentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    protected function redirectByUserRole(string $role): void
    {
        if (in_array($role, ['etudiant', 'enseignant', 'staff', 'admin'], true)) {
            $this->redirect('/home');
        }

        $this->redirect('/auth/login');
    }

    private function resolveLayoutPath(?string $layoutOverride = null): string
    {
        $layout = $layoutOverride;

        if ($layout === null || $layout === '') {
            $role = (string) ($_SESSION['user']['role'] ?? '');
            $layout = 'landing';

            if (in_array($role, ['etudiant', 'enseignant'], true)) {
                $layout = 'frontoffice';
            } elseif (in_array($role, ['staff', 'admin'], true)) {
                $layout = 'backoffice';
            }
        }

        return __DIR__ . '/../View/layout_' . $layout . '.php';
    }
}
