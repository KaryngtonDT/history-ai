<?php

declare(strict_types=1);

namespace App\Application\Shadow\Handlers;

use App\Application\Shadow\DTO\WatchContextResult;
use App\Application\Shadow\Queries\GetShadowContextQuery;
use App\Application\Shadow\ShadowContextFactory;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;

final class GetShadowContextHandler
{
    public function __construct(
        private readonly ShadowContextFactory $shadowContextFactory,
    ) {
    }

    public function __invoke(GetShadowContextQuery $query): WatchContextResult
    {
        try {
            new VideoId($query->videoId);
        } catch (InvalidVideoIdException) {
            throw new InvalidShadowSessionException('Video id must be a valid UUID.');
        }

        $context = $this->shadowContextFactory->create(
            $query->videoId,
            $query->time,
            $query->language,
            $query->conversationId,
        );

        return WatchContextResult::fromWatchContext($context);
    }
}
