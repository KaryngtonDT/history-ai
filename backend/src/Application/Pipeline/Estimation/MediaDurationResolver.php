<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Estimation;

use App\Domain\Video\VideoId;
use App\Domain\Video\VideoRepositoryInterface;
use Symfony\Component\Process\Process;

final class MediaDurationResolver
{
    public function __construct(
        private readonly VideoRepositoryInterface $videoRepository,
        private readonly string $ffprobeBinary = 'ffprobe',
    ) {
    }

    public function resolveForVideo(VideoId $videoId): ?int
    {
        $job = $this->videoRepository->findById($videoId);
        $path = $job?->storagePath();

        if (null === $path || !is_file($path)) {
            return null;
        }

        return $this->resolveFromPath($path);
    }

    public function resolveFromPath(string $path): ?int
    {
        $process = new Process([
            $this->ffprobeBinary,
            '-v',
            'error',
            '-show_entries',
            'format=duration',
            '-of',
            'default=noprint_wrappers=1:nokey=1',
            $path,
        ]);
        $process->setTimeout(30);
        $process->run();

        if (!$process->isSuccessful()) {
            return null;
        }

        $output = trim($process->getOutput());

        if (!is_numeric($output)) {
            return null;
        }

        return max(1, (int) round((float) $output));
    }
}
