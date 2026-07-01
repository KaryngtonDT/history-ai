<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shadow;

use App\Domain\Shadow\ShadowIntervention;
use App\Domain\Shadow\ShadowInterventionCollection;
use App\Domain\Shadow\ShadowInterventionId;
use App\Domain\Shadow\ShadowInterventionPolicy;
use App\Domain\Shadow\ShadowInterventionTrigger;
use App\Domain\Shadow\ShadowInterventionType;
use App\Domain\Shadow\ShadowTimestamp;
use PHPUnit\Framework\TestCase;

final class ShadowInterventionPolicyTest extends TestCase
{
    public function testDisabledPolicyBlocksInterventions(): void
    {
        $policy = ShadowInterventionPolicy::disabled();

        self::assertFalse($policy->canScheduleIntervention(
            10.0,
            ShadowInterventionCollection::empty(),
        ));
    }

    public function testThrottlesInterventionsTooCloseTogether(): void
    {
        $policy = ShadowInterventionPolicy::gentleDefault();
        $collection = ShadowInterventionCollection::empty()->append(
            $this->sampleIntervention(10.0),
        );

        self::assertFalse($policy->canScheduleIntervention(20.0, $collection));
        self::assertTrue($policy->canScheduleIntervention(60.0, $collection));
    }

    public function testLimitsInterventionsPerMinute(): void
    {
        $policy = ShadowInterventionPolicy::gentleDefault()->withFrequency(2, 5.0);
        $collection = ShadowInterventionCollection::empty()
            ->append($this->sampleIntervention(50.0))
            ->append($this->sampleIntervention(80.0));

        self::assertFalse($policy->canScheduleIntervention(90.0, $collection));
    }

    private function sampleIntervention(float $time): ShadowIntervention
    {
        return ShadowIntervention::create(
            ShadowInterventionId::generate(),
            ShadowInterventionType::ReflectionPrompt,
            ShadowInterventionTrigger::LongSilence,
            'No interaction for a while.',
            ShadowTimestamp::fromSeconds($time),
            'Reflect on what you heard',
            allowAutoPause: false,
        );
    }
}
