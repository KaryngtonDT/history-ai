<?php

declare(strict_types=1);

namespace App\Application\Hardware;

use App\Domain\Hardware\HardwareCapability;
use App\Domain\Hardware\HardwareDetectionReport;
use App\Domain\Hardware\HardwareProfile;
use App\Domain\Hardware\HardwareProfileType;

final class HardwareReportBuilder
{
    public function __construct(private readonly HardwareProfileClassifier $classifier)
    {
    }

    public function build(HardwareCapability $capabilities): HardwareDetectionReport
    {
        $profile = $this->classifier->classify($capabilities);

        return new HardwareDetectionReport(
            profile: $profile,
            capabilities: $capabilities,
            detectedAt: new \DateTimeImmutable(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function recommendedPipeline(HardwareProfile $profile): array
    {
        return match ($profile->type) {
            HardwareProfileType::LowEndLocal, HardwareProfileType::CpuOnly => [
                'speech' => 'faster_whisper_large_v3',
                'translation' => 'ollama_gemma3',
                'tts' => 'f5_tts',
                'voiceClone' => 'openvoice_v2',
                'lipSync' => 'wav2lip',
                'render' => 'ffmpeg_av1',
            ],
            HardwareProfileType::MidRangeNvidia => [
                'speech' => 'faster_whisper_large_v3',
                'translation' => 'ollama_gemma3',
                'tts' => 'f5_tts',
                'voiceClone' => 'openvoice_v2',
                'lipSync' => 'liveportrait',
                'render' => 'ffmpeg_nvenc',
            ],
            HardwareProfileType::HighEndNvidia, HardwareProfileType::EnterpriseGpu => [
                'speech' => 'faster_whisper_large_v3',
                'translation' => 'ollama_gemma3',
                'tts' => 'f5_tts',
                'voiceClone' => 'openvoice_v2',
                'lipSync' => 'latentsync',
                'render' => 'ffmpeg_nvenc',
            ],
            default => [
                'speech' => 'faster_whisper_large_v3',
                'translation' => 'ollama_gemma3',
                'tts' => 'f5_tts',
                'voiceClone' => 'openvoice_v2',
                'lipSync' => 'wav2lip',
                'render' => 'ffmpeg',
            ],
        };
    }
}
