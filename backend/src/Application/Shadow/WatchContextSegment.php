<?php

declare(strict_types=1);

namespace App\Application\Shadow;

use App\Domain\Speech\TranscriptSegment;
use App\Domain\Translation\TranslationSegment;

final readonly class WatchContextSegment
{
    public function __construct(
        public int $index,
        public float $startTime,
        public float $endTime,
        public string $text,
        public ?string $translatedText = null,
    ) {
    }

    public static function fromTranscript(TranscriptSegment $segment, ?TranslationSegment $translation = null): self
    {
        return new self(
            index: $segment->index(),
            startTime: $segment->startTime(),
            endTime: $segment->endTime(),
            text: $segment->text(),
            translatedText: null !== $translation ? $translation->translatedText() : null,
        );
    }

  /**
   * @return array{
   *     index: int,
   *     startTime: float,
   *     endTime: float,
   *     text: string,
   *     translatedText?: string
   * }
   */
    public function toArray(): array
    {
        $data = [
            'index' => $this->index,
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
            'text' => $this->text,
        ];

        if (null !== $this->translatedText) {
            $data['translatedText'] = $this->translatedText;
        }

        return $data;
    }
}
