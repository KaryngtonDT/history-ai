<?php

declare(strict_types=1);

namespace App\Domain\ShadowExecutive;

final readonly class ExecutiveTask
{
    public function __construct(
        private string $id,
        private ExecutiveTaskType $type,
        private string $label,
        private string $detail,
        private int $order,
        private ?\DateTimeImmutable $scheduledAt,
    ) {
    }

    public static function create(
        ExecutiveTaskType $type,
        string $label,
        string $detail = '',
        int $order = 0,
        ?\DateTimeImmutable $scheduledAt = null,
    ): self {
        return new self(
            bin2hex(random_bytes(8)),
            $type,
            $label,
            $detail,
            $order,
            $scheduledAt,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function type(): ExecutiveTaskType
    {
        return $this->type;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function detail(): string
    {
        return $this->detail;
    }

    public function order(): int
    {
        return $this->order;
    }

    public function scheduledAt(): ?\DateTimeImmutable
    {
        return $this->scheduledAt;
    }
}
