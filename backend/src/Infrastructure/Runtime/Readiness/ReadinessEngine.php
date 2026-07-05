<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Readiness;

use App\Domain\Engine\Engine;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Runtime\RuntimeCapability;
use App\Domain\Runtime\RuntimeEngine;
use App\Domain\Runtime\RuntimeRequirement;
use App\Domain\Runtime\RuntimeStatus;
use App\Infrastructure\Runtime\Discovery\EngineDiscovery;

final class ReadinessReport
{
    /**
     * @param list<RuntimeEngine> $engines
     * @param list<string> $issues
     */
    public function __construct(
        public readonly RuntimeStatus $status,
        public readonly int $readyCount,
        public readonly int $totalCount,
        public readonly array $engines,
        public readonly array $issues = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'readyCount' => $this->readyCount,
            'totalCount' => $this->totalCount,
            'issues' => $this->issues,
            'engines' => array_map(
                static fn (RuntimeEngine $engine): array => $engine->toArray(),
                $this->engines,
            ),
        ];
    }
}

final class ReadinessEngine
{
    public function __construct(private readonly EngineDiscovery $engineDiscovery)
    {
    }

    public function evaluate(): ReadinessReport
    {
        $environment = $this->engineDiscovery->environment();
        /** @var array<string, string> $activeProviders */
        $activeProviders = $environment['activeProviders'];
        $engines = [];
        $issues = [];
        $readyCount = 0;

        foreach ($this->engineDiscovery->discover() as $engine) {
            $runtimeEngine = $this->toRuntimeEngine($engine, $activeProviders);
            $engines[] = $runtimeEngine;

            if ($runtimeEngine->isReady()) {
                ++$readyCount;
            }

            if ($runtimeEngine->configured && !$runtimeEngine->discovered) {
                $issues[] = sprintf(
                    'Configured engine "%s" is not ready (%s).',
                    $runtimeEngine->displayName,
                    $runtimeEngine->capability->label(),
                );
            }
        }

        $total = count($engines);
        $status = match (true) {
            0 === $total => RuntimeStatus::Unknown,
            $readyCount === $total => RuntimeStatus::Ready,
            $readyCount > 0 => RuntimeStatus::Degraded,
            default => RuntimeStatus::Unavailable,
        };

        return new ReadinessReport($status, $readyCount, $total, $engines, $issues);
    }

    /**
     * @param array<string, string> $activeProviders
     */
    private function toRuntimeEngine(Engine $engine, array $activeProviders): RuntimeEngine
    {
        $capability = RuntimeCapability::from($engine->capability->value);
        $activeId = $this->normalizeProviderId($activeProviders[$engine->capability->value] ?? '');
        $configured = $activeId === $engine->id || ($engine->id === 'f5_tts' && 'f5' === $activeId);
        $discovered = $engine->installed;
        $requirements = array_map(
            static fn ($req): RuntimeRequirement => new RuntimeRequirement(
                $req->key,
                $req->label,
                $req->satisfied,
                $req->detail,
            ),
            $engine->requirements,
        );

        $status = match (true) {
            $discovered => RuntimeStatus::Ready,
            $configured => RuntimeStatus::Unavailable,
            default => RuntimeStatus::Degraded,
        };

        return new RuntimeEngine(
            id: $engine->id,
            displayName: $engine->displayName,
            capability: $capability,
            status: $status,
            configured: $configured,
            discovered: $discovered,
            version: $engine->version?->value,
            binaryPath: $engine->version?->build,
            requirements: $requirements,
        );
    }

    private function normalizeProviderId(string $providerId): string
    {
        return match ($providerId) {
            'f5', 'f5_tts' => 'f5_tts',
            default => $providerId,
        };
    }

    public function stageTypeForCapability(RuntimeCapability $capability): PipelineStageType
    {
        return match ($capability) {
            RuntimeCapability::SpeechToText => PipelineStageType::SpeechToText,
            RuntimeCapability::Translation => PipelineStageType::Translation,
            RuntimeCapability::TextToSpeech => PipelineStageType::TextToSpeech,
            RuntimeCapability::VoiceClone => PipelineStageType::VoiceClone,
            RuntimeCapability::LipSync => PipelineStageType::LipSync,
            RuntimeCapability::VideoRender => PipelineStageType::VideoRender,
        };
    }
}
