<?php

declare(strict_types=1);

/**
 * Groq aide staff : lecture rapide d’une demande de service (traitement back-office).
 */
class DemandeStaffAiCheckService
{
    public static function isEnabled(): bool
    {
        $apiKey = trim((string) (getenv('GROQ_API_KEY') ?: ''));
        if ($apiKey === '') {
            return false;
        }

        $flag = strtolower(trim((string) (getenv('DEMANDE_STAFF_AI_CHECK_ENABLED') ?: '1')));

        return !in_array($flag, ['0', 'false', 'no', 'off'], true);
    }

    /**
     * @param array<string, mixed> $demande Ligne admin {@see DemandeDeService::findById}
     * @return array{verdict: string, points_cles: string, elements_manquants: string, suggestion_prochaine_etape: string}|null
     */
    public static function analyze(array $demande): ?array
    {
        if (!self::isEnabled()) {
            return null;
        }

        $apiKey = trim((string) (getenv('GROQ_API_KEY') ?: ''));
        if ($apiKey === '') {
            return null;
        }

        $cat = self::clip((string) ($demande['categorie_nom'] ?? ''), 120);
        $titre = self::clip((string) ($demande['titre'] ?? ''), 150);
        $desc = self::clip(trim(preg_replace('/\s+/u', ' ', (string) ($demande['description'] ?? ''))), 3600);
        $statut = (string) ($demande['statut'] ?? '');
        $soumise = (string) ($demande['soumise_le'] ?? '');

        if ($titre === '' && $desc === '') {
            return null;
        }

        $model = trim((string) (getenv('GROQ_MODEL') ?: 'llama-3.3-70b-versatile'));

        $user = "Catégorie : {$cat}\nStatut dossier : {$statut}\nSoumise le : {$soumise}\nTitre : {$titre}\nDescription :\n{$desc}";

        $body = [
            'model' => $model,
            'temperature' => 0.2,
            'max_tokens' => 700,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Tu es un assistant interne pour le personnel administratif d’une université française. '
                        . 'Tu analyses une demande de service (texte déjà saisi par l’usager). '
                        . 'Réponds uniquement avec un JSON valide aux clés : '
                        . '"verdict" (une des valeurs : clair, a_preciser, insuffisant), '
                        . '"points_cles" (court paragraphe en français), '
                        . '"elements_manquants" (texte court : puces ou phrases, ou « aucun » — une seule chaîne, pas un tableau JSON), '
                        . '"suggestion_prochaine_etape" (pour le traitement humain : quoi demander / vérifier / classer). '
                        . 'Pas de markdown. Ne pas inventer de pièces ou de faits absents du texte.',
                ],
                ['role' => 'user', 'content' => $user],
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

        $verdictRaw = $obj['verdict'] ?? '';
        if (is_array($verdictRaw)) {
            $verdictRaw = reset($verdictRaw);
        }
        $verdict = trim((string) $verdictRaw);
        if ($verdict === '') {
            $verdict = 'a_preciser';
        }

        return [
            'verdict' => $verdict,
            'points_cles' => self::normalizeAiTextField($obj['points_cles'] ?? null),
            'elements_manquants' => self::normalizeAiTextField($obj['elements_manquants'] ?? null),
            'suggestion_prochaine_etape' => self::normalizeAiTextField($obj['suggestion_prochaine_etape'] ?? null),
        ];
    }

    /**
     * Le modèle renvoie parfois une liste JSON au lieu d'une chaîne ; éviter le cast PHP « Array ».
     */
    private static function normalizeAiTextField(mixed $v): string
    {
        if ($v === null) {
            return '';
        }
        if (is_string($v)) {
            return trim($v);
        }
        if (is_int($v) || is_float($v)) {
            return trim((string) $v);
        }
        if (is_array($v)) {
            $parts = [];
            foreach ($v as $item) {
                if (is_string($item)) {
                    $t = trim($item);
                    if ($t !== '') {
                        $parts[] = $t;
                    }
                } elseif (is_int($item) || is_float($item)) {
                    $parts[] = (string) $item;
                } elseif (is_array($item)) {
                    $nested = self::normalizeAiTextField($item);
                    if ($nested !== '') {
                        $parts[] = $nested;
                    }
                }
            }

            return $parts === [] ? '' : implode("\n• ", $parts);
        }

        return trim((string) $v);
    }

    private static function clip(string $s, int $max): string
    {
        if ($max <= 0) {
            return '';
        }
        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            return mb_strlen($s) <= $max ? $s : mb_substr($s, 0, $max - 1) . '…';
        }

        return strlen($s) <= $max ? $s : substr($s, 0, $max - 1) . '…';
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
