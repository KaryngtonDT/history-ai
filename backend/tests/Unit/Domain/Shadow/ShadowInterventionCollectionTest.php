<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shadow;

use App\Domain\Shadow\ShadowChallenge;
use App\Domain\Shadow\ShadowIntervention;
use App\Domain\Shadow\ShadowInterventionCollection;
use App\Domain\Shadow\ShadowInterventionId;
use App\Domain\Shadow\ShadowInterventionTrigger;
use App\Domain\Shadow\ShadowInterventionType;
use App\Domain\Shadow\ShadowTimestamp;
use PHPUnit\Framework\TestCase;

final class ShadowInterventionCollectionTest extends TestCase
{
    public function testAppendsInterventions(): void
    {
        $collection = ShadowInterventionCollection::empty();
        $first = $this->interventionAt(1.0);
        $second = $this->interventionAt(2.0);

        $collection = $collection->append($first)->append($second);

        self::assertSame(2, $collection->count());
        self::assertSame($first->id()->value, $collection->all()[0]->id()->value);
    }

    public function testFindsAndReplacesIntervention(): void
    {
        $intervention = $this->interventionAt(3.0);
        $collection = ShadowInterventionCollection::empty()->append($intervention);
        $skipped = $intervention->markSkipped();
        $updated = $collection->replace($skipped);

        self::assertTrue($updated->findById($intervention->id())?->isSkipped());
    }

    private function interventionAt(float $time): ShadowIntervention
    {
        return ShadowIntervention::create(
            ShadowInterventionId::generate(),
            ShadowInterventionType::ConceptCheck,
            ShadowInterventionTrigger::RepeatedConcept,
            'Concept repeated.',
            ShadowTimestamp::fromSeconds($time),
            'Explain in your own words',
            allowAutoPause: false,
            challenge: ShadowChallenge::create('What is the main idea in your own words?'),
        );
    }
}
