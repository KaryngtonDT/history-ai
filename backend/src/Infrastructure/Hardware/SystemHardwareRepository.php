<?php

declare(strict_types=1);

namespace App\Infrastructure\Hardware;

use App\Application\Hardware\HardwareDetector;
use App\Application\Hardware\HardwareReportBuilder;
use App\Domain\Hardware\HardwareDetectionReport;
use App\Domain\Hardware\HardwareProfile;
use App\Domain\Hardware\HardwareRepositoryInterface;

final class SystemHardwareRepository implements HardwareRepositoryInterface
{
    public function __construct(
        private readonly HardwareDetector $hardwareDetector,
        private readonly HardwareReportBuilder $reportBuilder,
        private readonly HardwareReportStore $hardwareReportStore,
    ) {
    }

    public function detect(): HardwareDetectionReport
    {
        $report = $this->hardwareDetector->detect();
        $this->hardwareReportStore->save($report);

        return $report;
    }

    public function profile(): HardwareProfile
    {
        return $this->hardwareDetector->detect()->profile;
    }

    /**
     * @return array<string, mixed>
     */
    public function overview(): array
    {
        $report = $this->detect();

        return [
            ...$report->toArray(),
            'recommendedPipeline' => $this->reportBuilder->recommendedPipeline($report->profile),
        ];
    }
}
