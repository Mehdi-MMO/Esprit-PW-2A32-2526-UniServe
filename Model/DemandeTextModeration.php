<?php

declare(strict_types=1);

/**
 * Optional Groq check on demande text (THEMODULES DEMANDE_MODULE parity).
 */
class DemandeTextModeration
{
    public static function isEnabled(): bool
    {
        $flag = strtolower(trim((string) (getenv('DEMANDE_GROQ_MODERATION_ENABLED') ?: '0')));

        return !in_array($flag, ['0', 'false', 'no', 'off'], true);
    }

    /**
     * @return string|null Error message in French, or null if OK / skipped.
     */
    public static function validate(string $titre, string $description, ?string $categorieServiceNom = null): ?string
    {
        if (!self::isEnabled()) {
            return null;
        }

        $apiKey = trim((string) (getenv('GROQ_API_KEY') ?: ''));
        if ($apiKey === '') {
            return null;
        }

        $titre = trim($titre);
        $description = trim($description);
        if ($titre === '' || $description === '') {
            return null;
        }

        $model = trim((string) (getenv('GROQ_MODEL') ?: 'llama-3.3-70b-versatile'));
        $payload = ['titre' => $titre, 'description' => $description];
        $cat = $categorieServiceNom !== null ? trim($categorieServiceNom) : '';
        if ($cat !== '') {
            $payload['categorie_service'] = $cat;
        }
        $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($payloadJson)) {
            return null;
        }

        $body = [
            'model' => $model,
            'temperature' => 0.1,
            'max_tokens' => 200,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You review a student service request for a French university portal. '
                        . 'Reject obvious spam, gibberish, hate, or wholly empty-of-meaning text. '
                        . 'Reply with ONLY JSON: {"acceptable":true|false,"reason_fr":"short French explanation if not acceptable"}.',
                ],
                [
                    'role' => 'user',
                    'content' => 'Contenu JSON à évaluer (la catégorie de service aide au contexte, sans valeur juridique) : ' . $payloadJson,
                ],
            ],
        ];

        [$status, $raw] = GroqClient::postChatCompletions($apiKey, $body, 15);
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

        $parsed = self::decodeJsonObject($text);
        if ($parsed === null) {
            return null;
        }

        $acceptable = (bool) ($parsed['acceptable'] ?? true);
        if ($acceptable) {
            return null;
        }

        $reason = trim((string) ($parsed['reason_fr'] ?? ''));
        if ($reason === '') {
            return 'Le texte semble inapproprié ou peu clair. Veuillez reformuler votre demande.';
        }

        return $reason;
    }

    private static function decodeJsonObject(string $text): ?array
    {
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
}
