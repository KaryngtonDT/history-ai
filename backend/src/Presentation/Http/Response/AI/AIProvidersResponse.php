<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\AI;

use App\Application\AI\DTO\AIEngineSummary;
use App\Application\AI\DTO\AIProviderSummary;

final readonly class AIProvidersResponse
{
    /**
     * @param list<AIEngineSummary> $engines
     */
    public static function fromSummaries(array $engines): self
    {
        return new self($engines);
    }

    /**
     * @param list<AIEngineSummary> $engines
     */
    private function __construct(private array $engines)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'engines' => array_map(
                static fn (AIEngineSummary $engine): array => [
                    'engineId' => $engine->engineId,
                    'capability' => $engine->capability,
                    'enabled' => $engine->enabled,
                    'providers' => array_map(
                        static fn (AIProviderSummary $provider): array => [
                            'providerId' => $provider->providerId,
                            'displayName' => $provider->displayName,
                            'capability' => $provider->capability,
                            'enabled' => $provider->enabled,
                        ],
                        $engine->providers,
                    ),
                ],
                $this->engines,
            ),
        ];
    }
}
