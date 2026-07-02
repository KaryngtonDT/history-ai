<?php

declare(strict_types=1);

namespace App\Domain\ShadowExecutive;

final readonly class ExecutiveRecommendation
{
    public function __construct(
        private string $id,
        private DecisionType $type,
        private string $title,
        private string $detail,
        private ExecutivePriority $priority,
        private ?string $conceptKey,
        private ?string $resourceId,
    ) {
    }

    public static function create(
        DecisionType $type,
        string $title,
        string $detail = '',
        ExecutivePriority $priority = ExecutivePriority::Normal,
        ?string $conceptKey = null,
        ?string $resourceId = null,
    ): self {
        return new self(
            bin2hex(random_bytes(8)),
            $type,
            $title,
            $detail,
            $priority,
            $conceptKey,
            $resourceId,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function type(): DecisionType
    {
        return $this->type;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function detail(): string
    {
        return $this->detail;
    }

    public function priority(): ExecutivePriority
    {
        return $this->priority;
    }

    public function conceptKey(): ?string
    {
        return $this->conceptKey;
    }

    public function resourceId(): ?string
    {
        return $this->resourceId;
    }
}
