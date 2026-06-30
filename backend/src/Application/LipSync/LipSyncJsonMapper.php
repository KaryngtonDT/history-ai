<?php

declare(strict_types=1);

namespace App\Application\LipSync;

use App\Domain\LipSync\Exception\InvalidLipSyncException;
use App\Domain\LipSync\LipSyncArtifact;
use App\Domain\LipSync\LipSyncArtifactId;
use App\Domain\LipSync\LipSyncProvider;
use App\Domain\LipSync\LipSyncVideo;
use App\Domain\LipSync\LipSyncVideoId;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\TTS\AudioId;
use App\Domain\Video\VideoId;
use JsonException;

final class LipSyncJsonMapper
{
    /**
     * @return array{
     *     artifactId: string,
     *     sourceVideoId: string,
     *     clonedAudioId: string,
     *     targetLanguage: string,
     *     provider: string,
     *     synchronizedVideoId: string,
     *     storagePath: string,
     *     duration: float
     * }
     */
    public function toArray(LipSyncArtifact $artifact, TranslationLanguage $targetLanguage): array
    {
        return [
            'artifactId' => $artifact->artifactId()->value,
            'sourceVideoId' => $artifact->sourceVideoId()->value,
            'clonedAudioId' => $artifact->audio()->value,
            'targetLanguage' => $targetLanguage->value,
            'provider' => $artifact->provider()->value,
            'synchronizedVideoId' => $artifact->video()->synchronizedVideoId()->value,
            'storagePath' => $artifact->video()->storagePath(),
            'duration' => $artifact->video()->duration(),
        ];
    }

    public function toJson(LipSyncArtifact $artifact, TranslationLanguage $targetLanguage): string
    {
        return json_encode($this->toArray($artifact, $targetLanguage), JSON_THROW_ON_ERROR);
    }

    public function fromJson(string $json): LipSyncArtifact
    {
        try {
            /** @var mixed $decoded */
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidLipSyncException('Stored lip sync is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded)) {
            throw new InvalidLipSyncException('Stored lip sync must be a JSON object.');
        }

        $artifactId = is_string($decoded['artifactId'] ?? null) ? $decoded['artifactId'] : null;
        $sourceVideoId = is_string($decoded['sourceVideoId'] ?? null) ? $decoded['sourceVideoId'] : null;
        $clonedAudioId = is_string($decoded['clonedAudioId'] ?? null) ? $decoded['clonedAudioId'] : null;
        $providerValue = is_string($decoded['provider'] ?? null)
            ? $decoded['provider']
            : LipSyncProvider::Mock->value;
        $synchronizedVideoId = is_string($decoded['synchronizedVideoId'] ?? null)
            ? $decoded['synchronizedVideoId']
            : null;
        $storagePath = is_string($decoded['storagePath'] ?? null) ? $decoded['storagePath'] : null;
        $duration = is_numeric($decoded['duration'] ?? null) ? (float) $decoded['duration'] : null;

        if (
            null === $artifactId
            || null === $sourceVideoId
            || null === $clonedAudioId
            || null === $synchronizedVideoId
            || null === $storagePath
            || null === $duration
        ) {
            throw new InvalidLipSyncException('Stored lip sync is missing required fields.');
        }

        $provider = LipSyncProvider::tryFrom($providerValue) ?? LipSyncProvider::Mock;

        return LipSyncArtifact::create(
            new LipSyncArtifactId($artifactId),
            new VideoId($sourceVideoId),
            new AudioId($clonedAudioId),
            $provider,
            LipSyncVideo::create(
                new LipSyncVideoId($synchronizedVideoId),
                $storagePath,
                $duration,
            ),
        );
    }

    public function targetLanguageFromJson(string $json): TranslationLanguage
    {
        try {
            /** @var mixed $decoded */
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidLipSyncException('Stored lip sync is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded)) {
            throw new InvalidLipSyncException('Stored lip sync must be a JSON object.');
        }

        $targetLanguageValue = is_string($decoded['targetLanguage'] ?? null)
            ? $decoded['targetLanguage']
            : TranslationLanguage::Unknown->value;

        return TranslationLanguage::tryFrom($targetLanguageValue) ?? TranslationLanguage::Unknown;
    }
}
