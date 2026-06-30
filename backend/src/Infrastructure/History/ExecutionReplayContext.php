<?php

declare(strict_types=1);

namespace App\Infrastructure\History;

use App\Domain\History\ExecutionReplayContextInterface;
use App\Domain\Pipeline\PipelineConfiguration;
use App\Domain\Video\VideoId;

final class ExecutionReplayContext implements ExecutionReplayContextInterface
{
    /** @var array<string, PipelineConfiguration> */
    private array $configurations = [];

    public function arm(VideoId $videoId, PipelineConfiguration $configuration): void
    {
        $this->configurations[$videoId->value] = $configuration;
    }

    public function consume(VideoId $videoId): ?PipelineConfiguration
    {
        $configuration = $this->configurations[$videoId->value] ?? null;

        if (null !== $configuration) {
            unset($this->configurations[$videoId->value]);
        }

        return $configuration;
    }

    public function clear(VideoId $videoId): void
    {
        unset($this->configurations[$videoId->value]);
    }
}
