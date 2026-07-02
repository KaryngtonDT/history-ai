<?php

declare(strict_types=1);

namespace App\Infrastructure\Shadow;

use App\Domain\Shadow\ShadowSession;
use App\Domain\Shadow\ShadowSessionId;
use App\Domain\Shadow\ShadowSessionRepositoryInterface;
use App\Domain\Video\VideoId;
use App\Infrastructure\Storage\JsonFileStore;

final class FileShadowSessionRepository implements ShadowSessionRepositoryInterface
{
    public function __construct(
        private readonly JsonFileStore $store,
        private readonly ShadowSessionPersistenceMapper $mapper,
    ) {
    }

    public function save(ShadowSession $session): void
    {
        $this->store->write(
            $this->filenameForId($session->id()->value),
            $this->mapper->toArray($session),
        );
    }

    public function findById(ShadowSessionId $id): ?ShadowSession
    {
        return $this->readSession($this->filenameForId($id->value));
    }

    public function findByVideoId(VideoId $videoId): array
    {
        $sessions = [];

        foreach ($this->store->listJsonFiles() as $filename) {
            $session = $this->readSession($filename);

            if (null !== $session && $session->videoId()->value === $videoId->value) {
                $sessions[] = $session;
            }
        }

        return $sessions;
    }

    public function clear(): void
    {
        foreach ($this->store->listJsonFiles() as $filename) {
            $this->store->delete($filename);
        }
    }

    private function readSession(string $filename): ?ShadowSession
    {
        $data = $this->store->read($filename);

        if (null === $data) {
            return null;
        }

        return $this->mapper->fromJson(json_encode($data, JSON_THROW_ON_ERROR));
    }

    private function filenameForId(string $id): string
    {
        return $id . '.json';
    }
}
