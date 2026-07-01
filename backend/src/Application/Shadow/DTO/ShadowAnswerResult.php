<?php

declare(strict_types=1);

namespace App\Application\Shadow\DTO;

use App\Domain\Shadow\ShadowSession;

final readonly class ShadowAnswerResult
{
    public function __construct(
        public string $sessionId,
        public string $answer,
        public float $currentTimeSeconds,
        public ?int $currentTranscriptSegmentIndex,
        public ?int $currentTranslationSegmentIndex,
        public ShadowSessionResult $session,
    ) {
    }

    public static function fromSession(ShadowSession $session, string $answer): self
    {
        return new self(
            sessionId: $session->id()->value,
            answer: $answer,
            currentTimeSeconds: $session->currentTimestamp()->seconds(),
            currentTranscriptSegmentIndex: $session->currentTranscriptSegmentIndex(),
            currentTranslationSegmentIndex: $session->currentTranslationSegmentIndex(),
            session: ShadowSessionResult::fromDomain($session),
        );
    }
}
