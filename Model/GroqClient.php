<?php

declare(strict_types=1);

/**
 * Groq OpenAI-compatible Chat Completions API.
 *
 * @see https://console.groq.com/docs/openai
 */
class GroqClient
{
    private const CHAT_COMPLETIONS_URL = 'https://api.groq.com/openai/v1/chat/completions';

    /**
     * @param array<string, mixed> $requestBody Must include "model" and "messages"
     * @return array{0: int, 1: string, 2: string} HTTP status, raw response body, transport error (empty if OK)
     */
    public static function postChatCompletions(string $apiKey, array $requestBody, int $timeoutSeconds = 25): array
    {
        $body = json_encode($requestBody, JSON_UNESCAPED_UNICODE);
        if (!is_string($body)) {
            return [0, '', 'JSON encode error'];
        }

        $apiKey = trim($apiKey);
        if ($apiKey === '') {
            return [0, '', 'API key empty'];
        }

        if (function_exists('curl_init')) {
            $ch = curl_init(self::CHAT_COMPLETIONS_URL);
            if ($ch === false) {
                return [0, '', 'curl_init failed'];
            }
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey,
                ],
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_TIMEOUT => $timeoutSeconds,
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
                'timeout' => $timeoutSeconds,
                'ignore_errors' => true,
            ],
        ]);

        $response = @file_get_contents(self::CHAT_COMPLETIONS_URL, false, $context);
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
}
