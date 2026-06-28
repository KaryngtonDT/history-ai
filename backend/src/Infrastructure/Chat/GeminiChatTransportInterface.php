<?php

declare(strict_types=1);

namespace App\Infrastructure\Chat;

interface GeminiChatTransportInterface
{
    /**
     * @param array<string, mixed> $payload Request payload including `model` key for URL routing
     *
     * @return array<string, mixed>
     */
    public function generateContent(array $payload): array;
}
