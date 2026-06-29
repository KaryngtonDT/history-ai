<?php

declare(strict_types=1);

namespace App\Domain\Video;

interface VideoRepositoryInterface
{
    public function save(VideoJob $job): void;

    public function findById(VideoId $id): ?VideoJob;
}
