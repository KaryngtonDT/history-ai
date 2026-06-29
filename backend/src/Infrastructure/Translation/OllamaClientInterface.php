<?php

declare(strict_types=1);

namespace App\Infrastructure\Translation;

interface OllamaClientInterface
{
    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function generate(array $payload): array;
}
