<?php

declare(strict_types=1);

namespace App\Application\Content\Handlers;

use App\Application\Content\DTO\ContentListItem;
use App\Application\Content\DTO\ListContentsResult;
use App\Application\Content\Queries\ListContentsQuery;
use App\Domain\Content\ContentRepositoryInterface;

final class ListContentsHandler
{
    public function __construct(
        private readonly ContentRepositoryInterface $contentRepository,
    ) {
    }

    public function __invoke(ListContentsQuery $query): ListContentsResult
    {
        $items = array_map(
            static fn ($content): ContentListItem => ContentListItem::fromDomain($content),
            $this->contentRepository->findAll(),
        );

        return new ListContentsResult($items);
    }
}
