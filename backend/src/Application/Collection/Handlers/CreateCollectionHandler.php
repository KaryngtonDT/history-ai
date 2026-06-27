<?php

declare(strict_types=1);

namespace App\Application\Collection\Handlers;

use App\Application\Collection\Commands\CreateCollectionCommand;
use App\Application\Collection\DTO\CreateCollectionResult;
use App\Domain\Collection\Collection;
use App\Domain\Collection\CollectionDescription;
use App\Domain\Collection\CollectionId;
use App\Domain\Collection\CollectionName;
use App\Domain\Collection\CollectionRepositoryInterface;

final class CreateCollectionHandler
{
    public function __construct(
        private readonly CollectionRepositoryInterface $collectionRepository,
    ) {
    }

    public function __invoke(CreateCollectionCommand $command): CreateCollectionResult
    {
        $collection = Collection::create(
            CollectionId::generate(),
            new CollectionName($command->name),
            new CollectionDescription($command->description),
        );

        $this->collectionRepository->save($collection);

        return new CreateCollectionResult(
            $collection->id(),
            $collection->name(),
            $collection->description(),
            $collection->createdAt(),
        );
    }
}
