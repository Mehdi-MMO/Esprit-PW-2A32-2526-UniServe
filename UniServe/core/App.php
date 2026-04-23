<?php

declare(strict_types=1);

class App
{
    private string $controller = 'AuthController';
    private string $action = 'landing';
    private array $params = [];

    public function __construct()
    {
        $url = $this->parseUrl();

        if (!empty($url[0])) {
            $candidateController = ucfirst($url[0]) . 'Controller';
            $controllerPath = __DIR__ . '/../Controller/' . $candidateController . '.php';
            if (is_file($controllerPath)) {
                $this->controller = $candidateController;
                unset($url[0]);
            }
        }

        $controllerFile = __DIR__ . '/../Controller/' . $this->controller . '.php';
        if (!is_file($controllerFile)) {
            http_response_code(404);
            exit('Controller not found.');
        }

        require_once $controllerFile;
        $controllerInstance = new $this->controller();

        if (!empty($url[1]) && method_exists($controllerInstance, $url[1])) {
            $this->action = $url[1];
            unset($url[1]);
        }

        $this->params = array_values($url);
        call_user_func_array([$controllerInstance, $this->action], $this->params);
    }

    private function parseUrl(): array
    {
        if (!isset($_GET['url']) || trim((string) $_GET['url']) === '') {
            return [];
        }

        $cleanUrl = filter_var(rtrim((string) $_GET['url'], '/'), FILTER_SANITIZE_URL);
        return explode('/', $cleanUrl);
    }
}
