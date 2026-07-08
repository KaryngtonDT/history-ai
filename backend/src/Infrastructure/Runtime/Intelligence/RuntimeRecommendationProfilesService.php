<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Intelligence;

use App\Application\EngineAnalytics\EngineStatisticsAggregator;
use App\Application\Hardware\HardwareReportBuilder;
use App\Domain\Engine\EngineCatalogCapability;
use App\Domain\Engine\EngineRepositoryInterface;
use App\Domain\Hardware\HardwareRepositoryInterface;
use App\Domain\Runtime\RuntimeRepositoryInterface;
use App\Infrastructure\Runtime\Catalog\EngineCatalogDefinitions;

final class RuntimeRecommendationProfilesService
{
    public function __construct(
        private readonly EngineRepositoryInterface $engineRepository,
        private readonly HardwareRepositoryInterface $hardwareRepository,
        private readonly HardwareReportBuilder $hardwareReportBuilder,
        private readonly EngineStatisticsAggregator $statisticsAggregator,
        private readonly RuntimeRepositoryInterface $runtimeRepository,
        private readonly RecommendationEngine $recommendationEngine,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function profiles(): array
    {
        $hardware = $this->hardwareRepository->detect();
        $pipeline = $this->hardwareReportBuilder->recommendedPipeline($hardware->profile);
        $config = $this->runtimeRepository->getConfiguration();
        $analytics = $this->statisticsAggregator->aggregateEngines();

        $fastestByCapability = $this->fastestByCapability($analytics);
        $profileRecommendations = $this->recommendationEngine->recommend($config);

        return [
            'hardwareSummary' => $hardware->profile->summary,
            'hardwareType' => $hardware->profile->type->value,
            'profiles' => [
                'bestQuality' => $this->buildProfile('Best Quality', $profileRecommendations, 'quality'),
                'fastest' => $this->buildFromAnalytics('Fastest', $fastestByCapability),
                'lowestRam' => $this->buildProfile('Lowest RAM', $profileRecommendations, 'balanced'),
                'hardwareRecommended' => $this->buildHardwareProfile('Hardware Recommended', $pipeline),
                'currentSelection' => $this->buildProfile('Current Selection', $profileRecommendations, 'current'),
            ],
            'pipeline' => $pipeline,
            'at' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ];
    }

    /**
     * @param list<array<string, mixed>> $recommendations
     *
     * @return array<string, mixed>
     */
    private function buildProfile(string $label, array $recommendations, string $mode): array
    {
        $items = [];

        foreach ($recommendations as $rec) {
            $items[] = [
                'capability' => $rec['capability'] ?? '',
                'label' => $rec['label'] ?? '',
                'engineId' => $rec['recommendedEngineId'] ?? null,
                'displayName' => $rec['recommendedDisplayName'] ?? null,
                'reason' => $rec['reason'] ?? '',
            ];
        }

        return ['label' => $label, 'mode' => $mode, 'items' => $items];
    }

    /**
     * @param array<string, string> $pipeline
     *
     * @return array<string, mixed>
     */
    private function buildHardwareProfile(string $label, array $pipeline): array
    {
        $map = [
            'speech' => EngineCatalogCapability::SpeechToText,
            'translation' => EngineCatalogCapability::Translation,
            'tts' => EngineCatalogCapability::TextToSpeech,
            'voiceClone' => EngineCatalogCapability::VoiceClone,
            'lipSync' => EngineCatalogCapability::LipSync,
            'render' => EngineCatalogCapability::VideoRender,
        ];

        $items = [];

        foreach ($map as $key => $capability) {
            $engineId = $pipeline[$key] ?? EngineCatalogDefinitions::defaultForCapability($capability)?->id;
            $engine = is_string($engineId) ? $this->engineRepository->findById($engineId) : null;
            $items[] = [
                'capability' => $capability->value,
                'label' => $capability->label(),
                'engineId' => $engineId,
                'displayName' => $engine?->displayName ?? $engineId,
                'reason' => 'Recommended for detected hardware profile.',
            ];
        }

        return ['label' => $label, 'mode' => 'hardware', 'items' => $items];
    }

    /**
     * @param array<string, array<string, mixed>> $fastestByCapability
     *
     * @return array<string, mixed>
     */
    private function buildFromAnalytics(string $label, array $fastestByCapability): array
    {
        $items = [];

        foreach ($fastestByCapability as $capability => $engine) {
            $items[] = [
                'capability' => $capability,
                'label' => EngineCatalogCapability::tryFrom($capability)?->label() ?? $capability,
                'engineId' => $engine['engineId'] ?? null,
                'displayName' => $engine['engineId'] ?? null,
                'reason' => 'Fastest based on recorded execution history.',
            ];
        }

        return ['label' => $label, 'mode' => 'analytics', 'items' => $items];
    }

    /**
     * @param list<array<string, mixed>> $analytics
     *
     * @return array<string, array<string, mixed>>
     */
    private function fastestByCapability(array $analytics): array
    {
        $byCapability = [];

        foreach ($analytics as $engine) {
            $capability = (string) ($engine['stage'] ?? $engine['capability'] ?? '');
            if ('' === $capability) {
                continue;
            }

            $current = $byCapability[$capability] ?? null;
            $duration = (int) ($engine['medianDurationSeconds'] ?? PHP_INT_MAX);

            if (null === $current || $duration < (int) ($current['medianDurationSeconds'] ?? PHP_INT_MAX)) {
                $byCapability[$capability] = $engine;
            }
        }

        return $byCapability;
    }
}
