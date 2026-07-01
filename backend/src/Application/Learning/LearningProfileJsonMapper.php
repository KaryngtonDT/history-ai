<?php

declare(strict_types=1);

namespace App\Application\Learning;

use App\Domain\Learning\Exception\InvalidLearningProfileException;
use App\Domain\Learning\LearningInsight;
use App\Domain\Learning\LearningInsightCollection;
use App\Domain\Learning\LearningInsightId;
use App\Domain\Learning\LearningInsightType;
use App\Domain\Learning\LearningPreference;
use App\Domain\Learning\LearningPreferenceCollection;
use App\Domain\Learning\LearningPreferenceKey;
use App\Domain\Learning\LearningProfile;
use App\Domain\Learning\LearningProfileId;
use App\Domain\Learning\LearningRecommendation;
use App\Domain\Learning\LearningRecommendationCollection;
use App\Domain\Learning\LearningRecommendationId;
use App\Domain\Learning\LearningRecommendationType;
use App\Domain\Learning\LearningSignal;
use App\Domain\Learning\LearningSignalCollection;
use App\Domain\Learning\LearningSignalId;
use App\Domain\Learning\LearningSignalType;
use JsonException;

final class LearningProfileJsonMapper
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(LearningProfile $profile): array
    {
        return [
            'id' => $profile->id()->value,
            'scopeKey' => $profile->scopeKey(),
            'adaptiveRecommendationsEnabled' => $profile->adaptiveRecommendationsEnabled(),
            'preferences' => array_map(
                static fn (LearningPreference $preference): array => [
                    'key' => $preference->key()->value,
                    'enabled' => $preference->enabled(),
                ],
                $profile->preferences()->all(),
            ),
            'signals' => array_map(
                fn (LearningSignal $signal): array => $this->signalToArray($signal),
                $profile->signals()->all(),
            ),
            'insights' => array_map(
                fn (LearningInsight $insight): array => $this->insightToArray($insight),
                $profile->insights()->all(),
            ),
            'recommendations' => array_map(
                fn (LearningRecommendation $recommendation): array => $this->recommendationToArray($recommendation),
                $profile->recommendations()->all(),
            ),
        ];
    }

    public function toJson(LearningProfile $profile): string
    {
        return json_encode($this->toArray($profile), JSON_THROW_ON_ERROR);
    }

    public function fromJson(string $json): LearningProfile
    {
        try {
            /** @var mixed $decoded */
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidLearningProfileException('Stored learning profile is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded)) {
            throw new InvalidLearningProfileException('Stored learning profile must be a JSON object.');
        }

        $idValue = is_string($decoded['id'] ?? null) ? $decoded['id'] : null;
        $scopeKey = is_string($decoded['scopeKey'] ?? null) ? $decoded['scopeKey'] : 'default';

        if (null === $idValue) {
            throw new InvalidLearningProfileException('Stored learning profile is missing id.');
        }

        $preferences = $this->preferencesFromArray(
            is_array($decoded['preferences'] ?? null) ? $decoded['preferences'] : [],
            (bool) ($decoded['adaptiveRecommendationsEnabled'] ?? false),
        );
        $signals = $this->signalsFromArray(is_array($decoded['signals'] ?? null) ? $decoded['signals'] : []);
        $insights = $this->insightsFromArray(is_array($decoded['insights'] ?? null) ? $decoded['insights'] : []);
        $recommendations = $this->recommendationsFromArray(
            is_array($decoded['recommendations'] ?? null) ? $decoded['recommendations'] : [],
        );

        return new LearningProfile(
            new LearningProfileId($idValue),
            $scopeKey,
            $preferences,
            $signals,
            $insights,
            $recommendations,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function signalToArray(LearningSignal $signal): array
    {
        return [
            'id' => $signal->id()->value,
            'type' => $signal->type()->value,
            'recordedAt' => $signal->recordedAt()->format(DATE_ATOM),
            'context' => $signal->context(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function insightToArray(LearningInsight $insight): array
    {
        return [
            'id' => $insight->id()->value,
            'type' => $insight->type()->value,
            'summary' => $insight->summary(),
            'sourceSignalIds' => $insight->sourceSignalIds(),
            'generatedAt' => $insight->generatedAt()->format(DATE_ATOM),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function recommendationToArray(LearningRecommendation $recommendation): array
    {
        return [
            'id' => $recommendation->id()->value,
            'type' => $recommendation->type()->value,
            'explanation' => $recommendation->explanation(),
            'sourceInsightIds' => $recommendation->sourceInsightIds(),
            'generatedAt' => $recommendation->generatedAt()->format(DATE_ATOM),
        ];
    }

    /**
     * @param list<array<string, mixed>> $data
     */
    private function preferencesFromArray(array $data, bool $adaptiveEnabled): LearningPreferenceCollection
    {
        $collection = LearningPreferenceCollection::default();

        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }

            $keyValue = is_string($item['key'] ?? null) ? $item['key'] : null;
            $enabled = (bool) ($item['enabled'] ?? false);
            $key = LearningPreferenceKey::tryFrom((string) $keyValue);

            if (LearningPreferenceKey::AdaptiveRecommendationsEnabled === $key) {
                $collection = $collection->withPreference(
                    LearningPreference::adaptiveRecommendationsEnabled($enabled),
                );
            }
        }

        if ($adaptiveEnabled && !$collection->adaptiveRecommendationsEnabled()) {
            $collection = $collection->withPreference(
                LearningPreference::adaptiveRecommendationsEnabled(true),
            );
        }

        return $collection;
    }

    /**
     * @param list<array<string, mixed>> $data
     */
    private function signalsFromArray(array $data): LearningSignalCollection
    {
        $collection = LearningSignalCollection::empty();

        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }

            $id = is_string($item['id'] ?? null) ? $item['id'] : null;
            $typeValue = is_string($item['type'] ?? null) ? $item['type'] : null;
            $recordedAtValue = is_string($item['recordedAt'] ?? null) ? $item['recordedAt'] : null;
            $context = is_array($item['context'] ?? null) ? $item['context'] : null;
            $type = LearningSignalType::tryFrom((string) $typeValue);

            if (null === $id || null === $type || null === $context) {
                continue;
            }

            $recordedAt = $recordedAtValue
                ? \DateTimeImmutable::createFromFormat(DATE_ATOM, $recordedAtValue) ?: new \DateTimeImmutable()
                : new \DateTimeImmutable();

            $collection = $collection->append(
                LearningSignal::record(
                    $type,
                    $context,
                    new LearningSignalId($id),
                    $recordedAt,
                ),
            );
        }

        return $collection;
    }

    /**
     * @param list<array<string, mixed>> $data
     */
    private function insightsFromArray(array $data): LearningInsightCollection
    {
        $collection = LearningInsightCollection::empty();

        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }

            $id = is_string($item['id'] ?? null) ? $item['id'] : null;
            $typeValue = is_string($item['type'] ?? null) ? $item['type'] : null;
            $summary = is_string($item['summary'] ?? null) ? $item['summary'] : null;
            $sourceSignalIds = is_array($item['sourceSignalIds'] ?? null) ? $item['sourceSignalIds'] : [];
            $generatedAtValue = is_string($item['generatedAt'] ?? null) ? $item['generatedAt'] : null;
            $type = LearningInsightType::tryFrom((string) $typeValue);

            if (null === $id || null === $type || null === $summary) {
                continue;
            }

            $sourceIds = array_values(array_filter($sourceSignalIds, is_string(...)));

            if ([] === $sourceIds) {
                continue;
            }

            $generatedAt = $generatedAtValue
                ? \DateTimeImmutable::createFromFormat(DATE_ATOM, $generatedAtValue) ?: new \DateTimeImmutable()
                : new \DateTimeImmutable();

            $collection = $collection->append(
                LearningInsight::derive(
                    $type,
                    $summary,
                    $sourceIds,
                    new LearningInsightId($id),
                    $generatedAt,
                ),
            );
        }

        return $collection;
    }

    /**
     * @param list<array<string, mixed>> $data
     */
    private function recommendationsFromArray(array $data): LearningRecommendationCollection
    {
        $collection = LearningRecommendationCollection::empty();

        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }

            $id = is_string($item['id'] ?? null) ? $item['id'] : null;
            $typeValue = is_string($item['type'] ?? null) ? $item['type'] : null;
            $explanation = is_string($item['explanation'] ?? null) ? $item['explanation'] : null;
            $sourceInsightIds = is_array($item['sourceInsightIds'] ?? null) ? $item['sourceInsightIds'] : [];
            $generatedAtValue = is_string($item['generatedAt'] ?? null) ? $item['generatedAt'] : null;
            $type = LearningRecommendationType::tryFrom((string) $typeValue);

            if (null === $id || null === $type || null === $explanation) {
                continue;
            }

            $sourceIds = array_values(array_filter($sourceInsightIds, is_string(...)));

            if ([] === $sourceIds) {
                continue;
            }

            $generatedAt = $generatedAtValue
                ? \DateTimeImmutable::createFromFormat(DATE_ATOM, $generatedAtValue) ?: new \DateTimeImmutable()
                : new \DateTimeImmutable();

            $collection = $collection->append(
                LearningRecommendation::derive(
                    $type,
                    $explanation,
                    $sourceIds,
                    new LearningRecommendationId($id),
                    $generatedAt,
                ),
            );
        }

        return $collection;
    }
}
