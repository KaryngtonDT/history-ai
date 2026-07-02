<?php

declare(strict_types=1);

namespace App\Domain\ShadowRelationship;

final readonly class RelationshipExplanation
{
    public function __construct(
        private string $summary,
        private string $reason,
        private string $source,
    ) {
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'summary' => $this->summary,
            'reason' => $this->reason,
            'source' => $this->source,
        ];
    }
}
