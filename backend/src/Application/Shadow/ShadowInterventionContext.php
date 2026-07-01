<?php

declare(strict_types=1);

namespace App\Application\Shadow;

use App\Domain\Review\UserPreferenceProfile;
use App\Domain\Shadow\ShadowInterventionCollection;
use App\Domain\Shadow\ShadowInterventionPolicy;
use App\Domain\Shadow\ShadowPlaybackState;
use App\Domain\Shadow\ShadowSession;
use App\Domain\VideoIntelligence\VideoIntelligence;

final readonly class ShadowInterventionContext
{
    public function __construct(
        public WatchContext $watchContext,
        public ShadowSession $session,
        public ShadowInterventionPolicy $policy,
        public ShadowInterventionCollection $recentInterventions,
        public ?VideoIntelligence $videoIntelligence = null,
        public ?UserPreferenceProfile $userPreferenceProfile = null,
    ) {
    }

    public function currentTimeSeconds(): float
    {
        return $this->watchContext->currentTimeSeconds;
    }

    public function isPaused(): bool
    {
        return ShadowPlaybackState::Paused === $this->session->playbackState();
    }

    public function transcriptText(): ?string
    {
        return $this->watchContext->currentTranscriptSegment?->text;
    }

    public function translationText(): ?string
    {
        return $this->watchContext->currentTranslationSegment?->translatedText
            ?? $this->watchContext->currentTranscriptSegment?->translatedText;
    }

    public function previousTranscriptText(): ?string
    {
        return $this->watchContext->previousTranscriptSegment?->text;
    }
}
