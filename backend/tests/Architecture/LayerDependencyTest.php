<?php

declare(strict_types=1);

namespace App\Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class LayerDependencyTest extends TestCase
{
    /** @var list<string> */
    private const DOMAIN_FORBIDDEN_PREFIXES = [
        'Symfony\\',
        'Doctrine\\',
        'App\\Infrastructure\\',
        'App\\Presentation\\',
    ];

    /** @var list<string> */
    private const APPLICATION_FORBIDDEN_PREFIXES = [
        'App\\Infrastructure\\',
        'App\\Presentation\\',
    ];

    private string $srcRoot;

    protected function setUp(): void
    {
        $this->srcRoot = dirname(__DIR__, 2) . '/src';
    }

    public function testDomainDoesNotDependOnOuterLayersOrFrameworks(): void
    {
        $this->assertNoViolations(
            'Domain layer',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Domain',
                self::DOMAIN_FORBIDDEN_PREFIXES,
            ),
        );
    }

    public function testSearchDomainDoesNotDependOnOuterLayersOrFrameworks(): void
    {
        $this->assertNoViolations(
            'Search domain',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Domain/Search',
                self::DOMAIN_FORBIDDEN_PREFIXES,
            ),
        );
    }

    public function testTimelineDomainDoesNotDependOnOuterLayersOrFrameworks(): void
    {
        $this->assertNoViolations(
            'Timeline domain',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Domain/Timeline',
                self::DOMAIN_FORBIDDEN_PREFIXES,
            ),
        );
    }

    public function testApplicationDoesNotDependOnInfrastructureOrPresentation(): void
    {
        $this->assertNoViolations(
            'Application layer',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Application',
                self::APPLICATION_FORBIDDEN_PREFIXES,
            ),
        );
    }

    public function testSearchApplicationMayDependOnSearchDomainOnly(): void
    {
        $this->assertNoViolations(
            'Search application',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Application/Search',
                self::APPLICATION_FORBIDDEN_PREFIXES,
            ),
        );
    }

    public function testPresentationDoesNotDependOnInfrastructure(): void
    {
        $this->assertNoViolations(
            'Presentation layer',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Presentation',
                ['App\\Infrastructure\\'],
            ),
        );
    }

    public function testSearchPresentationMayDependOnSearchApplicationOnly(): void
    {
        $searchPresentationPaths = [
            $this->srcRoot . '/Presentation/Http/Controller/Search',
            $this->srcRoot . '/Presentation/Http/Request/Search',
            $this->srcRoot . '/Presentation/Http/Response/Search',
        ];

        $violations = [];

        foreach ($searchPresentationPaths as $path) {
            $violations = array_merge(
                $violations,
                LayerDependencyRules::findViolations($path, ['App\\Infrastructure\\']),
            );
        }

        $this->assertNoViolations('Search presentation', $violations);
    }

    public function testInfrastructureDoesNotDependOnPresentation(): void
    {
        $this->assertNoViolations(
            'Infrastructure layer',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Infrastructure',
                ['App\\Presentation\\'],
            ),
        );
    }

    public function testSearchInfrastructureMayDependOnSearchAndLibraryDomain(): void
    {
        $this->assertNoViolations(
            'Search infrastructure',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Infrastructure/Persistence/Doctrine/Search',
                ['App\\Presentation\\'],
            ),
        );
    }

    /**
     * @param list<string> $violations
     */
    private function assertNoViolations(string $scope, array $violations): void
    {
        self::assertSame(
            [],
            $violations,
            sprintf("%s dependency violations:\n%s", $scope, implode("\n", $violations)),
        );
    }
}
