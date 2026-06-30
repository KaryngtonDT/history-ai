<?php

declare(strict_types=1);

namespace App\Domain\Workspace;

final readonly class ProjectCollection
{
    /** @var list<Project> */
    private array $projects;

    /**
     * @param list<Project> $projects
     */
    public function __construct(array $projects = [])
    {
        $this->projects = array_values($projects);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<Project>
     */
    public function all(): array
    {
        return $this->projects;
    }

    public function count(): int
    {
        return count($this->projects);
    }

    public function isEmpty(): bool
    {
        return [] === $this->projects;
    }

    public function append(Project $project): self
    {
        return new self([...$this->projects, $project]);
    }
}
