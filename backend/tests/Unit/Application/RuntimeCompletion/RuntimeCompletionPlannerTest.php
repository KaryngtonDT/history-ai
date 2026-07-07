<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\RuntimeCompletion;

use App\Application\Runtime\RuntimePlatformInterface;
use App\Application\RuntimeCompletion\RuntimeCompletionPlanner;
use App\Application\RuntimeDashboard\RuntimeDashboardInterface;
use PHPUnit\Framework\TestCase;

final class RuntimeCompletionPlannerTest extends TestCase
{
    public function testPlansOnlyRecommendedMissingEngines(): void
    {
        $dashboard = $this->createStub(RuntimeDashboardInterface::class);
        $platform = $this->createStub(RuntimePlatformInterface::class);

        $dashboard->method('dashboard')->willReturn([
            'summary' => [
                'hardwareProfile' => 'cpu_only',
                'hardwareProfileLabel' => 'CPU Only',
            ],
            'overallRuntimeScore' => ['score' => 40.0],
            'platformScore' => ['score' => 50.0],
            'hardware' => [
                'recommendedPipeline' => [
                    'speech' => 'faster_whisper_large_v3',
                    'lipSync' => 'wav2lip',
                ],
            ],
            'engineRecommendations' => [],
        ]);

        $platform->method('readiness')->willReturn([
            'engines' => [
                [
                    'id' => 'faster_whisper_large_v3',
                    'displayName' => 'Faster Whisper',
                    'capability' => 'speech_to_text',
                    'status' => 'ready',
                    'tier' => 'default',
                    'compatibility' => ['hardwareCompatible' => true, 'blockedReasonCode' => 'none'],
                ],
                [
                    'id' => 'wav2lip',
                    'displayName' => 'Wav2Lip',
                    'capability' => 'lip_sync',
                    'status' => 'blocked',
                    'tier' => 'cpu_alternative',
                    'compatibility' => [
                        'hardwareCompatible' => true,
                        'blockedReasonCode' => 'model_missing',
                        'canBeFixedByInstall' => true,
                        'humanReason' => 'Model files missing',
                    ],
                ],
                [
                    'id' => 'whisper_cpp',
                    'displayName' => 'Whisper.cpp',
                    'capability' => 'speech_to_text',
                    'status' => 'blocked',
                    'tier' => 'cpu_alternative',
                    'compatibility' => [
                        'hardwareCompatible' => true,
                        'blockedReasonCode' => 'model_missing',
                        'canBeFixedByInstall' => true,
                    ],
                ],
            ],
        ]);

        $planner = new RuntimeCompletionPlanner($dashboard, $platform);
        $plan = $planner->plan();

        self::assertFalse($plan['hardwareRedetected']);
        self::assertSame(1, $plan['completionCount']);
        self::assertSame('wav2lip', $plan['compatibleEngineCompletionPlan'][0]['engineId']);
    }
}
