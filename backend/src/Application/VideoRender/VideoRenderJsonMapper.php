<?php

declare(strict_types=1);

namespace App\Application\VideoRender;

use App\Domain\Translation\TranslationLanguage;
use App\Domain\VideoRender\Exception\InvalidVideoRenderException;
use App\Domain\VideoRender\FinalVideoArtifact;
use App\Domain\VideoRender\FinalVideoId;
use App\Domain\VideoRender\VideoRenderFormat;
use App\Domain\VideoRender\VideoRenderProvider;
use App\Domain\VideoRender\VideoRenderQuality;
use App\Domain\LipSync\LipSyncArtifactId;
use App\Domain\Video\VideoId;
use JsonException;

final class VideoRenderJsonMapper
{
    /**
     * @return array{
     *     finalVideoId: string,
     *     videoId: string,
     *     lipSyncArtifactId: string,
     *     targetLanguage: string,
     *     provider: string,
     *     format: string,
     *     quality: string,
     *     duration: float,
     *     fileSizeBytes: int,
     *     storagePath: string
     * }
     */
    public function toArray(
        FinalVideoArtifact $artifact,
        TranslationLanguage $targetLanguage,
        string $storagePath,
    ): array {
        return [
            'finalVideoId' => $artifact->finalVideoId()->value,
            'videoId' => $artifact->videoId()->value,
            'lipSyncArtifactId' => $artifact->lipSyncArtifactId()->value,
            'targetLanguage' => $targetLanguage->value,
            'provider' => $artifact->provider()->value,
            'format' => $artifact->format()->value,
            'quality' => $artifact->quality()->value,
            'duration' => $artifact->duration(),
            'fileSizeBytes' => $artifact->fileSizeBytes(),
            'storagePath' => $storagePath,
        ];
    }

    public function toJson(
        FinalVideoArtifact $artifact,
        TranslationLanguage $targetLanguage,
        string $storagePath,
    ): string {
        return json_encode($this->toArray($artifact, $targetLanguage, $storagePath), JSON_THROW_ON_ERROR);
    }

    public function fromJson(string $json): FinalVideoArtifact
    {
        try {
            /** @var mixed $decoded */
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidVideoRenderException('Stored final video is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded)) {
            throw new InvalidVideoRenderException('Stored final video must be a JSON object.');
        }

        $finalVideoId = is_string($decoded['finalVideoId'] ?? null) ? $decoded['finalVideoId'] : null;
        $videoId = is_string($decoded['videoId'] ?? null) ? $decoded['videoId'] : null;
        $lipSyncArtifactId = is_string($decoded['lipSyncArtifactId'] ?? null) ? $decoded['lipSyncArtifactId'] : null;
        $providerValue = is_string($decoded['provider'] ?? null) ? $decoded['provider'] : VideoRenderProvider::Mock->value;
        $formatValue = is_string($decoded['format'] ?? null) ? $decoded['format'] : VideoRenderFormat::MP4->value;
        $qualityValue = is_string($decoded['quality'] ?? null) ? $decoded['quality'] : VideoRenderQuality::Standard->value;
        $duration = is_numeric($decoded['duration'] ?? null) ? (float) $decoded['duration'] : null;
        $fileSizeBytes = is_numeric($decoded['fileSizeBytes'] ?? null) ? (int) $decoded['fileSizeBytes'] : null;

        if (
            null === $finalVideoId
            || null === $videoId
            || null === $lipSyncArtifactId
            || null === $duration
            || null === $fileSizeBytes
        ) {
            throw new InvalidVideoRenderException('Stored final video is missing required fields.');
        }

        return FinalVideoArtifact::create(
            new FinalVideoId($finalVideoId),
            new VideoId($videoId),
            new LipSyncArtifactId($lipSyncArtifactId),
            VideoRenderProvider::tryFrom($providerValue) ?? VideoRenderProvider::Mock,
            VideoRenderFormat::tryFrom($formatValue) ?? VideoRenderFormat::MP4,
            VideoRenderQuality::tryFrom($qualityValue) ?? VideoRenderQuality::Standard,
            $duration,
            $fileSizeBytes,
        );
    }

    public function storagePathFromJson(string $json): string
    {
        try {
            /** @var mixed $decoded */
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidVideoRenderException('Stored final video is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded)) {
            throw new InvalidVideoRenderException('Stored final video must be a JSON object.');
        }

        $storagePath = is_string($decoded['storagePath'] ?? null) ? $decoded['storagePath'] : null;

        if (null === $storagePath || '' === trim($storagePath)) {
            throw new InvalidVideoRenderException('Stored final video is missing storage path.');
        }

        return $storagePath;
    }

    public function targetLanguageFromJson(string $json): TranslationLanguage
    {
        try {
            /** @var mixed $decoded */
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidVideoRenderException('Stored final video is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded)) {
            throw new InvalidVideoRenderException('Stored final video must be a JSON object.');
        }

        $targetLanguageValue = is_string($decoded['targetLanguage'] ?? null)
            ? $decoded['targetLanguage']
            : TranslationLanguage::Unknown->value;

        return TranslationLanguage::tryFrom($targetLanguageValue) ?? TranslationLanguage::Unknown;
    }
}
