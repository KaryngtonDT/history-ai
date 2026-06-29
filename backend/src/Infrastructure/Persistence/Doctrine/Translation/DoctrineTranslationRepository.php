<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Translation;

use App\Application\Translation\TranslationJsonMapper;
use App\Domain\Translation\Translation;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\Translation\TranslationRepositoryInterface;
use App\Domain\Video\VideoId;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineTranslationRepository implements TranslationRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslationJsonMapper $translationJsonMapper,
    ) {
    }

    public function save(VideoId $videoId, Translation $translation): void
    {
        $record = $this->entityManager->find(
            TranslationRecord::class,
            [
                'videoId' => $videoId->value,
                'targetLanguage' => $translation->targetLanguage()->value,
            ],
        );

        $payload = $this->translationJsonMapper->toJson($translation);

        if (null === $record) {
            $this->entityManager->persist(new TranslationRecord(
                $videoId->value,
                $translation->targetLanguage()->value,
                $payload,
            ));
        } else {
            $record->syncPayload($payload);
        }

        $this->entityManager->flush();
    }

    public function findByVideoIdAndLanguage(VideoId $videoId, TranslationLanguage $targetLanguage): ?Translation
    {
        $record = $this->entityManager->find(
            TranslationRecord::class,
            [
                'videoId' => $videoId->value,
                'targetLanguage' => $targetLanguage->value,
            ],
        );

        if (null === $record) {
            return null;
        }

        return $this->translationJsonMapper->fromJson($record->payload());
    }

    public function findAllByVideoId(VideoId $videoId): array
    {
        /** @var list<TranslationRecord> $records */
        $records = $this->entityManager->getRepository(TranslationRecord::class)->findBy(
            ['videoId' => $videoId->value],
            ['targetLanguage' => 'ASC'],
        );

        $translations = [];

        foreach ($records as $record) {
            $translations[] = $this->translationJsonMapper->fromJson($record->payload());
        }

        return $translations;
    }
}
