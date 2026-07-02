<?php

declare(strict_types=1);

namespace App\Application\Shadow\Handlers;

use App\Application\Learning\LearningAdaptiveAdvisor;
use App\Application\Learning\LearningAdaptiveVoiceResolver;
use App\Application\Shadow\Commands\AskShadowQuestionCommand;
use App\Application\Shadow\DTO\ShadowAnswerResult;
use App\Application\Shadow\ShadowAnswerLanguageResolver;
use App\Application\Shadow\ShadowContextFactory;
use App\Application\Shadow\ShadowSessionResolver;
use App\Application\Shadow\SessionLearning\SessionLearningCoordinator;
use App\Application\ShadowRelationship\RelationshipProfileBuilder;
use App\Application\ShadowMemory\MemoryBuilder;
use App\Application\Shadow\ShadowWatchAnswerer;
use App\Domain\Shadow\SessionLearning\TeachingStrategy;
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
        private readonly LearningAdaptiveAdvisor $learningAdvisor,
        private readonly LearningAdaptiveVoiceResolver $adaptiveVoiceResolver,
        private readonly SessionLearningCoordinator $sessionLearningCoordinator,
        private readonly RelationshipProfileBuilder $relationshipProfileBuilder,
        private readonly MemoryBuilder $memoryBuilder,
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

        $explicitLanguage = str_contains($voice->reason, 'explicit_user_override');
        $hints = $this->learningAdvisor->hints();
        $voice = $this->adaptiveVoiceResolver->apply(
            $voice,
            $session->voicePreference(),
            $hints,
            $explicitLanguage,
        );

        $learningState = $this->sessionLearningCoordinator->analyzeAndSave($session);
        $teachingStrategy = $this->sessionLearningCoordinator->resolveStrategy($learningState);
        $explanationStyle = $this->resolveExplanationStyle($hints, $teachingStrategy, $learningState->preferences()->adaptiveEnabled());

        $answer = $this->shadowWatchAnswerer->answer(
            $context,
            $question,
            $voice,
            $explanationStyle,
            $learningState->preferences()->adaptiveEnabled() ? $teachingStrategy : null,
        );
        $session = $session->recordAnswer($answer);

        $this->sessionRepository->save($session);

        $this->relationshipProfileBuilder->recordPayload('default', [
            'source' => 'shadow',
            'kind' => 'question',
            'data' => [
                'question' => $command->question,
                'sessionId' => $command->sessionId,
                'videoId' => $command->videoId,
                'timeSeconds' => $command->currentTimeSeconds,
            ],
        ]);

        $this->memoryBuilder->recordPayload('default', [
            'source' => 'shadow',
            'kind' => 'question',
            'data' => [
                'question' => $command->question,
                'sessionId' => $command->sessionId,
                'videoId' => $command->videoId,
                'timeSeconds' => $command->currentTimeSeconds,
            ],
        ]);

        return ShadowAnswerResult::fromSession($session, $answer->text(), $voice);
    }

    private function resolveExplanationStyle(
        \App\Application\Learning\DTO\LearningAdaptiveHints $hints,
        TeachingStrategy $teachingStrategy,
        bool $adaptiveEnabled,
    ): ?\App\Domain\Shadow\ShadowExplanationStyle {
        if ($adaptiveEnabled) {
            return $teachingStrategy->explanationStyle();
        }

        return $hints->explanationStyle;
    }
}
