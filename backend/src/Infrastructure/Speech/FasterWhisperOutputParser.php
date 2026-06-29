<?php

declare(strict_types=1);

namespace App\Infrastructure\Speech;

use App\Domain\Speech\Transcript;
use App\Domain\Speech\TranscriptId;
use App\Domain\Speech\TranscriptSegment;
use App\Domain\Speech\TranscriptSegmentCollection;
use App\Infrastructure\Speech\Exception\FasterWhisperProviderException;
use JsonException;

final class FasterWhisperOutputParser
{
    public function parse(string $json): Transcript
    {
        try {
            /** @var mixed $decoded */
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new FasterWhisperProviderException('Faster-Whisper output is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded)) {
            throw new FasterWhisperProviderException('Faster-Whisper output must be a JSON object.');
        }

        $languageCode = is_string($decoded['language'] ?? null) ? $decoded['language'] : 'unknown';
        $rawSegments = $decoded['segments'] ?? [];

        if (!is_array($rawSegments)) {
            throw new FasterWhisperProviderException('Faster-Whisper output must include a segments array.');
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
            $start = is_numeric($rawSegment['start'] ?? null)
                ? (float) $rawSegment['start']
                : 0.0;
            $end = is_numeric($rawSegment['end'] ?? null)
                ? (float) $rawSegment['end']
                : $start;

            $segments[] = TranscriptSegment::create($index, $start, $end, $text);
        }

        return Transcript::create(
            TranscriptId::generate(),
            TranscriptLanguageMapper::fromProviderCode($languageCode),
            new TranscriptSegmentCollection($segments),
        );
    }
}
