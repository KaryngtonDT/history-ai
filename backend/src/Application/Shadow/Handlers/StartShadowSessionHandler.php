<?php

declare(strict_types=1);

namespace App\Application\Shadow\Handlers;

use App\Application\Shadow\Commands\StartShadowSessionCommand;
use App\Application\Shadow\DTO\ShadowSessionResult;
use App\Application\Shadow\ShadowSessionResolver;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Domain\Chat\Exception\InvalidConversationIdException;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Shadow\ShadowSession;
use App\Domain\Shadow\ShadowSessionId;
use App\Domain\Shadow\ShadowSessionRepositoryInterface;
use App\Domain\Speech\TranscriptRepositoryInterface;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;

final class StartShadowSessionHandler
{
    public function __construct(
        private readonly ShadowSessionRepositoryInterface $sessionRepository,
        private readonly TranscriptRepositoryInterface $transcriptRepository,
        private readonly ShadowSessionResolver $sessionResolver,
    ) {
    }

    public function __invoke(StartShadowSessionCommand $command): ShadowSessionResult
    {
        try {
            $videoId = new VideoId($command->videoId);
        } catch (InvalidVideoIdException) {
            throw new InvalidShadowSessionException('Video id must be a valid UUID.');
        }

        if ('' === trim($command->targetLanguage)) {
            throw new InvalidShadowSessionException('Target language cannot be empty.');
        }

        if (null === $this->transcriptRepository->findByVideoId($videoId)) {
            throw new InvalidShadowSessionException(sprintf(
                'Transcript for video "%s" was not found.',
                $command->videoId,
            ));
        }

        try {
            $contentId = $this->sessionResolver->optionalContentId($command->contentId);
            $conversationId = $this->sessionResolver->optionalConversationId($command->conversationId);
        } catch (InvalidContentIdException|InvalidConversationIdException) {
            throw new InvalidShadowSessionException('Invalid request.');
        }

        $session = ShadowSession::start(
            ShadowSessionId::generate(),
            $videoId,
            trim($command->targetLanguage),
            $contentId,
            $conversationId,
        );

        $this->sessionRepository->save($session);

        return ShadowSessionResult::fromDomain($session);
    }
}
