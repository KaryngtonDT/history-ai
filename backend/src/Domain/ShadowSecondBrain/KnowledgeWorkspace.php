<?php

declare(strict_types=1);

namespace App\Domain\ShadowSecondBrain;

use App\Domain\ShadowSecondBrain\Exception\InvalidShadowSecondBrainException;

final readonly class KnowledgeWorkspace
{
    public function __construct(
        private KnowledgeWorkspaceId $id,
        private string $scopeKey,
        private KnowledgeCollection $entries,
        private KnowledgeBookmarkCollection $bookmarks,
        private KnowledgeNoteCollection $notes,
        private KnowledgeTimelineCollection $timeline,
        private KnowledgeStatistics $statistics,
        private bool $workspaceEnabled,
        private ?\DateTimeImmutable $lastSyncedAt,
    ) {
        if ('' === trim($scopeKey)) {
            throw new InvalidShadowSecondBrainException('Knowledge workspace scope cannot be empty.');
        }
    }

    public static function create(
        ?KnowledgeWorkspaceId $id = null,
        string $scopeKey = 'default',
    ): self {
        return new self(
            $id ?? KnowledgeWorkspaceId::generate(),
            trim($scopeKey),
            KnowledgeCollection::empty(),
            KnowledgeBookmarkCollection::empty(),
            KnowledgeNoteCollection::empty(),
            KnowledgeTimelineCollection::empty(),
            KnowledgeStatistics::empty(),
            true,
            null,
        );
    }

    public function id(): KnowledgeWorkspaceId
    {
        return $this->id;
    }

    public function scopeKey(): string
    {
        return $this->scopeKey;
    }

    public function entries(): KnowledgeCollection
    {
        return $this->entries;
    }

    public function bookmarks(): KnowledgeBookmarkCollection
    {
        return $this->bookmarks;
    }

    public function notes(): KnowledgeNoteCollection
    {
        return $this->notes;
    }

    public function timeline(): KnowledgeTimelineCollection
    {
        return $this->timeline;
    }

    public function statistics(): KnowledgeStatistics
    {
        return $this->statistics;
    }

    public function workspaceEnabled(): bool
    {
        return $this->workspaceEnabled;
    }

    public function lastSyncedAt(): ?\DateTimeImmutable
    {
        return $this->lastSyncedAt;
    }

    public function findEntry(string $conceptKey): ?KnowledgeEntry
    {
        return $this->entries->findByKey($conceptKey);
    }

    public function addBookmark(KnowledgeBookmark $bookmark): self
    {
        return $this->replace(bookmarks: $this->bookmarks->upsert($bookmark));
    }

    public function removeBookmark(string $id): self
    {
        if (null === $this->bookmarks->find($id)) {
            throw new InvalidShadowSecondBrainException('Bookmark not found.');
        }

        return $this->replace(bookmarks: $this->bookmarks->remove($id));
    }

    public function addNote(KnowledgeNote $note): self
    {
        return $this->replace(notes: $this->notes->append($note));
    }

    public function withEntries(KnowledgeCollection $entries): self
    {
        return $this->replace(entries: $entries);
    }

    public function withBookmarks(KnowledgeBookmarkCollection $bookmarks): self
    {
        return $this->replace(bookmarks: $bookmarks);
    }

    public function withNotes(KnowledgeNoteCollection $notes): self
    {
        return $this->replace(notes: $notes);
    }

    public function withTimeline(KnowledgeTimelineCollection $timeline): self
    {
        return $this->replace(timeline: $timeline);
    }

    public function withStatistics(KnowledgeStatistics $statistics): self
    {
        return $this->replace(statistics: $statistics);
    }

    public function withWorkspaceEnabled(bool $workspaceEnabled): self
    {
        return $this->replace(workspaceEnabled: $workspaceEnabled);
    }

    public function withLastSyncedAt(?\DateTimeImmutable $lastSyncedAt): self
    {
        return $this->replace(lastSyncedAt: $lastSyncedAt);
    }

    public function reset(): self
    {
        return self::create($this->id, $this->scopeKey);
    }

    private function replace(
        ?KnowledgeCollection $entries = null,
        ?KnowledgeBookmarkCollection $bookmarks = null,
        ?KnowledgeNoteCollection $notes = null,
        ?KnowledgeTimelineCollection $timeline = null,
        ?KnowledgeStatistics $statistics = null,
        ?bool $workspaceEnabled = null,
        ?\DateTimeImmutable $lastSyncedAt = null,
    ): self {
        return new self(
            $this->id,
            $this->scopeKey,
            $entries ?? $this->entries,
            $bookmarks ?? $this->bookmarks,
            $notes ?? $this->notes,
            $timeline ?? $this->timeline,
            $statistics ?? $this->statistics,
            $workspaceEnabled ?? $this->workspaceEnabled,
            $lastSyncedAt ?? $this->lastSyncedAt,
        );
    }
}
