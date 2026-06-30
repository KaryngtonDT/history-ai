<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\TTS;

use App\Application\TTS\AudioJsonMapper;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\TTS\AudioArtifact;
use App\Domain\TTS\AudioRepositoryInterface;
use App\Domain\Video\VideoId;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineAudioRepository implements AudioRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AudioJsonMapper $audioJsonMapper,
    ) {
    }

    public function save(VideoId $videoId, AudioArtifact $audio): void
    {
        $record = $this->entityManager->find(
            AudioRecord::class,
            [
                'videoId' => $videoId->value,
                'targetLanguage' => $audio->targetLanguage()->value,
            ],
        );

        $payload = $this->audioJsonMapper->toJson($audio);

        if (null === $record) {
            $this->entityManager->persist(new AudioRecord(
                $videoId->value,
                $audio->targetLanguage()->value,
                $payload,
            ));
        } else {
            $record->syncPayload($payload);
        }

        $this->entityManager->flush();
    }

    public function findByVideoIdAndLanguage(VideoId $videoId, TranslationLanguage $targetLanguage): ?AudioArtifact
    {
        $record = $this->entityManager->find(
            AudioRecord::class,
            [
                'videoId' => $videoId->value,
                'targetLanguage' => $targetLanguage->value,
            ],
        );

        if (null === $record) {
            return null;
        }

        return $this->audioJsonMapper->fromJson($record->payload());
    }

    public function findAllByVideoId(VideoId $videoId): array
    {
        /** @var list<AudioRecord> $records */
        $records = $this->entityManager->getRepository(AudioRecord::class)->findBy(
            ['videoId' => $videoId->value],
            ['targetLanguage' => 'ASC'],
        );

        $audioArtifacts = [];

        foreach ($records as $record) {
            $audioArtifacts[] = $this->audioJsonMapper->fromJson($record->payload());
        }

        return $audioArtifacts;
    }
}
