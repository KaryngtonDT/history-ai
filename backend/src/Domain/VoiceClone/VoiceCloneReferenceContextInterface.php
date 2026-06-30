<?php

declare(strict_types=1);

namespace App\Domain\VoiceClone;

interface VoiceCloneReferenceContextInterface
{
    /**
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     */
    public function withReference(string $referenceAudioPath, callable $callback): mixed;

    public function referenceAudioPath(): ?string;
}
