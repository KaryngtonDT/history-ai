<?php

declare(strict_types=1);

namespace App\Application\Chat\Handlers;

use App\Application\Chat\Commands\AskContentChatCommand;
use App\Application\Chat\ContentChatAnswerer;
use App\Application\Chat\DTO\ChatAnswerResult;
use App\Application\Platform\PlatformLoggerInterface;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Chat\ChatQuestion;
use App\Domain\Content\ContentId;

final class AskContentChatHandler
{
    private const string COMPONENT = 'AskContentChatHandler';

    public function __construct(
        private readonly ArtifactRepositoryInterface $artifactRepository,
        private readonly ContentChatAnswerer $contentChatAnswerer,
        private readonly PlatformLoggerInterface $platformLogger,
    ) {
    }

    public function __invoke(AskContentChatCommand $command): ChatAnswerResult
    {
        $this->platformLogger->info(self::COMPONENT, 'request started', [
            'contentId' => $command->contentId,
        ]);

        try {
            $artifacts = $this->artifactRepository->findByContentId(
                new ContentId($command->contentId),
            );

            return $this->contentChatAnswerer->answer(
                $artifacts,
                new ChatQuestion($command->question),
                self::COMPONENT,
            );
        } finally {
            $this->platformLogger->info(self::COMPONENT, 'request completed');
        }
    }
}
