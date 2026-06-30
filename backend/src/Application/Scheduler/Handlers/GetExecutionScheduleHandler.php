<?php

declare(strict_types=1);

namespace App\Application\Scheduler\Handlers;

use App\Application\Scheduler\DTO\ExecutionScheduleResult;
use App\Application\Scheduler\Queries\GetExecutionScheduleQuery;
use App\Domain\Optimization\ExecutionOptimizerInterface;
use App\Domain\Scheduler\PipelineSchedulerInterface;
use App\Domain\Scheduler\RuntimeExecutionScheduleContextInterface;
use App\Domain\Video\Exception\InvalidVideoJobException;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\VideoIntelligence\VideoIntelligenceFactoryInterface;

final class GetExecutionScheduleHandler
{
    public function __construct(
        private readonly VideoRepositoryInterface $videoRepository,
        private readonly VideoIntelligenceFactoryInterface $videoIntelligenceFactory,
        private readonly ExecutionOptimizerInterface $executionOptimizer,
        private readonly PipelineSchedulerInterface $pipelineScheduler,
        private readonly RuntimeExecutionScheduleContextInterface $runtimeScheduleContext,
    ) {
    }

    public function __invoke(GetExecutionScheduleQuery $query): ExecutionScheduleResult
    {
        try {
            $videoId = new VideoId($query->videoId);
        } catch (InvalidVideoJobException) {
            throw new InvalidVideoJobException('Video not found.');
        }

        $job = $this->videoRepository->findById($videoId);

        if (null === $job) {
            throw new InvalidVideoJobException('Video not found.');
        }

        $runtimeSchedule = $this->runtimeScheduleContext->get();

        if (null !== $runtimeSchedule) {
            return ExecutionScheduleResult::fromSchedule($videoId->value, $runtimeSchedule);
        }

        $intelligence = $this->videoIntelligenceFactory->fromVideoJob($job);
        $optimization = $this->executionOptimizer->optimize($intelligence);
        $schedule = $this->pipelineScheduler->schedule($intelligence, $optimization);

        return ExecutionScheduleResult::fromSchedule($videoId->value, $schedule);
    }
}
