<?php

declare(strict_types=1);

namespace App\Application\Timeline\Handlers;

use App\Application\Timeline\DTO\TimelineResult;
use App\Application\Timeline\Queries\GetTimelineQuery;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Timeline\TimelineParser;

final class GetTimelineHandler
{
    public function __construct(
        private readonly ArtifactRepositoryInterface $artifactRepository,
    ) {
    }

    public function __invoke(GetTimelineQuery $query): ?TimelineResult
    {
        $artifact = $this->artifactRepository->findById(new ArtifactId($query->artifactId));

        if (null === $artifact || ArtifactType::Timeline !== $artifact->type()) {
            return null;
        }

        $timeline = TimelineParser::parse($artifact->content()->value());

        return TimelineResult::fromDomain($timeline);
    }
}
