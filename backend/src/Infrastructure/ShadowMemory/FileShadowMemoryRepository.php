<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowMemory;

use App\Domain\ShadowMemory\MemoryTimeline;
use App\Domain\ShadowMemory\MemoryTimelineId;
use App\Domain\ShadowMemory\ShadowMemoryRepositoryInterface;
use App\Infrastructure\Storage\JsonFileStore;

final class FileShadowMemoryRepository implements ShadowMemoryRepositoryInterface
{
    public function __construct(
        private readonly JsonFileStore $store,
        private readonly ShadowMemoryPersistenceMapper $mapper,
    ) {
    }

    public function findByScope(string $scopeKey): ?MemoryTimeline
    {
        foreach ($this->store->listJsonFiles() as $filename) {
            $timeline = $this->read($filename);

            if (null !== $timeline && $timeline->scopeKey() === $scopeKey) {
                return $timeline;
            }
        }

        return null;
    }

    public function findById(MemoryTimelineId $id): ?MemoryTimeline
    {
        return $this->read($id->value.'.json');
    }

    public function save(MemoryTimeline $timeline): void
    {
        $this->store->write(
            $timeline->id()->value.'.json',
            $this->mapper->toArray($timeline),
        );
    }

    private function read(string $filename): ?MemoryTimeline
    {
        $data = $this->store->read($filename);

        if (null === $data) {
            return null;
        }

        return $this->mapper->fromJson(json_encode($data, JSON_THROW_ON_ERROR));
    }
}
