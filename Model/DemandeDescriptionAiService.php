<?php

declare(strict_types=1);

/**
 * Optional Groq assist for drafting the "description" field on service requests.
 */
class DemandeDescriptionAiService
{
    public static function isEnabled(): bool
    {
        $apiKey = trim((string) (getenv('GROQ_API_KEY') ?: ''));
        if ($apiKey === '') {
            return false;
        }

        $flag = strtolower(trim((string) (getenv('DEMANDE_AI_DESCRIPTION_ENABLED') ?: '1')));

        return !in_array($flag, ['0', 'false', 'no', 'off'], true);
    }

    /**
     * @return string|null Plain French description, or null on skip / failure.
     */
    public static function suggest(string $titre, string $notes, ?string $categorieNom, string $portalContext = ''): ?string
    {
        if (!self::isEnabled()) {
            return null;
        }

        $apiKey = trim((string) (getenv('GROQ_API_KEY') ?: ''));
        if ($apiKey === '') {
            return null;
        }

        $titre = self::clip($titre, 150);
        $notes = self::clip($notes, 2800);
        $cat = $categorieNom !== null ? self::clip(trim($categorieNom), 200) : '';
        $portalContext = self::clip(trim($portalContext), 5200);

        if ($titre === '' && $notes === '' && $cat === '' && $portalContext === '') {
            return null;
        }

        $model = trim((string) (getenv('GROQ_MODEL') ?: 'llama-3.3-70b-versatile'));

        $ctx = "Catégorie de la nouvelle demande : " . ($cat !== '' ? $cat : 'non précisée') . "\n"
            . "Titre proposé (formulaire en cours) : " . ($titre !== '' ? $titre : '(vide)') . "\n"
            . "Notes / brouillon (formulaire en cours) :\n" . ($notes !== '' ? $notes : '(vide)');

        if ($portalContext !== '') {
            $ctx .= "\n\n--- Contexte dossier utilisateur (Mes demandes et autres modules du portail ; textes tronqués) ---\n"
                . $portalContext
                . "\n--- Fin contexte ---\n"
                . 'Rédige la description UNIQUEMENT pour la nouvelle demande du formulaire. Tu peux harmoniser le ton '
                . 'ou rappeler un fil conducteur si les notes du dossier sont pertinentes, sans fusionner plusieurs dossiers ni inventer de faits.';
        }

        $body = [
            'model' => $model,
            'temperature' => 0.35,
            'max_tokens' => 1100,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Tu aides à rédiger une demande de service administratif pour un portail universitaire français. '
                        . 'Réponds uniquement avec un JSON valide : {"description":"..."}. '
                        . 'Le champ description est en français, ton professionnel et clair, sans markdown, sans titre « Description », '
                        . 'structuré en quelques phrases courtes ou un court paragraphe : contexte, besoin précis, éventuelle urgence ou date utile si mentionné dans les notes. '
                        . 'N’invente pas de faits personnels absents des notes ou du contexte fourni ; tu peux reformuler pour la clarté. '
                        . 'Si un bloc « Mes demandes » ou portail est fourni, ne recopie pas mécaniquement les anciennes descriptions : sers-t’en seulement si utile pour la nouvelle demande.',
                ],
                ['role' => 'user', 'content' => $ctx],
            ],
        ];

        [$status, $raw, $transportError] = GroqClient::postChatCompletions($apiKey, $body, 35);
        if ($transportError !== '' || !is_string($raw) || $raw === '' || $status < 200 || $status >= 300) {
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

        $obj = self::decodeJsonObject($text);
        if ($obj === null) {
            return null;
        }

        $out = trim((string) ($obj['description'] ?? ''));
        if ($out === '') {
            return null;
        }

        return self::clip($out, 8000);
    }

    private static function clip(string $s, int $maxBytes): string
    {
        if ($maxBytes <= 0 || $s === '') {
            return '';
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            $len = mb_strlen($s, 'UTF-8');
            if ($len <= $maxBytes) {
                return $s;
            }

            return mb_substr($s, 0, $maxBytes, 'UTF-8');
        }

        return strlen($s) <= $maxBytes ? $s : substr($s, 0, $maxBytes);
    }

    /**
     * @return array<string, mixed>|null
     */
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
