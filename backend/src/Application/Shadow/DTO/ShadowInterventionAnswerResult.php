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
        public ShadowSessionResult $session,
    ) {
    }

    public static function fromSession(
        ShadowSession $session,
        string $interventionId,
        string $reply,
        bool $recommendResume,
    ): self {
        return new self(
            sessionId: $session->id()->value,
            interventionId: $interventionId,
            reply: $reply,
            recommendResume: $recommendResume,
            session: ShadowSessionResult::fromDomain($session),
        );
    }
}
