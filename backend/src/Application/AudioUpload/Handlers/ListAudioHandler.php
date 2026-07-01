<?php

declare(strict_types=1);

namespace App\Application\AudioUpload\Handlers;

use App\Application\AudioUpload\DTO\GetAudioResult;
use App\Application\AudioUpload\Queries\ListAudioQuery;
use App\Domain\Source\SourceRepositoryInterface;
use App\Domain\Source\SourceType;

final class ListAudioHandler
{
    public function __construct(
        private readonly SourceRepositoryInterface $sourceRepository,
    ) {
    }

    /**
     * @return list<GetAudioResult>
     */
    public function __invoke(ListAudioQuery $query): array
    {
        $sources = $this->sourceRepository->findByType(SourceType::Audio, $query->limit);

        return array_map(
            static fn ($source): GetAudioResult => GetAudioResult::fromSource($source),
            $sources,
        );
    }
}
