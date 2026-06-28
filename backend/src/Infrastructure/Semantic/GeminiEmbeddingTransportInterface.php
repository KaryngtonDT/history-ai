<?php

declare(strict_types=1);

namespace App\Infrastructure\Semantic;

interface GeminiEmbeddingTransportInterface
{
    /**
     * @param array<string, string> $headers
     * @param array<string, mixed>  $payload
     */
    public function post(string $url, array $headers, array $payload): string;
}
