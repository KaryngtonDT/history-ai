<?php

declare(strict_types=1);

namespace App\Domain\ShadowExecutive;

final readonly class ExecutiveAgenda
{
    public function __construct(
        private ExecutiveTaskCollection $today,
        private ExecutiveTaskCollection $upcoming,
    ) {
    }

    public static function empty(): self
    {
        return new self(
            ExecutiveTaskCollection::empty(),
            ExecutiveTaskCollection::empty(),
        );
    }

    public function today(): ExecutiveTaskCollection
    {
        return $this->today;
    }

    public function upcoming(): ExecutiveTaskCollection
    {
        return $this->upcoming;
    }

    public function withToday(ExecutiveTaskCollection $today): self
    {
        return new self($today, $this->upcoming);
    }

    public function withUpcoming(ExecutiveTaskCollection $upcoming): self
    {
        return new self($this->today, $upcoming);
    }
}
