<?php

declare(strict_types=1);

namespace App\Infrastructure\Chat;

use JsonException;
use RuntimeException;

final class CurlGeminiChatTransport implements GeminiChatTransportInterface
{
    private const string API_BASE_URL = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct(
        private readonly string $apiKey,
    ) {
    }

    public function generateContent(array $payload): array
    {
        if ('' === trim($this->apiKey)) {
            throw new RuntimeException('GEMINI_API_KEY is not configured.');
        }

        $model = $payload['model'] ?? null;
        if (!is_string($model) || '' === trim($model)) {
            throw new RuntimeException('Gemini chat request requires a model.');
        }

        /** @var array<string, mixed> $requestBody */
        $requestBody = $payload;
        unset($requestBody['model']);

        $url = sprintf('%s/models/%s:generateContent', self::API_BASE_URL, $model);
        $body = json_encode($requestBody, JSON_THROW_ON_ERROR);
        $handle = curl_init($url);

        if (false === $handle) {
            throw new RuntimeException('Unable to initialize Gemini chat request.');
        }

        curl_setopt_array($handle, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                sprintf('x-goog-api-key: %s', $this->apiKey),
            ],
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
        ]);

        $responseBody = curl_exec($handle);
        $statusCode = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $error = curl_error($handle);
        curl_close($handle);

        if ('' !== $error) {
            throw new RuntimeException(sprintf('Gemini chat request failed: %s', $error));
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new RuntimeException(sprintf(
                'Gemini chat request failed with HTTP %d: %s',
                $statusCode,
                is_string($responseBody) ? $responseBody : '',
            ));
        }

        if (!is_string($responseBody) || '' === $responseBody) {
            throw new RuntimeException('Gemini chat request returned an empty response.');
        }

        try {
            /** @var mixed $decoded */
            $decoded = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Gemini chat request returned invalid JSON.', 0, $exception);
        }

        if (!is_array($decoded)) {
            throw new RuntimeException('Gemini chat request returned an unexpected response payload.');
        }

        return $decoded;
    }
}
