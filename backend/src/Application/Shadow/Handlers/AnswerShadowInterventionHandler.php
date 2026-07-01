<?php

declare(strict_types=1);

namespace App\Application\Shadow\Handlers;

use App\Application\Shadow\Commands\AnswerShadowInterventionCommand;
use App\Application\Shadow\DTO\ShadowInterventionAnswerResult;
use App\Application\Shadow\ShadowContextFactory;
use App\Application\Shadow\ShadowInterventionAnswerer;
use App\Application\Shadow\ShadowSessionResolver;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Shadow\ShadowAnswer;
use App\Domain\Shadow\ShadowChallengeAnswer;
use App\Domain\Shadow\ShadowInterventionId;
use App\Domain\Shadow\ShadowQuestion;
use App\Domain\Shadow\ShadowSessionRepositoryInterface;
use App\Domain\Shadow\ShadowTimestamp;

final class AnswerShadowInterventionHandler
{
    public function __construct(
        private readonly ShadowSessionRepositoryInterface $sessionRepository,
        private readonly ShadowSessionResolver $sessionResolver,
        private readonly ShadowContextFactory $shadowContextFactory,
        private readonly ShadowInterventionAnswerer $interventionAnswerer,
    ) {
    }

    public function __invoke(AnswerShadowInterventionCommand $command): ShadowInterventionAnswerResult
    {
        if ($command->currentTimeSeconds < 0) {
            throw new InvalidShadowSessionException('Shadow timestamp cannot be negative.');
        }

        $session = $this->sessionResolver->resolve($command->videoId, $command->sessionId);
        $interventionId = $this->resolveInterventionId($command->interventionId);
        $intervention = $session->interventions()->findById($interventionId);

        if (null === $intervention) {
            throw new InvalidShadowSessionException('Shadow intervention was not found.');
        }

        if ($intervention->isSkipped() || $intervention->isAnswered()) {
            throw new InvalidShadowSessionException('Shadow intervention is no longer active.');
        }

        $answer = ShadowChallengeAnswer::fromString($command->answer);
        $context = $this->shadowContextFactory->create(
            $command->videoId,
            $command->currentTimeSeconds,
            $session->targetLanguage(),
            $session->conversationId()?->value,
        );

        $session = $session->withTimestamp(ShadowTimestamp::fromSeconds($command->currentTimeSeconds));

        $reply = $this->interventionAnswerer->reply($context, $intervention, $answer);
        $session = $session
            ->recordQuestion(ShadowQuestion::fromString($answer->text()))
            ->replaceIntervention($intervention->markAnswered())
            ->recordAnswer(ShadowAnswer::fromString($reply));

        $this->sessionRepository->save($session);

        return ShadowInterventionAnswerResult::fromSession(
            $session,
            $interventionId->value,
            $reply,
            $session->interventionPolicy()->autoResume(),
        );
    }

    private function resolveInterventionId(string $value): ShadowInterventionId
    {
        try {
            return new ShadowInterventionId($value);
        } catch (InvalidShadowSessionException) {
            throw new InvalidShadowSessionException('Shadow intervention was not found.');
        }
    }
}
