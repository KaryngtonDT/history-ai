<?php

declare(strict_types=1);

namespace App\Infrastructure\Semantic;

use RuntimeException;

final class CurlGeminiEmbeddingTransport implements GeminiEmbeddingTransportInterface
{
    public function post(string $url, array $headers, array $payload): string
    {
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $handle = curl_init($url);

        if (false === $handle) {
            throw new RuntimeException('Unable to initialize Gemini embedding request.');
        }

        /** @var list<string> $headerLines */
        $headerLines = [];
        foreach ($headers as $name => $value) {
            $headerLines[] = sprintf('%s: %s', $name, $value);
        }

        curl_setopt_array($handle, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headerLines,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
        ]);

        $responseBody = curl_exec($handle);
        $statusCode = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $error = curl_error($handle);
        curl_close($handle);

        if ('' !== $error) {
            throw new RuntimeException(sprintf('Gemini embedding request failed: %s', $error));
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new RuntimeException(sprintf(
                'Gemini embedding request failed with HTTP %d: %s',
                $statusCode,
                is_string($responseBody) ? $responseBody : '',
            ));
        }

        if (!is_string($responseBody) || '' === $responseBody) {
            throw new RuntimeException('Gemini embedding request returned an empty response.');
        }

        return $responseBody;
    }
}
