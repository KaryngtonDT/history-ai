<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Shadow;

use App\Application\Shadow\DTO\ShadowAnswerResult;

final readonly class ShadowAnswerResponse
{
    public function __construct(
        public string $sessionId,
        public string $answer,
        public float $currentTimeSeconds,
        public ?int $currentTranscriptSegmentIndex,
        public ?int $currentTranslationSegmentIndex,
        public string $answerLanguage,
        public string $speechLanguage,
        public bool $fallbackUsed,
        public string $reason,
        public ShadowSessionResponse $session,
    ) {
    }

    public static function fromResult(ShadowAnswerResult $result): self
    {
        return new self(
            sessionId: $result->sessionId,
            answer: $result->answer,
            currentTimeSeconds: $result->currentTimeSeconds,
            currentTranscriptSegmentIndex: $result->currentTranscriptSegmentIndex,
            currentTranslationSegmentIndex: $result->currentTranslationSegmentIndex,
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
            'answer' => $this->answer,
            'currentTimeSeconds' => $this->currentTimeSeconds,
            'currentTranscriptSegmentIndex' => $this->currentTranscriptSegmentIndex,
            'currentTranslationSegmentIndex' => $this->currentTranslationSegmentIndex,
            'answerLanguage' => $this->answerLanguage,
            'speechLanguage' => $this->speechLanguage,
            'fallbackUsed' => $this->fallbackUsed,
            'reason' => $this->reason,
            'session' => $this->session->toArray(),
        ];
    }
}
