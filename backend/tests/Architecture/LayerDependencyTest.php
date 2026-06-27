<?php

declare(strict_types=1);

namespace App\Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class LayerDependencyTest extends TestCase
{
    private string $srcRoot;

    protected function setUp(): void
    {
        $this->srcRoot = dirname(__DIR__, 2) . '/src';
    }

    public function testDomainDoesNotDependOnOuterLayersOrFrameworks(): void
    {
        $violations = LayerDependencyRules::findViolations(
            $this->srcRoot . '/Domain',
            [
                'Symfony\\',
                'Doctrine\\',
                'App\\Infrastructure\\',
                'App\\Presentation\\',
            ],
        );

        self::assertSame(
            [],
            $violations,
            "Domain layer dependency violations:\n" . implode("\n", $violations),
        );
    }

    public function testApplicationDoesNotDependOnInfrastructureOrPresentation(): void
    {
        $violations = LayerDependencyRules::findViolations(
            $this->srcRoot . '/Application',
            [
                'App\\Infrastructure\\',
                'App\\Presentation\\',
            ],
        );

        self::assertSame(
            [],
            $violations,
            "Application layer dependency violations:\n" . implode("\n", $violations),
        );
    }

    public function testPresentationDoesNotDependOnInfrastructure(): void
    {
        $violations = LayerDependencyRules::findViolations(
            $this->srcRoot . '/Presentation',
            [
                'App\\Infrastructure\\',
            ],
        );

        self::assertSame(
            [],
            $violations,
            "Presentation layer dependency violations:\n" . implode("\n", $violations),
        );
    }

    public function testInfrastructureDoesNotDependOnPresentation(): void
    {
        $violations = LayerDependencyRules::findViolations(
            $this->srcRoot . '/Infrastructure',
            [
                'App\\Presentation\\',
            ],
        );

        self::assertSame(
            [],
            $violations,
            "Infrastructure layer dependency violations:\n" . implode("\n", $violations),
        );
    }
}
