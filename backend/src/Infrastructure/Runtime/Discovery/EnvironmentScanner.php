<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Discovery;

final class EnvironmentScanner
{
    /**
     * @return array<string, string>
     */
    public function activeProviders(
        string $sttProvider,
        string $translationProvider,
        string $ttsProvider,
        string $voiceCloneProvider,
        string $lipSyncProvider,
        string $videoRenderProvider,
    ): array {
        return [
            'speech_to_text' => $this->normalize($sttProvider, 'faster_whisper'),
            'translation' => $this->normalize($translationProvider, 'ollama'),
            'text_to_speech' => $this->normalize($ttsProvider, 'f5'),
            'voice_clone' => $this->normalize($voiceCloneProvider, 'openvoice'),
            'lip_sync' => $this->normalize($lipSyncProvider, 'latentsync'),
            'video_render' => $this->normalize($videoRenderProvider, 'ffmpeg'),
        ];
    }

    private function normalize(string $value, string $fallback): string
    {
        $normalized = strtolower(trim($value));

        return '' === $normalized ? $fallback : $normalized;
    }
}
