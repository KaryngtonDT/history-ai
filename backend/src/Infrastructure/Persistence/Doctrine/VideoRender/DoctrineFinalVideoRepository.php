<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\VideoRender;

use App\Application\VideoRender\VideoRenderJsonMapper;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\Video\VideoId;
use App\Domain\VideoRender\FinalVideoArtifact;
use App\Domain\VideoRender\FinalVideoRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineFinalVideoRepository implements FinalVideoRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly VideoRenderJsonMapper $videoRenderJsonMapper,
    ) {
    }

    public function save(
        VideoId $videoId,
        TranslationLanguage $targetLanguage,
        FinalVideoArtifact $artifact,
        string $storagePath,
    ): void {
        $record = $this->entityManager->find(
            FinalVideoRecord::class,
            [
                'videoId' => $videoId->value,
                'targetLanguage' => $targetLanguage->value,
            ],
        );

        $payload = $this->videoRenderJsonMapper->toJson($artifact, $targetLanguage, $storagePath);

        if (null === $record) {
            $this->entityManager->persist(new FinalVideoRecord(
                $videoId->value,
                $targetLanguage->value,
                $payload,
            ));
        } else {
            $record->syncPayload($payload);
        }

        $this->entityManager->flush();
    }

    public function findByVideoIdAndLanguage(VideoId $videoId, TranslationLanguage $targetLanguage): ?FinalVideoArtifact
    {
        $record = $this->entityManager->find(
            FinalVideoRecord::class,
            [
                'videoId' => $videoId->value,
                'targetLanguage' => $targetLanguage->value,
            ],
        );

        if (null === $record) {
            return null;
        }

        return $this->videoRenderJsonMapper->fromJson($record->payload());
    }

    public function findStoragePathByVideoIdAndLanguage(VideoId $videoId, TranslationLanguage $targetLanguage): ?string
    {
        $record = $this->entityManager->find(
            FinalVideoRecord::class,
            [
                'videoId' => $videoId->value,
                'targetLanguage' => $targetLanguage->value,
            ],
        );

        if (null === $record) {
            return null;
        }

        return $this->videoRenderJsonMapper->storagePathFromJson($record->payload());
    }

    public function findAllDetailedByVideoId(VideoId $videoId): array
    {
        /** @var list<FinalVideoRecord> $records */
        $records = $this->entityManager->getRepository(FinalVideoRecord::class)->findBy(
            ['videoId' => $videoId->value],
            ['targetLanguage' => 'ASC'],
        );

        $entries = [];

        foreach ($records as $record) {
            $language = TranslationLanguage::tryFrom($record->targetLanguage()) ?? TranslationLanguage::Unknown;

            $entries[] = [
                'language' => $language,
                'artifact' => $this->videoRenderJsonMapper->fromJson($record->payload()),
                'storagePath' => $this->videoRenderJsonMapper->storagePathFromJson($record->payload()),
            ];
        }

        return $entries;
    }
}
