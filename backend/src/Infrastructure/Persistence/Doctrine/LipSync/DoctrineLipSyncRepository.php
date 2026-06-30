<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\LipSync;

use App\Application\LipSync\LipSyncJsonMapper;
use App\Domain\LipSync\LipSyncArtifact;
use App\Domain\LipSync\LipSyncRepositoryInterface;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\Video\VideoId;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineLipSyncRepository implements LipSyncRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LipSyncJsonMapper $lipSyncJsonMapper,
    ) {
    }

    public function save(VideoId $videoId, TranslationLanguage $targetLanguage, LipSyncArtifact $artifact): void
    {
        $record = $this->entityManager->find(
            LipSyncRecord::class,
            [
                'videoId' => $videoId->value,
                'targetLanguage' => $targetLanguage->value,
            ],
        );

        $payload = $this->lipSyncJsonMapper->toJson($artifact, $targetLanguage);

        if (null === $record) {
            $this->entityManager->persist(new LipSyncRecord(
                $videoId->value,
                $targetLanguage->value,
                $payload,
            ));
        } else {
            $record->syncPayload($payload);
        }

        $this->entityManager->flush();
    }

    public function findByVideoIdAndLanguage(VideoId $videoId, TranslationLanguage $targetLanguage): ?LipSyncArtifact
    {
        $record = $this->entityManager->find(
            LipSyncRecord::class,
            [
                'videoId' => $videoId->value,
                'targetLanguage' => $targetLanguage->value,
            ],
        );

        if (null === $record) {
            return null;
        }

        return $this->lipSyncJsonMapper->fromJson($record->payload());
    }

    public function findAllByVideoId(VideoId $videoId): array
    {
        return array_map(
            static fn (array $entry): LipSyncArtifact => $entry['artifact'],
            $this->findAllDetailedByVideoId($videoId),
        );
    }

    public function findAllDetailedByVideoId(VideoId $videoId): array
    {
        /** @var list<LipSyncRecord> $records */
        $records = $this->entityManager->getRepository(LipSyncRecord::class)->findBy(
            ['videoId' => $videoId->value],
            ['targetLanguage' => 'ASC'],
        );

        $entries = [];

        foreach ($records as $record) {
            $language = TranslationLanguage::tryFrom($record->targetLanguage()) ?? TranslationLanguage::Unknown;

            $entries[] = [
                'language' => $language,
                'artifact' => $this->lipSyncJsonMapper->fromJson($record->payload()),
            ];
        }

        return $entries;
    }
}
