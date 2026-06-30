<?php

declare(strict_types=1);

namespace App\Domain\Pipeline;

use App\Domain\Pipeline\Exception\InvalidPipelineConfigurationException;

final readonly class PipelineStageCollection
{
    /**
     * @param list<PipelineStage> $stages
     */
    public function __construct(private array $stages)
    {
        $seen = [];

        foreach ($this->stages as $stage) {
            $key = $stage->stage()->value;

            if (isset($seen[$key])) {
                throw new InvalidPipelineConfigurationException(sprintf(
                    'Duplicate pipeline stage "%s".',
                    $key,
                ));
            }

            $seen[$key] = true;
        }
    }

    /**
     * @param list<PipelineStage> $stages
     */
    public static function fromStages(array $stages): self
    {
        return new self(array_values($stages));
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<PipelineStage>
     */
    public function all(): array
    {
        return $this->stages;
    }

    public function count(): int
    {
        return count($this->stages);
    }

    public function findByType(PipelineStageType $type): ?PipelineStage
    {
        foreach ($this->stages as $stage) {
            if ($stage->stage() === $type) {
                return $stage;
            }
        }

        return null;
    }

    public function replace(PipelineStage $replacement): self
    {
        $updated = [];
        $replaced = false;

        foreach ($this->stages as $stage) {
            if ($stage->stage() === $replacement->stage()) {
                $updated[] = $replacement;
                $replaced = true;

                continue;
            }

            $updated[] = $stage;
        }

        if (!$replaced) {
            $updated[] = $replacement;
        }

        return new self($updated);
    }
}
