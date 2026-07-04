<?php

declare(strict_types=1);

namespace App\Application\ShadowBrowser;

use App\Application\ShadowSecondBrain\Handlers\PostBrainBookmarkHandler;
use App\Application\Video\VideoProcessingEnqueueService;
use App\Application\YouTube\Commands\ImportYouTubeCommand;
use App\Application\YouTube\Handlers\ImportYouTubeHandler;
use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\ShadowBrowser\BrowserActionType;
use App\Domain\ShadowBrowser\BrowserPlatform;
use App\Domain\ShadowBrowser\Exception\InvalidShadowBrowserException;
use App\Domain\Speech\TranscriptRepositoryInterface;
use App\Domain\Video\VideoId;
use App\Domain\YouTube\Exception\InvalidYouTubeException;
use App\Domain\YouTube\YouTubeUrl;
use App\Domain\YouTube\YouTubeVideoRepositoryInterface;
use App\Infrastructure\YouTube\YouTubeImporterException;

final class BrowserActionDispatcher
{
    public function __construct(
        private readonly BrowserCoordinator $coordinator,
        private readonly BrowserYouTubeUrlParser $youtubeUrlParser,
        private readonly YouTubeVideoRepositoryInterface $youtubeVideoRepository,
        private readonly ImportYouTubeHandler $importYouTubeHandler,
        private readonly PostBrainBookmarkHandler $postBrainBookmarkHandler,
        private readonly BrowserAuditLog $auditLog,
        private readonly VideoProcessingEnqueueService $videoProcessingEnqueueService,
        private readonly TranscriptRepositoryInterface $transcriptRepository,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function dispatch(string $scopeKey, BrowserActionType $action, array $payload): array
    {
        $page = $this->normalizePageContext($payload);
        $workspace = $this->coordinator->getWorkspace($scopeKey);

        if (null === $workspace->activeSession()) {
            throw new InvalidShadowBrowserException('Connect the browser companion before running actions.');
        }

        $activity = $this->auditLog->recordAction($workspace, $action, $page['url'], $page['platform']);
        $workspace = $workspace->recordActivity($activity);
        $this->coordinator->saveWorkspace($workspace);

        return match ($action) {
            BrowserActionType::Explain => $this->explain($page),
            BrowserActionType::Translate => $this->translate($page),
            BrowserActionType::Summarize => $this->summarize($page),
            BrowserActionType::SaveToBrain => $this->saveToBrain($scopeKey, $page),
            BrowserActionType::OpenWatch => $this->openWatch($page, $payload),
        };
    }

    /** @param array<string, mixed> $payload */
    /** @return array{url: string, title: string, platform: BrowserPlatform, host: string, language: ?string} */
    private function normalizePageContext(array $payload): array
    {
        $url = is_string($payload['url'] ?? null) ? trim($payload['url']) : '';

        if ('' === $url) {
            throw new InvalidShadowBrowserException('Browser action requires a page url.');
        }

        $title = is_string($payload['title'] ?? null) ? trim($payload['title']) : 'Untitled page';
        $host = is_string($payload['host'] ?? null) ? trim($payload['host']) : '';
        $platformValue = is_string($payload['platform'] ?? null) ? $payload['platform'] : BrowserPlatform::Unknown->value;
        $platform = BrowserPlatform::tryFrom($platformValue) ?? BrowserPlatform::Unknown;
        $language = is_string($payload['language'] ?? null) ? trim($payload['language']) : null;

        return [
            'url' => $url,
            'title' => $title,
            'platform' => $platform,
            'host' => $host,
            'language' => '' === $language ? null : $language,
        ];
    }

    /** @param array{url: string, title: string, platform: BrowserPlatform, host: string} $page */
    /** @return array<string, mixed> */
    private function explain(array $page): array
    {
        $platformLabel = str_replace('_', ' ', $page['platform']->value);
        $summary = sprintf(
            'This page (%s) appears to focus on: %s.',
            $platformLabel,
            $page['title'],
        );

        return [
            'action' => BrowserActionType::Explain->value,
            'status' => 'completed',
            'message' => 'Explanation ready.',
            'summary' => $summary,
            'body' => $summary,
            'concepts' => $this->inferConcepts($page['title']),
            'estimatedLevel' => 'intermediate',
            'explainability' => [
                'reason' => 'page_context',
                'detail' => 'Generated from the current page title and detected platform.',
                'humanReadable' => $summary,
            ],
        ];
    }

    /** @param array{url: string, title: string, platform: BrowserPlatform, host: string} $page */
    /** @return array<string, mixed> */
    private function translate(array $page): array
    {
        $language = $this->resolveLanguage($page);

        return [
            'action' => BrowserActionType::Translate->value,
            'status' => 'completed',
            'message' => sprintf('Translation preview (%s).', $language),
            'language' => $language,
            'body' => sprintf('[%s] %s', strtoupper($language), $page['title']),
            'summary' => 'Full page translation uses the Lumen pipeline when the source is imported.',
        ];
    }

    /** @param array{url: string, title: string, platform: BrowserPlatform, host: string} $page */
    /** @return array<string, mixed> */
    private function summarize(array $page): array
    {
        $keyPoints = [
            sprintf('Source: %s', $page['title']),
            sprintf('Platform: %s', str_replace('_', ' ', $page['platform']->value)),
            'Shadow can deepen this summary after import or transcript availability.',
        ];

        return [
            'action' => BrowserActionType::Summarize->value,
            'status' => 'completed',
            'message' => 'Summary ready.',
            'summary' => $keyPoints[0],
            'body' => implode("\n", array_map(static fn (string $point): string => "• {$point}", $keyPoints)),
            'keyPoints' => $keyPoints,
        ];
    }

    /** @param array{url: string, title: string, platform: BrowserPlatform, host: string} $page */
    /** @return array<string, mixed> */
    private function saveToBrain(string $scopeKey, array $page): array
    {
        $resourceType = BrowserPlatform::Youtube === $page['platform']
            ? 'youtube'
            : null;

        $bookmark = ($this->postBrainBookmarkHandler)($scopeKey, [
            'label' => $page['title'],
            'tags' => ['browser', $page['platform']->value],
            'resourceType' => $resourceType,
            'resourceId' => $page['url'],
        ]);

        return [
            'action' => BrowserActionType::SaveToBrain->value,
            'status' => 'completed',
            'message' => 'Saved to Second Brain.',
            'bookmark' => $bookmark['bookmark'] ?? null,
        ];
    }

    /** @param array<string, mixed> $payload */
    /** @param array{url: string, title: string, platform: BrowserPlatform, host: string} $page */
    /** @return array<string, mixed> */
    private function openWatch(array $page, array $payload): array
    {
        if (BrowserPlatform::Youtube !== $page['platform']) {
            return [
                'action' => BrowserActionType::OpenWatch->value,
                'status' => 'unavailable',
                'message' => 'Open Watch is available on YouTube videos only.',
            ];
        }

        $url = $this->canonicalYoutubeUrl($page['url']);

        if (!YouTubeUrl::isValid($url)) {
            return [
                'action' => BrowserActionType::OpenWatch->value,
                'status' => 'unavailable',
                'message' => 'This YouTube URL is not supported for import.',
            ];
        }

        $existing = $this->findImportedVideo($url);

        if (null !== $existing) {
            $this->ensureTranscriptProcessingQueued($existing['videoId']);

            return $this->watchResponse($existing['videoId'], 'completed', 'Watch ready.');
        }

        $importConfirmed = filter_var($payload['importConfirmed'] ?? false, FILTER_VALIDATE_BOOL);

        if (!$importConfirmed) {
            return [
                'action' => BrowserActionType::OpenWatch->value,
                'status' => 'confirmation_required',
                'message' => 'Import this YouTube video into Lumen?',
                'importRequired' => true,
            ];
        }

        try {
            $result = ($this->importYouTubeHandler)(new ImportYouTubeCommand(
                url: $url,
                processingMode: ProcessingMode::Manual,
            ));
        } catch (InvalidYouTubeException $exception) {
            return [
                'action' => BrowserActionType::OpenWatch->value,
                'status' => 'error',
                'message' => $exception->getMessage(),
            ];
        } catch (YouTubeImporterException $exception) {
            return [
                'action' => BrowserActionType::OpenWatch->value,
                'status' => 'error',
                'message' => $exception->getMessage(),
            ];
        } catch (\Throwable $exception) {
            return [
                'action' => BrowserActionType::OpenWatch->value,
                'status' => 'error',
                'message' => $exception->getMessage(),
            ];
        }

        return $this->watchResponse(
            $result->videoId->value,
            'completed',
            'Video imported. Opening Shadow Watch.',
        );
    }

    /** @return array{videoId: string}|null */
    private function findImportedVideo(string $url): ?array
    {
        foreach ($this->youtubeVideoRepository->findRecent(100) as $video) {
            if ($this->youtubeUrlParser->urlsMatch($url, $video->url())) {
                return ['videoId' => $video->videoId()->value];
            }
        }

        return null;
    }

    /** @return array<string, mixed> */
    private function watchResponse(string $videoId, string $status, string $message): array
    {
        $watchPath = sprintf('/video/%s/watch', $videoId);

        return [
            'action' => BrowserActionType::OpenWatch->value,
            'status' => $status,
            'message' => $message,
            'videoId' => $videoId,
            'watchPath' => $watchPath,
        ];
    }

    /** @return list<string> */
    private function inferConcepts(string $title): array
    {
        $parts = preg_split('/[|:–-]+/', $title) ?: [];

        return array_values(array_filter(array_map('trim', $parts), static fn (string $part): bool => '' !== $part));
    }

    /** @param array{url: string, title: string, platform: BrowserPlatform, host: string, language: ?string} $page */
    private function resolveLanguage(array $page): string
    {
        if (null !== $page['language']) {
            return strtolower($page['language']);
        }

        return 'fr';
    }

    private function canonicalYoutubeUrl(string $url): string
    {
        $videoKey = $this->youtubeUrlParser->extractVideoKey($url);

        if (null === $videoKey) {
            return trim($url);
        }

        return sprintf('https://www.youtube.com/watch?v=%s', $videoKey);
    }

    private function ensureTranscriptProcessingQueued(string $videoId): void
    {
        try {
            $id = new VideoId($videoId);
        } catch (\Throwable) {
            return;
        }

        if (null !== $this->transcriptRepository->findByVideoId($id)) {
            return;
        }

        $this->videoProcessingEnqueueService->enqueueIfNeeded($id, ProcessingMode::Manual);
    }
}
