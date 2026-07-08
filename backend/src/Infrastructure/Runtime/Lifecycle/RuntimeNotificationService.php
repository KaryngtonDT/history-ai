<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Lifecycle;

use App\Infrastructure\Storage\JsonFileStore;

final class RuntimeNotificationService
{
    private const string FILE = 'runtime-notifications.json';

    private const int MAX_ITEMS = 100;

    public function __construct(private readonly JsonFileStore $store)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function record(string $type, string $engineId, string $message, array $payload = []): void
    {
        $items = $this->allRaw();
        array_unshift($items, [
            'id' => bin2hex(random_bytes(8)),
            'type' => $type,
            'engineId' => $engineId,
            'message' => $message,
            'payload' => $payload,
            'read' => false,
            'createdAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ]);
        $items = array_slice($items, 0, self::MAX_ITEMS);
        $this->store->write(self::FILE, $items);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function list(?int $limit = 20): array
    {
        return array_slice($this->allRaw(), 0, $limit ?? 20);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function allRaw(): array
    {
        $data = $this->store->read(self::FILE);

        return is_array($data) ? $data : [];
    }
}
