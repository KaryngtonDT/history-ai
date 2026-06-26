<?php

declare(strict_types=1);

namespace App\Application\Content\Handlers;

use App\Application\Content\Commands\CreateContentCommand;
use App\Application\Content\DTO\CreateContentResult;
use App\Domain\Content\Content;
use App\Domain\Content\ContentId;
use App\Domain\Content\ContentRepositoryInterface;
use App\Domain\Content\ContentTitle;

final class CreateContentHandler
{
    public function __construct(
        private readonly ContentRepositoryInterface $contentRepository,
    ) {
    }

    public function __invoke(CreateContentCommand $command): CreateContentResult
    {
        $content = Content::create(
            ContentId::generate(),
            new ContentTitle($command->title),
            $command->sourceType,
        );

        $this->contentRepository->save($content);

        return new CreateContentResult($content->id());
    }
}
