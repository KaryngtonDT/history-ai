<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\ShadowPresence;

use App\Application\ShadowPresence\PresencePermissionEvaluator;
use App\Domain\ShadowPresence\PresenceCapability;
use App\Domain\ShadowPresence\PresencePreferences;
use PHPUnit\Framework\TestCase;

final class PresencePermissionEvaluatorTest extends TestCase
{
    public function testDefaultPermissionsGrantCoreCapabilitiesOnConnect(): void
    {
        $evaluator = new PresencePermissionEvaluator();
        $preferences = PresencePreferences::default();

        self::assertTrue($evaluator->isGranted($preferences, PresenceCapability::AskQuestion));
        self::assertTrue($evaluator->isGranted($preferences, PresenceCapability::SearchBrain));
        self::assertTrue($evaluator->isGranted($preferences, PresenceCapability::ResumeConversation));
        self::assertFalse($evaluator->isGranted($preferences, PresenceCapability::ReadSelection));
        self::assertFalse($evaluator->isGranted($preferences, PresenceCapability::ProactiveHint));
    }

    public function testProactiveHintRequiresProactiveEnabled(): void
    {
        $evaluator = new PresencePermissionEvaluator();
        $preferences = $evaluator->applyPermissionUpdates(
            PresencePreferences::default(),
            [['capability' => PresenceCapability::ProactiveHint->value, 'granted' => true]],
        );

        self::assertFalse($evaluator->isGranted($preferences, PresenceCapability::ProactiveHint));

        $enabled = $preferences->withUpdates(proactiveEnabled: true);
        self::assertTrue($evaluator->isGranted($enabled, PresenceCapability::ProactiveHint));
    }

    public function testAssertGrantedThrowsWhenCapabilityDenied(): void
    {
        $evaluator = new PresencePermissionEvaluator();
        $preferences = PresencePreferences::default();

        $this->expectException(\DomainException::class);
        $evaluator->assertGranted($preferences, PresenceCapability::ReadWorkspace);
    }
}
