<?php

declare(strict_types=1);

/**
 * Groq-based login risk signals (THEMODULES USER_MODULE used Gemini; INTEG uses Groq).
 */
class GroqLoginRiskService
{
    public function assess(array $signals): ?array
    {
        if (!$this->isEnabled()) {
            return null;
        }

        $apiKey = trim((string) (getenv('GROQ_API_KEY') ?: ''));
        if ($apiKey === '') {
            return null;
        }

        $model = trim((string) (getenv('GROQ_MODEL') ?: 'llama-3.3-70b-versatile'));
        $signalsJson = json_encode($signals, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($signalsJson) || $signalsJson === '') {
            $signalsJson = '{}';
        }

        $body = [
            'model' => $model,
            'temperature' => 0.1,
            'max_tokens' => 256,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You classify login risk for a university web portal. Reply with ONLY valid JSON: '
                        . '{"risk_level":"low"|"medium"|"high","risk_score":0-100,"reason_codes":["TOKEN"]}. '
                        . 'reason_codes: short uppercase snake tokens. No markdown.',
                ],
                [
                    'role' => 'user',
                    'content' => 'Classify this login attempt from signals (JSON): ' . $signalsJson,
                ],
            ],
        ];

        [$status, $raw] = GroqClient::postChatCompletions($apiKey, $body, 12);
        if ($status < 200 || $status >= 300 || !is_string($raw) || $raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return null;
        }

        $text = trim((string) ($decoded['choices'][0]['message']['content'] ?? ''));
        if ($text === '') {
            return null;
        }

        $parsed = $this->decodeAssistantJson($text);
        if ($parsed === null) {
            return null;
        }

        return $this->normalizeResult($parsed);
    }

    private function isEnabled(): bool
    {
        $flag = strtolower(trim((string) (getenv('LOGIN_RISK_AI_ENABLED') ?: '1')));

        return !in_array($flag, ['0', 'false', 'no', 'off'], true);
    }

    private function decodeAssistantJson(string $text): ?array
    {
        $text = trim($text);
        if ($text === '') {
            return null;
        }

        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{.*\}/s', $text, $matches) === 1) {
            $candidate = json_decode((string) ($matches[0] ?? ''), true);
            if (is_array($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $result
     * @return array{risk_level: string, risk_score: int, reason_codes: list<string>}|null
     */
    private function normalizeResult(array $result): ?array
    {
        $riskLevel = strtolower(trim((string) ($result['risk_level'] ?? '')));
        if (!in_array($riskLevel, ['low', 'medium', 'high'], true)) {
            return null;
        }

        $riskScore = max(0, min(100, (int) ($result['risk_score'] ?? 0)));

        $reasonCodes = $result['reason_codes'] ?? [];
        if (!is_array($reasonCodes)) {
            $reasonCodes = [];
        }

        $normalizedReasons = [];
        foreach ($reasonCodes as $reasonCode) {
            $token = strtoupper(trim((string) $reasonCode));
            if ($token !== '') {
                $normalizedReasons[] = preg_replace('/[^A-Z0-9_]/', '_', $token) ?? $token;
            }
        }

        return [
            'risk_level' => $riskLevel,
            'risk_score' => $riskScore,
            'reason_codes' => array_values(array_unique($normalizedReasons)),
        ];
    }
}
