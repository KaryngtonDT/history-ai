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
use App\Infrastructure\Runtime\Readiness\EngineStatusFinalizer;

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
    public function __construct(
        private readonly EngineDiscovery $engineDiscovery,
        private readonly EngineStatusFinalizer $statusFinalizer,
    ) {
    }

    public function evaluate(): ReadinessReport
    {
        $engines = [];
        $issues = [];
        $readyCount = 0;

        foreach ($this->engineDiscovery->discover() as $engine) {
            $engine = $this->statusFinalizer->finalize($engine);
            $runtimeEngine = $this->toRuntimeEngine($engine);
            $engines[] = $runtimeEngine;

            if ($runtimeEngine->isReady()) {
                ++$readyCount;
            }

            if ($runtimeEngine->configured && !$runtimeEngine->isReady()) {
                $issues[] = sprintf(
                    'Configured engine "%s" is %s (%s). %s',
                    $runtimeEngine->displayName,
                    $runtimeEngine->status->value,
                    $runtimeEngine->capability->label(),
                    $runtimeEngine->errorReason ?? 'See requirements.',
                );
            }
        }

        $total = count($engines);
        $configuredReady = count(array_filter(
            $engines,
            static fn (RuntimeEngine $engine): bool => $engine->configured && $engine->isReady(),
        ));
        $configuredCount = count(array_filter(
            $engines,
            static fn (RuntimeEngine $engine): bool => $engine->configured,
        ));

        $status = match (true) {
            0 === $total => RuntimeStatus::Unknown,
            $configuredCount > 0 && $configuredReady === $configuredCount => RuntimeStatus::Ready,
            $configuredReady > 0 => RuntimeStatus::Degraded,
            default => RuntimeStatus::Unavailable,
        };

        return new ReadinessReport($status, $readyCount, $total, $engines, $issues);
    }

    private function toRuntimeEngine(Engine $engine): RuntimeEngine
    {
        $requirements = array_map(
            static fn ($req): RuntimeRequirement => new RuntimeRequirement(
                $req->key,
                $req->label,
                $req->satisfied,
                $req->detail,
            ),
            $engine->requirements,
        );

        return new RuntimeEngine(
            id: $engine->id,
            displayName: $engine->displayName,
            capability: RuntimeCapability::from($engine->capability->value),
            status: $engine->runtimeStatus,
            mode: $engine->executionMode,
            configured: $engine->configured,
            discovered: $engine->installed,
            executableFound: $engine->executableFound,
            modelFound: $engine->modelFound,
            role: $engine->role->value,
            roleLabel: $engine->role->label(),
            tier: $engine->tier->value,
            tierLabel: $engine->tier->label(),
            version: $engine->version?->value,
            binaryPath: $engine->version?->build,
            errorReason: $engine->errorReason,
            expectedModel: $engine->expectedModel,
            requirements: $requirements,
            installCommand: $engine->installCommand,
            modelDownloadHint: $engine->modelDownloadHint,
            documentationPath: $engine->documentationPath,
            autoProvisionSupported: $engine->autoProvisionSupported,
        );
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
            default => throw new \InvalidArgumentException(
                sprintf('Capability "%s" is not part of the video pipeline.', $capability->value),
            ),
        };
    }
}
