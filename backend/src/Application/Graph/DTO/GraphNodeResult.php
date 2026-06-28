<?php

declare(strict_types=1);

namespace App\Application\Graph\DTO;

use App\Domain\Graph\GraphNode;

final readonly class GraphNodeResult
{
    public function __construct(
        public string $artifactId,
        public string $type,
        public string $title,
    ) {
    }

    public static function fromDomain(GraphNode $node): self
    {
        return new self(
            artifactId: $node->artifactId()->value,
            type: $node->artifactType()->value,
            title: $node->title(),
        );
    }
}
