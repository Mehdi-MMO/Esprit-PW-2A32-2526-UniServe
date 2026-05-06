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

    public function render(string $view, array $data = []): void
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

        $layoutPath = $this->resolveLayoutPath();
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
        $currentRole = (string) ($_SESSION['user']['role'] ?? '');
        if (!in_array($currentRole, $roles, true)) {
            $this->redirect('/');
        }
    }

    public function currentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    private function resolveLayoutPath(): string
    {
        // Pour la démo : si l'URL contient 'backoffice' or 'dashboard' or 'utilisateurs', 
        // on force le layout backoffice si aucun utilisateur n'est connecté
        $url = (string) ($_GET['url'] ?? '');
        $isBackRoute = (strpos($url, 'backoffice') !== false || strpos($url, 'dashboard') !== false || strpos($url, 'utilisateurs') !== false);

        if ($isBackRoute && !isset($_SESSION['user'])) {
            $_SESSION['user'] = ['id' => 1, 'nom' => 'Simulé', 'prenom' => 'Admin', 'role' => 'admin'];
        }

        $role = (string) ($_SESSION['user']['role'] ?? '');
        $layout = 'landing';

        if (in_array($role, ['etudiant', 'enseignant'], true)) {
            $layout = 'frontoffice';
        } elseif (in_array($role, ['staff', 'admin'], true)) {
            $layout = 'backoffice';
        }

        return __DIR__ . '/../View/layouts/' . $layout . '.php';
    }
}
