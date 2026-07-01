<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Shadow;

use App\Application\Shadow\DTO\ShadowInterventionAnswerResult;

final readonly class ShadowInterventionAnswerResponse
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
        public ShadowSessionResponse $session,
    ) {
    }

    public static function fromResult(ShadowInterventionAnswerResult $result): self
    {
        return new self(
            sessionId: $result->sessionId,
            interventionId: $result->interventionId,
            reply: $result->reply,
            recommendResume: $result->recommendResume,
            answerLanguage: $result->answerLanguage,
            speechLanguage: $result->speechLanguage,
            fallbackUsed: $result->fallbackUsed,
            reason: $result->reason,
            session: ShadowSessionResponse::fromResult($result->session),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'sessionId' => $this->sessionId,
            'interventionId' => $this->interventionId,
            'reply' => $this->reply,
            'recommendResume' => $this->recommendResume,
            'answerLanguage' => $this->answerLanguage,
            'speechLanguage' => $this->speechLanguage,
            'fallbackUsed' => $this->fallbackUsed,
            'reason' => $this->reason,
            'session' => $this->session->toArray(),
        ];
    }
}
