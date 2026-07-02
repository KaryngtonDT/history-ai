<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\ShadowConfiguration;

use App\Application\ShadowConfiguration\ShadowConfigurationConfirmation;
use App\Application\ShadowConfiguration\ShadowConfigurationExecutor;
use App\Application\ShadowConfiguration\ShadowConfigurationIntent;
use App\Application\ShadowConfiguration\ShadowConfigurationIntentDetector;
use App\Application\ShadowConfiguration\ShadowConfigurationInterpreter;
use App\Application\ShadowIdentity\ShadowIdentityJsonMapper;
use App\Infrastructure\ShadowIdentity\InMemoryShadowIdentityRepository;
use PHPUnit\Framework\TestCase;

final class ShadowConfigurationInterpreterTest extends TestCase
{
    private ShadowConfigurationInterpreter $interpreter;

    protected function setUp(): void
    {
        $this->interpreter = new ShadowConfigurationInterpreter(
            new ShadowConfigurationIntentDetector(),
            new ShadowConfigurationExecutor(),
            new ShadowConfigurationConfirmation(),
            new InMemoryShadowIdentityRepository(),
            new ShadowIdentityJsonMapper(),
        );
    }

    public function testDetectsSlowerSpeechWithoutApplyingUntilConfirmedForReset(): void
    {
        $result = $this->interpreter->interpret('Shadow parle moins vite.');

        self::assertSame(ShadowConfigurationIntent::ChangeSpeed->value, $result['intent']);
        self::assertTrue($result['applied']);
        self::assertSame(0.9, $result['profile']['preferences']['voiceProfile']['speed']);
    }

    public function testStorytellerPersonaCommand(): void
    {
        $result = $this->interpreter->interpret('Utilise une voix de conteur.');

        self::assertSame(ShadowConfigurationIntent::ChangePersona->value, $result['intent']);
        self::assertSame('storyteller', $result['profile']['preferences']['persona']);
    }

    public function testResetRequiresConfirmation(): void
    {
        $pending = $this->interpreter->interpret('Réinitialise complètement ton profil.');
        self::assertTrue($pending['requiresConfirmation']);
        self::assertFalse($pending['applied']);

        $applied = $this->interpreter->interpret(
            'Réinitialise complètement ton profil.',
            confirmed: true,
        );
        self::assertTrue($applied['applied']);
        self::assertSame('teacher', $applied['profile']['preferences']['persona']);
    }
}
