<?php

declare(strict_types=1);

namespace App\Domain\ShadowMentor;

final readonly class RoadmapStep
{
    public function __construct(
        private RoadmapHorizon $horizon,
        private string $label,
        private string $detail,
        private int $order,
    ) {
    }

    public static function create(RoadmapHorizon $horizon, string $label, string $detail, int $order): self
    {
        return new self($horizon, $label, $detail, $order);
    }

    public function horizon(): RoadmapHorizon
    {
        return $this->horizon;
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
}
