<?php

declare(strict_types=1);

namespace App\Application\Library\Handlers;

use App\Application\Library\Commands\AddLibraryItemCommand;
use App\Application\Library\DTO\AddLibraryItemResult;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Content\ContentId;
use App\Domain\Library\LibraryItem;
use App\Domain\Library\LibraryItemId;
use App\Domain\Library\LibraryItemRepositoryInterface;
use App\Domain\Library\LibraryItemTitle;

final class AddLibraryItemHandler
{
    public function __construct(
        private readonly LibraryItemRepositoryInterface $libraryItemRepository,
    ) {
    }

    public function __invoke(AddLibraryItemCommand $command): AddLibraryItemResult
    {
        $item = LibraryItem::create(
            LibraryItemId::generate(),
            new ContentId($command->contentId),
            new ArtifactId($command->artifactId),
            $command->type,
            new LibraryItemTitle($command->title),
        );

        $this->libraryItemRepository->save($item);

        return new AddLibraryItemResult(
            $item->id(),
            $item->type(),
            $item->title(),
            $item->createdAt(),
        );
    }
}
