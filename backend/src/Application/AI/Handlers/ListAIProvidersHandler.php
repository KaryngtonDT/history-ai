<?php

declare(strict_types=1);

namespace App\Application\AI\Handlers;

use App\Application\AI\DTO\AIEngineSummary;
use App\Application\AI\DTO\AIProviderSummary;
use App\Domain\AI\AIProviderResolverInterface;

final class ListAIProvidersHandler
{
    public function __construct(
        private readonly AIProviderResolverInterface $aiProviderResolver,
    ) {
    }

    /**
     * @return list<AIEngineSummary>
     */
    public function __invoke(): array
    {
        $registry = $this->aiProviderResolver->registry();

        return array_map(
            static fn ($engine): AIEngineSummary => new AIEngineSummary(
                engineId: $engine->id()->value,
                capability: $engine->capability()->value,
                enabled: $engine->isEnabled(),
                providers: array_map(
                    static fn ($provider): AIProviderSummary => new AIProviderSummary(
                        providerId: $provider->providerId(),
                        displayName: $provider->displayName(),
                        capability: $provider->capability()->value,
                        enabled: $provider->isEnabled(),
                    ),
                    $engine->providers(),
                ),
            ),
            $registry->all(),
        );
    }
}
