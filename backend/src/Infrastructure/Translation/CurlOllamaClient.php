<?php

declare(strict_types=1);

namespace App\Infrastructure\Translation;

use App\Infrastructure\Translation\Exception\OllamaTranslationException;
use JsonException;

final class CurlOllamaClient implements OllamaClientInterface
{
    public function __construct(
        private readonly string $baseUrl,
    ) {
    }

    public function generate(array $payload): array
    {
        $url = rtrim($this->baseUrl, '/').'/api/generate';
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $handle = curl_init($url);

        if (false === $handle) {
            throw new OllamaTranslationException('Unable to initialize Ollama request.');
        }

        curl_setopt_array($handle, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 900,
        ]);

        $responseBody = curl_exec($handle);
        $statusCode = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $error = curl_error($handle);
        curl_close($handle);

        if ('' !== $error) {
            throw new OllamaTranslationException(sprintf('Ollama request failed: %s', $error));
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new OllamaTranslationException(sprintf(
                'Ollama request failed with HTTP %d: %s',
                $statusCode,
                is_string($responseBody) ? $responseBody : '',
            ));
        }

        if (!is_string($responseBody) || '' === $responseBody) {
            throw new OllamaTranslationException('Ollama request returned an empty response.');
        }

        try {
            /** @var mixed $decoded */
            $decoded = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new OllamaTranslationException('Ollama request returned invalid JSON.', 0, $exception);
        }

        if (!is_array($decoded)) {
            throw new OllamaTranslationException('Ollama request returned an unexpected payload.');
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }
}
