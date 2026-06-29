<?php

declare(strict_types=1);

namespace App\Application\Speech;

use App\Domain\Speech\Exception\InvalidTranscriptException;
use App\Domain\Speech\Transcript;
use App\Domain\Speech\TranscriptId;
use App\Domain\Speech\TranscriptLanguage;
use App\Domain\Speech\TranscriptSegment;
use App\Domain\Speech\TranscriptSegmentCollection;
use JsonException;

final class TranscriptJsonMapper
{
    /**
     * @return array{
     *     transcriptId: string,
     *     language: string,
     *     text: string,
     *     duration: float,
     *     segmentCount: int,
     *     segments: list<array{index: int, startTime: float, endTime: float, text: string}>
     * }
     */
    public function toArray(Transcript $transcript): array
    {
        /** @var list<array{index: int, startTime: float, endTime: float, text: string}> $segments */
        $segments = [];

        foreach ($transcript->segments()->all() as $segment) {
            $segments[] = [
                'index' => $segment->index(),
                'startTime' => $segment->startTime(),
                'endTime' => $segment->endTime(),
                'text' => $segment->text(),
            ];
        }

        return [
            'transcriptId' => $transcript->transcriptId()->value,
            'language' => $transcript->language()->value,
            'text' => $transcript->text(),
            'duration' => $transcript->duration(),
            'segmentCount' => $transcript->segmentCount(),
            'segments' => $segments,
        ];
    }

    public function toJson(Transcript $transcript): string
    {
        return json_encode($this->toArray($transcript), JSON_THROW_ON_ERROR);
    }

    public function fromJson(string $json): Transcript
    {
        try {
            /** @var mixed $decoded */
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidTranscriptException('Stored transcript is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded)) {
            throw new InvalidTranscriptException('Stored transcript must be a JSON object.');
        }

        $transcriptId = is_string($decoded['transcriptId'] ?? null)
            ? $decoded['transcriptId']
            : null;
        $languageValue = is_string($decoded['language'] ?? null)
            ? $decoded['language']
            : TranscriptLanguage::Unknown->value;

        if (null === $transcriptId) {
            throw new InvalidTranscriptException('Stored transcript must include transcriptId.');
        }

        $language = TranscriptLanguage::tryFrom($languageValue) ?? TranscriptLanguage::Unknown;
        $rawSegments = $decoded['segments'] ?? [];

        if (!is_array($rawSegments)) {
            throw new InvalidTranscriptException('Stored transcript must include segments array.');
        }

        /** @var list<TranscriptSegment> $segments */
        $segments = [];

        foreach (array_values($rawSegments) as $position => $rawSegment) {
            if (!is_array($rawSegment)) {
                continue;
            }

            $text = is_string($rawSegment['text'] ?? null) ? trim($rawSegment['text']) : '';

            if ('' === $text) {
                continue;
            }

            $index = is_int($rawSegment['index'] ?? null)
                ? $rawSegment['index']
                : $position;
            $startTime = is_numeric($rawSegment['startTime'] ?? null)
                ? (float) $rawSegment['startTime']
                : 0.0;
            $endTime = is_numeric($rawSegment['endTime'] ?? null)
                ? (float) $rawSegment['endTime']
                : $startTime;

            $segments[] = TranscriptSegment::create($index, $startTime, $endTime, $text);
        }

        return Transcript::create(
            new TranscriptId($transcriptId),
            $language,
            new TranscriptSegmentCollection($segments),
        );
    }
}
