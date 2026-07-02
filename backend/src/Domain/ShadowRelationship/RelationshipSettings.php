<?php

declare(strict_types=1);

namespace App\Domain\ShadowRelationship;

final readonly class RelationshipSettings
{
    public function __construct(
        private bool $showHypotheses,
        private bool $showTimeline,
        private bool $allowConversationalUpdates,
    ) {
    }

    public static function default(): self
    {
        return new self(true, true, true);
    }

    public function showHypotheses(): bool
    {
        return $this->showHypotheses;
    }

    public function showTimeline(): bool
    {
        return $this->showTimeline;
    }

    public function allowConversationalUpdates(): bool
    {
        return $this->allowConversationalUpdates;
    }
}
