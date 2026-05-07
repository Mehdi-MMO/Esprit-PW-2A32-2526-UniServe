<?php

declare(strict_types=1);

class ChatbotController extends Controller
{
    private function callOpenAI(string $apiKey, array $requestBody): array
    {
        $body = json_encode($requestBody, JSON_UNESCAPED_UNICODE);
        if (!is_string($body)) {
            return [0, '', 'JSON encode error'];
        }

        if (function_exists('curl_init')) {
            $ch = curl_init('https://api.openai.com/v1/chat/completions');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey,
                ],
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_TIMEOUT => 25,
            ]);

            $response = curl_exec($ch);
            $curlError = curl_error($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return [$status, is_string($response) ? $response : '', $curlError];
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nAuthorization: Bearer {$apiKey}\r\n",
                'content' => $body,
                'timeout' => 25,
                'ignore_errors' => true,
            ],
        ]);

        $response = @file_get_contents('https://api.openai.com/v1/chat/completions', false, $context);
        $status = 0;
        if (isset($http_response_header) && is_array($http_response_header)) {
            foreach ($http_response_header as $line) {
                if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $line, $m)) {
                    $status = (int) $m[1];
                    break;
                }
            }
        }

        return [$status, is_string($response) ? $response : '', $response === false ? 'HTTP request failed' : ''];
    }

    public function ask(): void
    {
        $this->requireLogin();

        if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');

        $raw = file_get_contents('php://input');
        $payload = json_decode((string) $raw, true);
        $message = trim((string) ($payload['message'] ?? ''));
        $history = $payload['history'] ?? [];

        if ($message === '') {
            http_response_code(422);
            echo json_encode(['error' => 'Message vide.']);
            exit;
        }

        $apiKey = function_exists('app_env') ? app_env('OPENAI_API_KEY', '') : ((getenv('OPENAI_API_KEY') ?: ''));
        if ($apiKey === '') {
            http_response_code(500);
            echo json_encode(['error' => 'OPENAI_API_KEY non configuree. Ajoutez-la dans .env (racine projet).']);
            exit;
        }

        $model = function_exists('app_env') ? app_env('OPENAI_MODEL', 'gpt-4o-mini') : ((getenv('OPENAI_MODEL') ?: 'gpt-4o-mini'));
        $user = $this->currentUser();
        $role = (string) ($user['role'] ?? 'utilisateur');

        $requestBody = [
            'model' => $model,
            'messages' => array_merge([
                [
                    'role' => 'system',
                    'content' => 'Tu es l assistant UniServe. Reponds en francais, clairement, avec des etapes concretes quand utile.',
                ],
                [
                    'role' => 'system',
                    'content' => 'Contexte utilisateur: role=' . $role,
                ],
            ], array_values(array_filter((array) $history, static function ($item): bool {
                if (!is_array($item)) {
                    return false;
                }
                $r = (string) ($item['role'] ?? '');
                $c = trim((string) ($item['content'] ?? ''));
                return in_array($r, ['user', 'assistant'], true) && $c !== '';
            })), [
                ['role' => 'user', 'content' => $message],
            ]),
            'temperature' => 0.4,
            'max_tokens' => 400,
        ];

        [$status, $response, $transportError] = $this->callOpenAI($apiKey, $requestBody);

        if ($response === '' || $transportError !== '') {
            http_response_code(502);
            echo json_encode(['error' => 'Erreur reseau vers le service IA. Detail: ' . $transportError]);
            exit;
        }

        $decoded = json_decode($response, true);
        $reply = (string) ($decoded['choices'][0]['message']['content'] ?? '');

        if ($status >= 400 || $reply === '') {
            $apiError = (string) ($decoded['error']['message'] ?? 'Reponse IA invalide.');
            http_response_code(502);
            echo json_encode(['error' => $apiError]);
            exit;
        }

        echo json_encode(['reply' => $reply], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

