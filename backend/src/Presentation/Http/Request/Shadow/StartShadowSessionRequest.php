<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request\Shadow;

use App\Presentation\Http\Request\Shadow\Exception\InvalidShadowRequestException;

final readonly class StartShadowSessionRequest
{
    public function __construct(
        public string $targetLanguage,
        public ?string $contentId = null,
        public ?string $conversationId = null,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        $targetLanguage = $payload['targetLanguage'] ?? null;

        if (!is_string($targetLanguage) || '' === trim($targetLanguage)) {
            throw new InvalidShadowRequestException('Target language is required.');
        }

        $contentId = $payload['contentId'] ?? null;
        $conversationId = $payload['conversationId'] ?? null;

        return new self(
            targetLanguage: trim($targetLanguage),
            contentId: is_string($contentId) ? $contentId : null,
            conversationId: is_string($conversationId) ? $conversationId : null,
        );
    }
}
