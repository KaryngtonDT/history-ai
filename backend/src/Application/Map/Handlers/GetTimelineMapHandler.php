<?php

declare(strict_types=1);

namespace App\Application\Map\Handlers;

use App\Application\Map\DTO\TimelineMapResult;
use App\Application\Map\Queries\GetTimelineMapQuery;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Map\TimelinePlaceResolver;
use App\Domain\Timeline\TimelineParser;

final class GetTimelineMapHandler
{
    public function __construct(
        private readonly ArtifactRepositoryInterface $artifactRepository,
    ) {
    }

    public function __invoke(GetTimelineMapQuery $query): ?TimelineMapResult
    {
        $artifact = $this->artifactRepository->findById(new ArtifactId($query->artifactId));

        if (null === $artifact || ArtifactType::Timeline !== $artifact->type()) {
            return null;
        }

        $timeline = TimelineParser::parse($artifact->content()->value());
        $places = TimelinePlaceResolver::resolve($timeline);

        return TimelineMapResult::fromDomain($places);
    }
}
