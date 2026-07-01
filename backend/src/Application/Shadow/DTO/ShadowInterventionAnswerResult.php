<?php

declare(strict_types=1);

namespace App\Application\Shadow\DTO;

use App\Domain\Shadow\ShadowSession;

final readonly class ShadowInterventionAnswerResult
{
    public function __construct(
        public string $sessionId,
        public string $interventionId,
        public string $reply,
        public bool $recommendResume,
        public string $answerLanguage,
        public string $speechLanguage,
        public bool $fallbackUsed,
        public string $reason,
        public ShadowSessionResult $session,
    ) {
    }

    public static function fromSession(
        ShadowSession $session,
        string $interventionId,
        string $reply,
        bool $recommendResume,
        ShadowAnswerVoiceMetadata $voice,
    ): self {
        return new self(
            sessionId: $session->id()->value,
            interventionId: $interventionId,
            reply: $reply,
            recommendResume: $recommendResume,
            answerLanguage: $voice->answerLanguage->value,
            speechLanguage: $voice->speechLanguage->value,
            fallbackUsed: $voice->fallbackUsed,
            reason: $voice->reason,
            session: ShadowSessionResult::fromDomain($session),
        );
    }
}
