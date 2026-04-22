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
            $this->redirect($this->url('/auth/login'));
        }
    }

    public function requireRole(array $roles): void
    {
        $currentRole = (string) ($_SESSION['user']['role'] ?? '');
        if (!in_array($currentRole, $roles, true)) {
            $this->redirect($this->url('/'));
        }
    }

    public function currentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    private function resolveLayoutPath(): string
    {
        $role = (string) ($_SESSION['user']['role'] ?? '');
        $layout = 'landing';

        if (in_array($role, ['etudiant', 'enseignant'], true)) {
            $layout = 'frontoffice';
        } elseif (in_array($role, ['staff', 'admin'], true)) {
            $layout = 'backoffice';
        }

        return __DIR__ . '/../View/layouts/' . $layout . '.php';
    }
    /**
 * Helper method to load and instantiate models
 */
public function model(string $model): object
{
    // On définit le chemin absolu à partir de la racine du projet
    $modelPath = 'C:/xampp/htdocs/UniServe/Model/' . $model . '.php'; 

    if (file_exists($modelPath)) {
        require_once $modelPath;
        return new $model();
    }
    
    die("Erreur fatale : Le fichier $model.php est introuvable dans " . $modelPath);
}
}
