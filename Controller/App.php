<?php

declare(strict_types=1);

/**
 * Front controller: first URL segment selects Controller/<Segment>Controller.php.
 * MVC front controller: see README.md at repository root.
 */
class App
{
    private string $controller = 'AuthController';
    private string $action = 'landing';
    private array $params = [];

    public function __construct()
    {
        $url = $this->parseUrl();

        if (!empty($url)) {
            $candidateController = ucfirst((string) $url[0]) . 'Controller';
            $controllerPath = __DIR__ . '/' . $candidateController . '.php';
            if (!is_file($controllerPath)) {
                $this->notFound('Controller not found.');
            }

            $this->controller = $candidateController;
            array_shift($url);
        }

        $controllerFile = __DIR__ . '/' . $this->controller . '.php';
        if (!is_file($controllerFile)) {
            $this->notFound('Controller not found.');
        }

        require_once $controllerFile;
        $controllerInstance = new $this->controller();

        if (!empty($url)) {
            $candidateAction = (string) $url[0];
            if (!method_exists($controllerInstance, $candidateAction)) {
                $this->notFound('Action not found.');
            }

            $this->action = $candidateAction;
            array_shift($url);
        } elseif (!method_exists($controllerInstance, $this->action)) {
            $this->notFound('Action not found.');
        }

        $this->params = $this->validateAndNormalizeParams($controllerInstance, $this->action, array_values($url));
        call_user_func_array([$controllerInstance, $this->action], $this->params);
    }

    private function parseUrl(): array
    {
        if (!isset($_GET['url']) || trim((string) $_GET['url']) === '') {
            return [];
        }

        $cleanUrl = trim((string) $_GET['url']);
        $cleanUrl = trim($cleanUrl, '/');
        if ($cleanUrl === '') {
            return [];
        }

        $segments = explode('/', $cleanUrl);
        return array_values(array_filter($segments, static fn (string $segment): bool => $segment !== ''));
    }

    private function validateAndNormalizeParams(object $controllerInstance, string $action, array $params): array
    {
        $method = new ReflectionMethod($controllerInstance, $action);
        $required = $method->getNumberOfRequiredParameters();
        $max = $method->getNumberOfParameters();
        $isVariadic = false;

        foreach ($method->getParameters() as $parameter) {
            if ($parameter->isVariadic()) {
                $isVariadic = true;
                break;
            }
        }

        $count = count($params);
        if ($count < $required || (!$isVariadic && $count > $max)) {
            $this->notFound('Invalid route parameters.');
        }

        return $params;
    }

    private function notFound(string $message): never
    {
        http_response_code(404);
        exit($message);
    }
}
