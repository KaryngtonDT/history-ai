<?php

declare(strict_types=1);

namespace App\Infrastructure\VoiceClone;

use App\Domain\VoiceClone\VoiceCloneReferenceContextInterface;

final class VoiceCloneProcessingContext implements VoiceCloneReferenceContextInterface
{
    private ?string $referenceAudioPath = null;

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
