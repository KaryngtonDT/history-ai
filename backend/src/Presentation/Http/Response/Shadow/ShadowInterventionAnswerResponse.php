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
            'session' => $this->session->toArray(),
        ];
    }
}
