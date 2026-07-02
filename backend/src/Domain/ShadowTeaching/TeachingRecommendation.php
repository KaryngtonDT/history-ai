<?php

declare(strict_types=1);

namespace App\Domain\ShadowTeaching;

final readonly class TeachingRecommendation
{
    public function __construct(
        private string $message,
        private string $action,
        private ?string $objectiveKey,
    ) {
    }

    public static function empty(): self
    {
        return new self('Teaching plan ready.', 'continue', null);
    }

    public function message(): string
    {
        return $this->message;
    }

    public function action(): string
    {
        return $this->action;
    }

    public function objectiveKey(): ?string
    {
        return $this->objectiveKey;
    }
}
