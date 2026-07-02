<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\ShadowExecutive;

use App\Application\ShadowExecutive\EnergyAwarePlanner;
use App\Domain\ShadowExecutive\ExecutiveAgenda;
use App\Domain\ShadowExecutive\ExecutivePlan;
use App\Domain\ShadowExecutive\ExecutiveTask;
use App\Domain\ShadowExecutive\ExecutiveTaskCollection;
use App\Domain\ShadowExecutive\ExecutiveTaskType;
use PHPUnit\Framework\TestCase;

final class EnergyAwarePlannerTest extends TestCase
{
    public function testFiltersTodayTasksToAvailableMinutes(): void
    {
        $today = ExecutiveTaskCollection::empty()
            ->append(ExecutiveTask::create(ExecutiveTaskType::Review, 'Review Docker', '', 0))
            ->append(ExecutiveTask::create(ExecutiveTaskType::Mission, 'Mission', '', 1))
            ->append(ExecutiveTask::create(ExecutiveTaskType::Watch, 'Watch', '', 2));

        $plan = ExecutivePlan::create()
            ->withAgenda(new ExecutiveAgenda($today, ExecutiveTaskCollection::empty()))
            ->withAvailableMinutes(25);

        $filtered = (new EnergyAwarePlanner())->filterAgenda($plan);

        self::assertCount(1, $filtered->today()->all());
        self::assertSame(ExecutiveTaskType::Review, $filtered->today()->all()[0]->type());
    }

    public function testReturnsFullAgendaWhenNoAvailableMinutesSet(): void
    {
        $today = ExecutiveTaskCollection::empty()
            ->append(ExecutiveTask::create(ExecutiveTaskType::Review, 'Review Docker', '', 0))
            ->append(ExecutiveTask::create(ExecutiveTaskType::Mission, 'Mission', '', 1));

        $plan = ExecutivePlan::create()->withAgenda(new ExecutiveAgenda($today, ExecutiveTaskCollection::empty()));

        $filtered = (new EnergyAwarePlanner())->filterAgenda($plan);

        self::assertCount(2, $filtered->today()->all());
    }
}
