<?php

declare(strict_types=1);

namespace App\Domain\ShadowMemory;

use App\Domain\ShadowMemory\Exception\InvalidShadowMemoryException;

final readonly class MemoryTimeline
{
    public function __construct(
        private MemoryTimelineId $id,
        private string $scopeKey,
        private MemoryEntryCollection $entries,
        private KnowledgeItemCollection $knowledge,
        private KnowledgeConnectionCollection $connections,
        private bool $memoryEnabled,
    ) {
        if ('' === trim($scopeKey)) {
            throw new InvalidShadowMemoryException('Memory timeline scope cannot be empty.');
        }
    }

    public static function create(
        ?MemoryTimelineId $id = null,
        string $scopeKey = 'default',
    ): self {
        return new self(
            $id ?? MemoryTimelineId::generate(),
            trim($scopeKey),
            MemoryEntryCollection::empty()->append(
                MemoryEntry::record(
                    MemoryCategory::Milestone,
                    'Memory timeline started',
                    'Initial learner memory timeline created.',
                    MemoryImportance::Normal,
                ),
            ),
            KnowledgeItemCollection::empty(),
            KnowledgeConnectionCollection::empty(),
            true,
        );
    }

    public function id(): MemoryTimelineId
    {
        return $this->id;
    }

    public function scopeKey(): string
    {
        return $this->scopeKey;
    }

    public function entries(): MemoryEntryCollection
    {
        return $this->entries;
    }

    public function knowledge(): KnowledgeItemCollection
    {
        return $this->knowledge;
    }

    public function connections(): KnowledgeConnectionCollection
    {
        return $this->connections;
    }

    public function memoryEnabled(): bool
    {
        return $this->memoryEnabled;
    }

    public function addEntry(MemoryEntry $entry): self
    {
        return new self(
            $this->id,
            $this->scopeKey,
            $this->entries->append($entry),
            $this->knowledge,
            $this->connections,
            $this->memoryEnabled,
        );
    }

    public function withKnowledge(KnowledgeItemCollection $knowledge): self
    {
        return new self(
            $this->id,
            $this->scopeKey,
            $this->entries,
            $knowledge,
            $this->connections,
            $this->memoryEnabled,
        );
    }

    public function upsertKnowledge(KnowledgeItem $item): self
    {
        return $this->withKnowledge($this->knowledge->upsert($item));
    }

    public function withConnections(KnowledgeConnectionCollection $connections): self
    {
        return new self(
            $this->id,
            $this->scopeKey,
            $this->entries,
            $this->knowledge,
            $connections,
            $this->memoryEnabled,
        );
    }

    public function addConnection(KnowledgeConnection $connection): self
    {
        return $this->withConnections($this->connections->append($connection));
    }

    public function reset(): self
    {
        $timeline = self::create($this->id, $this->scopeKey);

        return $timeline->addEntry(
            MemoryEntry::record(
                MemoryCategory::Milestone,
                'Memory reset',
                'User reset the memory timeline.',
                MemoryImportance::Critical,
            ),
        );
    }
}
