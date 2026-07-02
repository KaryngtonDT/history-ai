<?php

declare(strict_types=1);

namespace App\Application\ShadowExecutive;

use App\Domain\ShadowExecutive\ExecutiveAgenda;
use App\Domain\ShadowExecutive\ExecutivePlan;
use App\Domain\ShadowExecutive\ExecutiveTaskCollection;
use App\Domain\ShadowExecutive\ExecutiveTaskType;

final class EnergyAwarePlanner
{
    /** @var array<string, int> */
    private const array TASK_MINUTES = [
        ExecutiveTaskType::Review->value => 15,
        ExecutiveTaskType::Mission->value => 30,
        ExecutiveTaskType::Watch->value => 20,
        ExecutiveTaskType::Exercise->value => 10,
        ExecutiveTaskType::Checkpoint->value => 10,
        ExecutiveTaskType::Pause->value => 5,
    ];

    public function filterAgenda(ExecutivePlan $plan): ExecutiveAgenda
    {
        $availableMinutes = $plan->availableMinutes();

        if (null === $availableMinutes || $availableMinutes <= 0) {
            return $plan->agenda();
        }

        $filtered = ExecutiveTaskCollection::empty();
        $remaining = $availableMinutes;

        foreach ($plan->agenda()->today()->all() as $task) {
            $minutes = self::TASK_MINUTES[$task->type()->value] ?? 15;

            if ($minutes > $remaining && $filtered->all() !== []) {
                break;
            }

            if ($minutes <= $remaining) {
                $filtered = $filtered->append($task);
                $remaining -= $minutes;
            }
        }

        return $plan->agenda()->withToday($filtered);
    }
}
