<?php

declare(strict_types=1);

namespace App\Domain\ShadowExecutive;

use App\Domain\ShadowExecutive\Exception\InvalidShadowExecutiveException;

final readonly class ExecutiveDecision
{
    /** @param list<string> $evidence */
    /** @param list<DecisionImpact> $impacts */
    public function __construct(
        private string $id,
        private DecisionType $type,
        private DecisionStatus $status,
        private ExecutivePriority $priority,
        private string $title,
        private string $summary,
        private ExecutiveReason $reason,
        private array $evidence,
        private array $impacts,
        private ?string $linkedGoalId,
        private ?string $linkedConceptKey,
        private ?string $linkedResourceId,
        private ?ExecutiveConstraint $constraint,
    ) {
    }

    /** @param list<string> $evidence */
    /** @param list<DecisionImpact> $impacts */
    public static function create(
        DecisionType $type,
        string $title,
        string $summary,
        ExecutiveReason $reason,
        ExecutivePriority $priority = ExecutivePriority::Normal,
        array $evidence = [],
        array $impacts = [],
        ?string $linkedGoalId = null,
        ?string $linkedConceptKey = null,
        ?string $linkedResourceId = null,
    ): self {
        return new self(
            bin2hex(random_bytes(8)),
            $type,
            DecisionStatus::Pending,
            $priority,
            $title,
            $summary,
            $reason,
            $evidence,
            $impacts,
            $linkedGoalId,
            $linkedConceptKey,
            $linkedResourceId,
            null,
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

    public function status(): DecisionStatus
    {
        return $this->status;
    }

    public function priority(): ExecutivePriority
    {
        return $this->priority;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function summary(): string
    {
        return $this->summary;
    }

    public function reason(): ExecutiveReason
    {
        return $this->reason;
    }

    /** @return list<string> */
    public function evidence(): array
    {
        return $this->evidence;
    }

    /** @return list<DecisionImpact> */
    public function impacts(): array
    {
        return $this->impacts;
    }

    public function linkedGoalId(): ?string
    {
        return $this->linkedGoalId;
    }

    public function linkedConceptKey(): ?string
    {
        return $this->linkedConceptKey;
    }

    public function linkedResourceId(): ?string
    {
        return $this->linkedResourceId;
    }

    public function constraint(): ?ExecutiveConstraint
    {
        return $this->constraint;
    }

    public function approve(): self
    {
        return $this->withStatus(DecisionStatus::Approved);
    }

    public function reject(): self
    {
        return $this->withStatus(DecisionStatus::Rejected);
    }

    public function defer(): self
    {
        return $this->withStatus(DecisionStatus::Deferred);
    }

    public function ignore(ExecutiveConstraint $constraint): self
    {
        if (DecisionStatus::Pending !== $this->status) {
            throw new InvalidShadowExecutiveException('Only pending decisions can be ignored.');
        }

        return new self(
            $this->id,
            $this->type,
            DecisionStatus::Ignored,
            $this->priority,
            $this->title,
            $this->summary,
            $this->reason,
            $this->evidence,
            $this->impacts,
            $this->linkedGoalId,
            $this->linkedConceptKey,
            $this->linkedResourceId,
            $constraint,
        );
    }

    private function withStatus(DecisionStatus $status): self
    {
        if (DecisionStatus::Pending !== $this->status) {
            throw new InvalidShadowExecutiveException('Only pending decisions can change status.');
        }

        return new self(
            $this->id,
            $this->type,
            $status,
            $this->priority,
            $this->title,
            $this->summary,
            $this->reason,
            $this->evidence,
            $this->impacts,
            $this->linkedGoalId,
            $this->linkedConceptKey,
            $this->linkedResourceId,
            $this->constraint,
        );
    }
}
