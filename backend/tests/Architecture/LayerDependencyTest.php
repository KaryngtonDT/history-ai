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

    public function testMapDomainDoesNotDependOnOuterLayersOrFrameworks(): void
    {
        $this->assertNoViolations(
            'Map domain',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Domain/Map',
                self::DOMAIN_FORBIDDEN_PREFIXES,
            ),
        );
    }

    public function testRelationDomainDoesNotDependOnOuterLayersOrFrameworks(): void
    {
        $this->assertNoViolations(
            'Relation domain',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Domain/Relation',
                self::DOMAIN_FORBIDDEN_PREFIXES,
            ),
        );
    }

    public function testGraphDomainDoesNotDependOnOuterLayersOrFrameworks(): void
    {
        $this->assertNoViolations(
            'Graph domain',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Domain/Graph',
                self::DOMAIN_FORBIDDEN_PREFIXES,
            ),
        );
    }

    public function testRecommendationDomainDoesNotDependOnOuterLayersOrFrameworks(): void
    {
        $this->assertNoViolations(
            'Recommendation domain',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Domain/Recommendation',
                self::DOMAIN_FORBIDDEN_PREFIXES,
            ),
        );
    }

    public function testSemanticDomainDoesNotDependOnOuterLayersOrFrameworks(): void
    {
        $this->assertNoViolations(
            'Semantic domain',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Domain/Semantic',
                self::DOMAIN_FORBIDDEN_PREFIXES,
            ),
        );
    }

    public function testChatDomainDoesNotDependOnOuterLayersOrFrameworks(): void
    {
        self::assertFileExists($this->srcRoot . '/Domain/Chat/ChatOrchestrator.php');

        $this->assertNoViolations(
            'Chat domain',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Domain/Chat',
                self::DOMAIN_FORBIDDEN_PREFIXES,
            ),
        );
    }

    public function testPlatformDomainDoesNotDependOnOuterLayersOrFrameworks(): void
    {
        self::assertFileExists($this->srcRoot . '/Domain/Platform/CorrelationId.php');

        $this->assertNoViolations(
            'Platform domain',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Domain/Platform',
                self::DOMAIN_FORBIDDEN_PREFIXES,
            ),
        );
    }

    public function testSemanticVectorDomainDoesNotDependOnOuterLayersOrFrameworks(): void
    {
        self::assertFileExists($this->srcRoot . '/Domain/Semantic/VectorStoreInterface.php');

        $this->assertNoViolations(
            'Semantic vector store domain',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Domain/Semantic',
                self::DOMAIN_FORBIDDEN_PREFIXES,
            ),
        );
    }

    public function testRecommendationScoringDomainDoesNotDependOnOuterLayersOrFrameworks(): void
    {
        self::assertFileExists($this->srcRoot . '/Domain/Recommendation/RecommendationScoringEngine.php');

        $this->assertNoViolations(
            'Recommendation scoring domain',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Domain/Recommendation',
                self::DOMAIN_FORBIDDEN_PREFIXES,
            ),
        );
    }

    public function testApplicationDoesNotDependOnInfrastructureOrPresentation(): void
    {
        $violations = [];

        foreach (glob($this->srcRoot . '/Application/*', GLOB_ONLYDIR) ?: [] as $subdir) {
            if (str_ends_with(str_replace('\\', '/', $subdir), '/Application/Runtime')) {
                continue;
            }

            $violations = array_merge(
                $violations,
                LayerDependencyRules::findViolations($subdir, self::APPLICATION_FORBIDDEN_PREFIXES),
            );
        }

        $this->assertNoViolations(
            'Application layer (excluding Runtime catalog integration)',
            $violations,
        );
    }

    public function testRuntimeApplicationMayUseRuntimeInfrastructureCatalog(): void
    {
        self::assertFileExists($this->srcRoot . '/Application/Runtime/EngineCompatibilityEvaluator.php');

        $this->assertNoViolations(
            'Runtime application',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Application/Runtime',
                ['App\\Presentation\\'],
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

    public function testTimelineApplicationMayDependOnTimelineDomainOnly(): void
    {
        $this->assertNoViolations(
            'Timeline application',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Application/Timeline',
                self::APPLICATION_FORBIDDEN_PREFIXES,
            ),
        );
    }

    public function testMapApplicationMayDependOnMapAndTimelineDomainOnly(): void
    {
        $this->assertNoViolations(
            'Map application',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Application/Map',
                self::APPLICATION_FORBIDDEN_PREFIXES,
            ),
        );
    }

    public function testRelationApplicationMayDependOnRelationArtifactAndContentDomainOnly(): void
    {
        $this->assertNoViolations(
            'Relation application',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Application/Relation',
                self::APPLICATION_FORBIDDEN_PREFIXES,
            ),
        );
    }

    public function testGraphApplicationMayDependOnGraphRelationArtifactAndContentDomainOnly(): void
    {
        $this->assertNoViolations(
            'Graph application',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Application/Graph',
                self::APPLICATION_FORBIDDEN_PREFIXES,
            ),
        );
    }

    public function testRecommendationApplicationMayDependOnRecommendationGraphRelationArtifactAndContentDomainOnly(): void
    {
        $this->assertNoViolations(
            'Recommendation application',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Application/Recommendation',
                self::APPLICATION_FORBIDDEN_PREFIXES,
            ),
        );
    }

    public function testSemanticApplicationMayDependOnSemanticArtifactAndContentDomainOnly(): void
    {
        $this->assertNoViolations(
            'Semantic application',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Application/Semantic',
                self::APPLICATION_FORBIDDEN_PREFIXES,
            ),
        );
    }

    public function testChatApplicationMayDependOnChatSemanticArtifactAndContentDomainOnly(): void
    {
        self::assertFileExists($this->srcRoot . '/Application/Chat/Handlers/AskContentChatHandler.php');

        $this->assertNoViolations(
            'Chat application',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Application/Chat',
                self::APPLICATION_FORBIDDEN_PREFIXES,
            ),
        );
    }

    public function testPresentationDoesNotDependOnInfrastructure(): void
    {
        $presentationPaths = [
            $this->srcRoot . '/Presentation/Http',
            $this->srcRoot . '/Presentation/OpenApi',
        ];

        $violations = [];

        foreach ($presentationPaths as $path) {
            $violations = array_merge(
                $violations,
                LayerDependencyRules::findViolations($path, ['App\\Infrastructure\\']),
            );
        }

        $this->assertNoViolations('Presentation layer (HTTP/OpenAPI)', $violations);
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

    public function testTimelinePresentationMayDependOnTimelineApplicationOnly(): void
    {
        $timelinePresentationPaths = [
            $this->srcRoot . '/Presentation/Http/Controller/Timeline',
            $this->srcRoot . '/Presentation/Http/Response/Timeline',
            $this->srcRoot . '/Presentation/OpenApi/Schema/Timeline.php',
            $this->srcRoot . '/Presentation/OpenApi/Schema/TimelineSection.php',
            $this->srcRoot . '/Presentation/OpenApi/Schema/TimelineEvent.php',
        ];

        $violations = [];

        foreach ($timelinePresentationPaths as $path) {
            $violations = array_merge(
                $violations,
                LayerDependencyRules::findViolations($path, ['App\\Infrastructure\\']),
            );
        }

        $this->assertNoViolations('Timeline presentation', $violations);
    }

    public function testMapPresentationMayDependOnMapApplicationOnly(): void
    {
        $mapPresentationPaths = [
            $this->srcRoot . '/Presentation/Http/Controller/Map',
            $this->srcRoot . '/Presentation/Http/Response/Map',
            $this->srcRoot . '/Presentation/OpenApi/Schema/Map.php',
            $this->srcRoot . '/Presentation/OpenApi/Schema/HistoricalPlace.php',
            $this->srcRoot . '/Presentation/OpenApi/Schema/Coordinates.php',
        ];

        $violations = [];

        foreach ($mapPresentationPaths as $path) {
            $violations = array_merge(
                $violations,
                LayerDependencyRules::findViolations($path, ['App\\Infrastructure\\']),
            );
        }

        $this->assertNoViolations('Map presentation', $violations);
    }

    public function testRelationPresentationMayDependOnRelationApplicationOnly(): void
    {
        $relationPresentationPaths = [
            $this->srcRoot . '/Presentation/Http/Controller/Relation',
            $this->srcRoot . '/Presentation/Http/Response/Relation',
            $this->srcRoot . '/Presentation/OpenApi/Schema/ArtifactRelation.php',
            $this->srcRoot . '/Presentation/OpenApi/Schema/ArtifactRelations.php',
            $this->srcRoot . '/Presentation/OpenApi/Schema/ArtifactRelationTypeSchema.php',
        ];

        $violations = [];

        foreach ($relationPresentationPaths as $path) {
            $violations = array_merge(
                $violations,
                LayerDependencyRules::findViolations($path, ['App\\Infrastructure\\']),
            );
        }

        $this->assertNoViolations('Relation presentation', $violations);
    }

    public function testGraphPresentationMayDependOnGraphApplicationOnly(): void
    {
        $graphPresentationPaths = [
            $this->srcRoot . '/Presentation/Http/Controller/Graph',
            $this->srcRoot . '/Presentation/Http/Response/Graph',
            $this->srcRoot . '/Presentation/OpenApi/Schema/GraphNode.php',
            $this->srcRoot . '/Presentation/OpenApi/Schema/GraphEdge.php',
            $this->srcRoot . '/Presentation/OpenApi/Schema/KnowledgeGraph.php',
        ];

        $violations = [];

        foreach ($graphPresentationPaths as $path) {
            $violations = array_merge(
                $violations,
                LayerDependencyRules::findViolations($path, ['App\\Infrastructure\\']),
            );
        }

        $this->assertNoViolations('Graph presentation', $violations);
    }

    public function testRecommendationPresentationMayDependOnRecommendationApplicationOnly(): void
    {
        $recommendationPresentationPaths = [
            $this->srcRoot . '/Presentation/Http/Controller/Recommendation',
            $this->srcRoot . '/Presentation/Http/Response/Recommendation',
            $this->srcRoot . '/Presentation/OpenApi/Schema/RecommendedArtifact.php',
            $this->srcRoot . '/Presentation/OpenApi/Schema/ArtifactRecommendations.php',
            $this->srcRoot . '/Presentation/OpenApi/Schema/RecommendationReasonSchema.php',
        ];

        $violations = [];

        foreach ($recommendationPresentationPaths as $path) {
            $violations = array_merge(
                $violations,
                LayerDependencyRules::findViolations($path, ['App\\Infrastructure\\']),
            );
        }

        $this->assertNoViolations('Recommendation presentation', $violations);
    }

    public function testSemanticPresentationMayDependOnSemanticApplicationOnly(): void
    {
        $semanticPresentationPaths = [
            $this->srcRoot . '/Presentation/Http/Controller/Semantic',
            $this->srcRoot . '/Presentation/Http/Response/Semantic',
        ];

        $violations = [];

        foreach ($semanticPresentationPaths as $path) {
            $violations = array_merge(
                $violations,
                LayerDependencyRules::findViolations($path, ['App\\Infrastructure\\']),
            );
        }

        $this->assertNoViolations('Semantic presentation', $violations);
    }

    public function testChatPresentationMayDependOnChatApplicationOnly(): void
    {
        $chatPresentationPaths = [
            $this->srcRoot . '/Presentation/Http/Controller/Chat',
            $this->srcRoot . '/Presentation/Http/Request/Chat',
            $this->srcRoot . '/Presentation/Http/Response/Chat',
        ];

        $violations = [];

        foreach ($chatPresentationPaths as $path) {
            $violations = array_merge(
                $violations,
                LayerDependencyRules::findViolations($path, ['App\\Infrastructure\\']),
            );
        }

        $this->assertNoViolations('Chat presentation', $violations);
    }

    public function testConsolePresentationMayDependOnInfrastructure(): void
    {
        self::assertFileExists($this->srcRoot . '/Presentation/Console/Command/Semantic/GeminiEmbeddingSmokeTestCommand.php');

        $this->assertNoViolations(
            'Console presentation',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Presentation/Console',
                ['App\\Application\\'],
            ),
        );
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

    public function testSemanticInfrastructureMayDependOnSemanticDomainOnly(): void
    {
        self::assertFileExists($this->srcRoot . '/Infrastructure/Semantic/InMemoryVectorStore.php');

        $this->assertNoViolations(
            'Semantic infrastructure',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Infrastructure/Semantic',
                ['App\\Presentation\\', 'App\\Application\\'],
            ),
        );
    }

    public function testChatInfrastructureMayDependOnChatDomainOnly(): void
    {
        self::assertFileExists($this->srcRoot . '/Infrastructure/Chat/MockChatProvider.php');

        $this->assertNoViolations(
            'Chat infrastructure',
            LayerDependencyRules::findViolations(
                $this->srcRoot . '/Infrastructure/Chat',
                ['App\\Presentation\\', 'App\\Application\\'],
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
