<?php

declare(strict_types=1);

namespace App\Infrastructure\Shadow\SessionLearning;

use App\Domain\Shadow\SessionLearning\SessionLearningState;
use App\Domain\Shadow\SessionLearning\SessionLearningStateRepositoryInterface;
use App\Domain\Shadow\ShadowSessionId;
use App\Infrastructure\Storage\JsonFileStore;
use JsonException;
use RuntimeException;

final class FileSessionLearningStateRepository implements SessionLearningStateRepositoryInterface
{
    public function __construct(
        private readonly JsonFileStore $store,
        private readonly SessionLearningStatePersistenceMapper $mapper,
    ) {
    }

    public function save(SessionLearningState $state): void
    {
        $this->store->write(
            $this->filenameForId($state->sessionId()->value),
            $this->mapper->toArray($state),
        );
    }

    public function findBySessionId(ShadowSessionId $sessionId): ?SessionLearningState
    {
        $data = $this->store->read($this->filenameForId($sessionId->value));

        if (null === $data) {
            return null;
        }

        try {
            return $this->mapper->fromJson(json_encode($data, JSON_THROW_ON_ERROR));
        } catch (JsonException $exception) {
            throw new RuntimeException('Unable to read session learning state.', 0, $exception);
        }
    }

    public function deleteBySessionId(ShadowSessionId $sessionId): void
    {
        $this->store->delete($this->filenameForId($sessionId->value));
    }

    private function filenameForId(string $id): string
    {
        return $id . '.json';
    }
}
