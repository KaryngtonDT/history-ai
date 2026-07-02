<?php

declare(strict_types=1);

namespace App\Application\ShadowSecondBrain;

use App\Application\ShadowExecutive\ExecutiveCoordinator;
use App\Domain\ShadowSecondBrain\Exception\InvalidShadowSecondBrainException;
use App\Domain\ShadowSecondBrain\KnowledgeBookmark;
use App\Domain\ShadowSecondBrain\KnowledgeNote;
use App\Domain\ShadowSecondBrain\KnowledgeSourceType;
use App\Domain\ShadowSecondBrain\KnowledgeWorkspace;
use App\Domain\ShadowSecondBrain\KnowledgeWorkspaceRepositoryInterface;

final class WorkspaceBuilder
{
    public function __construct(
        private readonly KnowledgeWorkspaceRepositoryInterface $repository,
        private readonly ExecutiveCoordinator $executiveCoordinator,
        private readonly KnowledgeAggregator $aggregator,
        private readonly DuplicateConceptResolver $duplicateResolver,
        private readonly KnowledgeMergeEngine $mergeEngine,
        private readonly KnowledgeStatisticsBuilder $statisticsBuilder,
    ) {
    }

    public function getWorkspace(string $scopeKey = 'default'): KnowledgeWorkspace
    {
        return $this->repository->findByScope($scopeKey) ?? KnowledgeWorkspace::create(scopeKey: $scopeKey);
    }

    public function syncWorkspace(string $scopeKey = 'default'): KnowledgeWorkspace
    {
        $this->executiveCoordinator->syncPlan($scopeKey);
        $existing = $this->getWorkspace($scopeKey);
        $aggregated = $this->aggregator->aggregate($scopeKey);
        $entries = $aggregated['entries'];
        $timeline = $aggregated['timeline'];

        $duplicates = $this->duplicateResolver->resolve($entries);

        if ([] !== $duplicates) {
            $entries = $this->mergeEngine->merge($entries, $duplicates);
        }

        $statistics = $this->statisticsBuilder->build($entries, $timeline);

        $workspace = $existing
            ->withEntries($entries)
            ->withTimeline($timeline)
            ->withStatistics($statistics)
            ->withLastSyncedAt(new \DateTimeImmutable());

        $this->repository->save($workspace);

        return $workspace;
    }

    public function rebuild(string $scopeKey = 'default'): KnowledgeWorkspace
    {
        return $this->syncWorkspace($scopeKey);
    }

    /** @param array<string, mixed> $payload */
    public function addBookmark(string $scopeKey, array $payload): KnowledgeWorkspace
    {
        $label = is_string($payload['label'] ?? null) ? trim($payload['label']) : '';

        if ('' === $label) {
            throw new InvalidShadowSecondBrainException('Bookmark label is required.');
        }

        $bookmark = new KnowledgeBookmark(
            is_string($payload['id'] ?? null) ? $payload['id'] : bin2hex(random_bytes(8)),
            $label,
            is_array($payload['tags'] ?? null) ? array_values(array_filter($payload['tags'], 'is_string')) : [],
            is_string($payload['conceptKey'] ?? null) ? $payload['conceptKey'] : null,
            is_string($payload['resourceType'] ?? null) ? KnowledgeSourceType::tryFrom($payload['resourceType']) : null,
            is_string($payload['resourceId'] ?? null) ? $payload['resourceId'] : null,
        );

        $workspace = $this->getWorkspace($scopeKey)->addBookmark($bookmark);
        $this->repository->save($workspace);

        return $workspace;
    }

    /** @param array<string, mixed> $payload */
    public function addNote(string $scopeKey, array $payload): KnowledgeWorkspace
    {
        $body = is_string($payload['body'] ?? null) ? trim($payload['body']) : '';

        if ('' === $body) {
            throw new InvalidShadowSecondBrainException('Note body is required.');
        }

        $note = new KnowledgeNote(
            is_string($payload['id'] ?? null) ? $payload['id'] : bin2hex(random_bytes(8)),
            $body,
            new \DateTimeImmutable(),
            is_string($payload['conceptKey'] ?? null) ? $payload['conceptKey'] : null,
        );

        $workspace = $this->getWorkspace($scopeKey)->addNote($note);
        $this->repository->save($workspace);

        return $workspace;
    }

    public function removeBookmark(string $scopeKey, string $bookmarkId): KnowledgeWorkspace
    {
        $workspace = $this->getWorkspace($scopeKey)->removeBookmark($bookmarkId);
        $this->repository->save($workspace);

        return $workspace;
    }

    public function reset(string $scopeKey = 'default'): KnowledgeWorkspace
    {
        $workspace = $this->getWorkspace($scopeKey)->reset();
        $this->repository->save($workspace);

        return $this->syncWorkspace($scopeKey);
    }
}
