<?php

declare(strict_types=1);

namespace App\Infrastructure\VoiceClone;

final class VoiceCloneProcessingContext
{
    private ?string $referenceAudioPath = null;

    /**
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     */
    public function withReference(string $referenceAudioPath, callable $callback): mixed
    {
        $previous = $this->referenceAudioPath;
        $this->referenceAudioPath = $referenceAudioPath;

        try {
            return $callback();
        } finally {
            $this->referenceAudioPath = $previous;
        }
    }

    public function referenceAudioPath(): ?string
    {
        return $this->referenceAudioPath;
    }
}
