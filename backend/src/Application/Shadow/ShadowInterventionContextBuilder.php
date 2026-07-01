<?php

declare(strict_types=1);

namespace App\Application\Shadow;

use App\Domain\Shadow\ShadowInterventionPolicy;
use App\Domain\Shadow\ShadowSession;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\VideoIntelligence\VideoIntelligence;
use App\Domain\VideoIntelligence\VideoIntelligenceFactoryInterface;

final class ShadowInterventionContextBuilder
{
    public function __construct(
        private readonly ShadowContextFactory $shadowContextFactory,
        private readonly VideoRepositoryInterface $videoRepository,
        private readonly VideoIntelligenceFactoryInterface $videoIntelligenceFactory,
    ) {
    }

    public function build(ShadowSession $session, float $currentTimeSeconds): ShadowInterventionContext
    {
        $watchContext = $this->shadowContextFactory->create(
            $session->videoId()->value,
            $currentTimeSeconds,
            $session->targetLanguage(),
            $session->conversationId()?->value,
        );

        return new ShadowInterventionContext(
            $watchContext,
            $session,
            $session->interventionPolicy(),
            $session->interventions(),
            $this->resolveVideoIntelligence($session->videoId()),
        );
    }

    private function resolveVideoIntelligence(VideoId $videoId): ?VideoIntelligence
    {
        $job = $this->videoRepository->findById($videoId);

        if (null === $job) {
            return null;
        }

        return $this->videoIntelligenceFactory->fromVideoJob($job);
    }
}
