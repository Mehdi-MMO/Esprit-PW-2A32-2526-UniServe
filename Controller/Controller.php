<?php

declare(strict_types=1);

class Controller
{
    private const DEFAULT_INSTITUTIONAL_EMAIL_DOMAINS = ['gmail.com'];

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

    protected function normalizeText(string $value): string
    {
        return trim($value);
    }

    protected function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    protected function validateRequiredField(string $value, string $label): ?string
    {
        if ($this->normalizeText($value) === '') {
            return $label . ' est obligatoire.';
        }

        return null;
    }

    protected function validateMinLength(string $value, int $minimum, string $label): ?string
    {
        if (strlen($value) < $minimum) {
            return $label . ' doit contenir au moins ' . $minimum . ' caractères.';
        }

        return null;
    }

    protected function validateInstitutionalEmail(string $email): ?string
    {
        $normalized = $this->normalizeEmail($email);

        if ($normalized === '') {
            return "L'email est obligatoire.";
        }

        if (!filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            return 'Format email invalide.';
        }

        if (!$this->isInstitutionalEmail($normalized)) {
            return 'Utilisez une adresse email @gmail.com.';
        }

        return null;
    }

    public function institutionalEmailDomainsCsv(): string
    {
        return implode(',', $this->institutionalEmailDomains());
    }

    protected function isInstitutionalEmail(string $email): bool
    {
        $atPos = strrpos($email, '@');
        if ($atPos === false || $atPos === strlen($email) - 1) {
            return false;
        }

        $domain = strtolower(substr($email, $atPos + 1));
        foreach ($this->institutionalEmailDomains() as $allowedDomain) {
            if ($domain === $allowedDomain) {
                return true;
            }
        }

        return false;
    }

    private function institutionalEmailDomains(): array
    {
        $envDomains = (string) (getenv('INSTITUTIONAL_EMAIL_DOMAINS') ?: '');
        if ($envDomains === '') {
            return self::DEFAULT_INSTITUTIONAL_EMAIL_DOMAINS;
        }

        $domains = array_values(array_filter(array_map(
            static fn (string $domain): string => strtolower(trim($domain)),
            explode(',', $envDomains)
        ), static fn (string $domain): bool => $domain !== ''));

        return $domains !== [] ? $domains : self::DEFAULT_INSTITUTIONAL_EMAIL_DOMAINS;
    }

    protected function redirectByUserRole(string $role): void
    {
        if (in_array($role, ['etudiant', 'enseignant'], true)) {
            $this->redirect('/frontoffice/dashboard');
        }

        if (in_array($role, ['staff', 'admin'], true)) {
            $this->redirect('/backoffice/dashboard');
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
