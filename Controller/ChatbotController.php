<?php

declare(strict_types=1);

class ChatbotController extends Controller
{
    private function extractGeminiReply(?array $decoded): string
    {
        if ($decoded === null) {
            return '';
        }
        $candidates = $decoded['candidates'] ?? null;
        if (!is_array($candidates) || !isset($candidates[0]) || !is_array($candidates[0])) {
            return '';
        }
        $parts = $candidates[0]['content']['parts'] ?? null;
        if (!is_array($parts)) {
            return '';
        }
        $text = '';
        foreach ($parts as $part) {
            if (is_array($part) && isset($part['text'])) {
                $text .= (string) $part['text'];
            }
        }

        return trim($text);
    }

    private function extractGeminiApiError(?array $decoded, string $default): string
    {
        if (!is_array($decoded)) {
            return $default;
        }
        $err = $decoded['error'] ?? null;
        if (is_array($err)) {
            return (string) ($err['message'] ?? $default);
        }

        return $default;
    }

    /**
     * @param array<int, array{role: string, parts: array<int, array{text: string}>}> $contents
     */
    private function callGemini(string $apiKey, string $model, string $systemInstruction, array $contents, int $maxOutputTokens): array
    {
        $requestBody = [
            'contents' => $contents,
            'systemInstruction' => [
                'parts' => [['text' => $systemInstruction]],
            ],
            'generationConfig' => [
                'temperature' => 0.4,
                'maxOutputTokens' => $maxOutputTokens,
            ],
        ];

        $body = json_encode($requestBody, JSON_UNESCAPED_UNICODE);
        if (!is_string($body)) {
            return [0, '', 'JSON encode error'];
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/'
            . rawurlencode($model)
            . ':generateContent?key=' . rawurlencode($apiKey);

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                ],
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_TIMEOUT => 90,
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
                'header' => "Content-Type: application/json\r\n",
                'content' => $body,
                'timeout' => 90,
                'ignore_errors' => true,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
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

        $apiKey = function_exists('app_env') ? app_env('GEMINI_API_KEY', '') : ((getenv('GEMINI_API_KEY') ?: ''));
        if ($apiKey === '') {
            http_response_code(500);
            echo json_encode(['error' => 'GEMINI_API_KEY non configuree. Ajoutez-la dans .env (racine projet).']);
            exit;
        }

        $model = function_exists('app_env')
            ? app_env('GEMINI_MODEL', 'gemini-2.0-flash')
            : ((getenv('GEMINI_MODEL') ?: 'gemini-2.0-flash'));

        $maxOutput = (int) (function_exists('app_env')
            ? app_env('GEMINI_MAX_OUTPUT_TOKENS', '8192')
            : ((getenv('GEMINI_MAX_OUTPUT_TOKENS') ?: '8192')));
        if ($maxOutput < 128) {
            $maxOutput = 8192;
        }

        $user = $this->currentUser();
        $role = (string) ($user['role'] ?? 'utilisateur');

        $systemInstruction = 'Tu es l assistant UniServe. Reponds en francais, clairement, avec des etapes concretes quand utile.'
            . "\n\nContexte utilisateur: role=" . $role;

        $contents = [];
        foreach (array_values(array_filter((array) $history, static function ($item): bool {
            if (!is_array($item)) {
                return false;
            }
            $r = (string) ($item['role'] ?? '');
            $c = trim((string) ($item['content'] ?? ''));
            return in_array($r, ['user', 'assistant'], true) && $c !== '';
        })) as $item) {
            $r = (string) ($item['role'] ?? '');
            $c = trim((string) ($item['content'] ?? ''));
            $geminiRole = $r === 'assistant' ? 'model' : 'user';
            $contents[] = [
                'role' => $geminiRole,
                'parts' => [['text' => $c]],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $message]],
        ];

        [$status, $response, $transportError] = $this->callGemini($apiKey, $model, $systemInstruction, $contents, $maxOutput);

        if ($response === '' || $transportError !== '') {
            http_response_code(502);
            echo json_encode(['error' => 'Erreur reseau vers le service IA. Detail: ' . $transportError]);
            exit;
        }

        $decoded = json_decode($response, true);
        $reply = $this->extractGeminiReply(is_array($decoded) ? $decoded : null);

        if ($status >= 400 || $reply === '') {
            $apiError = is_array($decoded)
                ? $this->extractGeminiApiError($decoded, 'Reponse IA invalide.')
                : 'Reponse IA invalide.';
            if ($reply === '' && is_array($decoded) && isset($decoded['promptFeedback']['blockReason'])) {
                $apiError = 'Contenu bloque par le filtre de securite.';
            }
            http_response_code(502);
            echo json_encode(['error' => $apiError]);
            exit;
        }

        echo json_encode(['reply' => $reply], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
