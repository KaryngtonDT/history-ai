<?php

declare(strict_types=1);

namespace App\Domain\History;

use App\Domain\Pipeline\PipelineConfiguration;
use App\Domain\Video\VideoId;

interface ExecutionReplayContextInterface
{
    public function arm(VideoId $videoId, PipelineConfiguration $configuration): void;

    public function consume(VideoId $videoId): ?PipelineConfiguration;

    public function clear(VideoId $videoId): void;
}
