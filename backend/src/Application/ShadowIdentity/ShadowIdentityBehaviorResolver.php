<?php

declare(strict_types=1);

namespace App\Application\ShadowIdentity;

use App\Application\Learning\DTO\LearningAdaptiveHints;
use App\Application\Learning\LearningAdaptiveAdvisor;
use App\Application\ShadowNarrative\ShadowAnswerEnricher;
use App\Application\ShadowNarrative\ShadowPersonaSuggestionEngine;
use App\Domain\ShadowIdentity\ShadowIdentity;
use App\Domain\ShadowIdentity\ShadowIdentityRepositoryInterface;

final class ShadowIdentityBehaviorResolver
{
    public function __construct(
        private readonly ShadowIdentityRepositoryInterface $identityRepository,
        private readonly ShadowAnswerEnricher $answerEnricher,
        private readonly LearningAdaptiveAdvisor $learningAdvisor,
        private readonly ShadowPersonaSuggestionEngine $personaSuggestionEngine,
    ) {
    }

    public function resolve(string $scopeKey = 'default'): ?ShadowIdentity
    {
        return $this->identityRepository->findByScope($scopeKey);
    }

    /**
     * @param list<string> $basePromptLines
     *
     * @return list<string>
     */
    public function enrichPromptLines(array $basePromptLines, string $scopeKey = 'default'): array
    {
        $identity = $this->resolve($scopeKey);

        if (null === $identity) {
            return $basePromptLines;
        }

        return $this->answerEnricher->enrich($basePromptLines, $identity->preferences());
    }

    /**
     * @return array<string, mixed>
     */
    public function adaptiveContext(string $scopeKey = 'default', ?string $contentCategory = null): array
    {
        $identity = $this->resolve($scopeKey);
        $learningHints = $this->learningAdvisor->hints($scopeKey);
        $suggestion = $this->personaSuggestionEngine->suggest($contentCategory);

        return [
            'identityPresent' => null !== $identity,
            'learningHintsActive' => $learningHints->active,
            'personaSuggestion' => $suggestion,
            'explicitPreferencesWin' => true,
        ];
    }

    public function learningHints(string $scopeKey = 'default'): LearningAdaptiveHints
    {
        return $this->learningAdvisor->hints($scopeKey);
    }
}
