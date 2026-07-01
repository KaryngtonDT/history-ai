<?php

declare(strict_types=1);

namespace App\Application\Shadow\Handlers;

use App\Application\Shadow\Commands\AskShadowQuestionCommand;
use App\Application\Shadow\DTO\ShadowAnswerResult;
use App\Application\Shadow\ShadowAnswerLanguageResolver;
use App\Application\Shadow\ShadowContextFactory;
use App\Application\Shadow\ShadowSessionResolver;
use App\Application\Shadow\ShadowWatchAnswerer;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Shadow\ShadowQuestion;
use App\Domain\Shadow\ShadowSessionRepositoryInterface;
use App\Domain\Shadow\ShadowTimestamp;

final class AskShadowQuestionHandler
{
    public function __construct(
        private readonly ShadowSessionRepositoryInterface $sessionRepository,
        private readonly ShadowSessionResolver $sessionResolver,
        private readonly ShadowContextFactory $shadowContextFactory,
        private readonly ShadowWatchAnswerer $shadowWatchAnswerer,
        private readonly ShadowAnswerLanguageResolver $languageResolver,
    ) {
    }

    public function __invoke(AskShadowQuestionCommand $command): ShadowAnswerResult
    {
        if ($command->currentTimeSeconds < 0) {
            throw new InvalidShadowSessionException('Shadow timestamp cannot be negative.');
        }

        $session = $this->sessionResolver->resolve($command->videoId, $command->sessionId);
        $question = ShadowQuestion::fromString($command->question);

        $context = $this->shadowContextFactory->create(
            $command->videoId,
            $command->currentTimeSeconds,
            $session->targetLanguage(),
            $session->conversationId()?->value,
        );

        $transcriptIndex = $context->currentTranscriptSegment?->index;
        $translationIndex = $context->currentTranslationSegment?->index;

        $session = $session
            ->withTimestamp(
                ShadowTimestamp::fromSeconds($command->currentTimeSeconds),
                $transcriptIndex,
                $translationIndex,
            )
            ->recordQuestion($question);

        $interfaceLanguage = null !== $command->interfaceLanguage
            ? \App\Domain\Shadow\ShadowVoiceLanguage::tryFrom($command->interfaceLanguage)
                ?? \App\Domain\Shadow\ShadowVoiceLanguage::tryFromTargetLanguage($command->interfaceLanguage)
            : null;

        $voice = $this->languageResolver->resolve(
            $command->question,
            $session->targetLanguage(),
            $session->voicePreference(),
            $interfaceLanguage,
        );

        $answer = $this->shadowWatchAnswerer->answer($context, $question, $voice);
        $session = $session->recordAnswer($answer);

        $this->sessionRepository->save($session);

        return ShadowAnswerResult::fromSession($session, $answer->text(), $voice);
    }
}
