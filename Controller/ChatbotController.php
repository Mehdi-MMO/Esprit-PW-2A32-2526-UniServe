<?php

declare(strict_types=1);

class ChatbotController extends Controller
{
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

        $apiKey = trim((string) (getenv('GROQ_API_KEY') ?: ''));
        if ($apiKey === '') {
            http_response_code(500);
            echo json_encode(['error' => 'GROQ_API_KEY non configurée. Ajoutez-la dans .env (racine projet).']);
            exit;
        }

        $model = trim((string) (getenv('GROQ_MODEL') ?: 'llama-3.3-70b-versatile'));
        $user = $this->currentUser();
        $role = (string) ($user['role'] ?? 'utilisateur');

        $requestBody = [
            'model' => $model,
            'messages' => array_merge([
                [
                    'role' => 'system',
                    'content' => "Tu es l'assistant UniServe. Réponds en français, clairement, avec des étapes concrètes quand c'est utile.",
                ],
                [
                    'role' => 'system',
                    'content' => 'Contexte utilisateur : rôle=' . $role,
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

        [$status, $response, $transportError] = GroqClient::postChatCompletions($apiKey, $requestBody, 25);

        if ($response === '' || $transportError !== '') {
            http_response_code(502);
            echo json_encode(['error' => 'Erreur réseau vers le service IA. Détail : ' . $transportError]);
            exit;
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            http_response_code(502);
            echo json_encode(['error' => 'Réponse IA invalide (JSON).']);
            exit;
        }

        $choice = $decoded['choices'][0] ?? null;
        $reply = '';
        if (is_array($choice) && isset($choice['message']['content'])) {
            $reply = (string) $choice['message']['content'];
        }

        if ($status >= 400 || $reply === '') {
            $apiError = (string) ($decoded['error']['message'] ?? 'Réponse IA invalide.');
            http_response_code(502);
            echo json_encode(['error' => $apiError]);
            exit;
        }

        echo json_encode(['reply' => $reply], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
