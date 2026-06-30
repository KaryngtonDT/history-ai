<?php

declare(strict_types=1);

namespace App\Domain\TTS;

final readonly class VoiceCollection
{
    /** @var list<Voice> */
    private array $voices;

    /**
     * @param list<Voice> $voices
     */
    public function __construct(array $voices)
    {
        $this->voices = array_values($voices);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<Voice>
     */
    public function all(): array
    {
        return $this->voices;
    }

    public function count(): int
    {
        return count($this->voices);
    }

    public function isEmpty(): bool
    {
        return [] === $this->voices;
    }

    public function findById(string $voiceId): ?Voice
    {
        foreach ($this->voices as $voice) {
            if ($voice->voiceId() === $voiceId) {
                return $voice;
            }
        }

        return null;
    }

    public function append(Voice $voice): self
    {
        return new self([...$this->voices, $voice]);
    }
}
