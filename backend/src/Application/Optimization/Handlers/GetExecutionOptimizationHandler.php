<?php

declare(strict_types=1);

namespace App\Application\Optimization\Handlers;

use App\Application\Optimization\DTO\ExecutionOptimizationResult;
use App\Application\Optimization\Queries\GetExecutionOptimizationQuery;
use App\Domain\Optimization\ExecutionOptimizerInterface;
use App\Domain\Video\Exception\InvalidVideoJobException;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\VideoIntelligence\VideoIntelligenceFactoryInterface;

final class GetExecutionOptimizationHandler
{
    public function __construct(
        private readonly VideoRepositoryInterface $videoRepository,
        private readonly VideoIntelligenceFactoryInterface $videoIntelligenceFactory,
        private readonly ExecutionOptimizerInterface $executionOptimizer,
    ) {
    }

    public function __invoke(GetExecutionOptimizationQuery $query): ExecutionOptimizationResult
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

        $intelligence = $this->videoIntelligenceFactory->fromVideoJob($job);
        $optimization = $this->executionOptimizer->optimize($intelligence);

        return ExecutionOptimizationResult::fromOptimization($videoId->value, $optimization);
    }
}
