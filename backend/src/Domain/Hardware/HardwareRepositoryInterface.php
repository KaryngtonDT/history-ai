<?php

declare(strict_types=1);

namespace App\Domain\Hardware;

interface HardwareRepositoryInterface
{
    public function detect(): HardwareDetectionReport;

    public function profile(): HardwareProfile;
}
