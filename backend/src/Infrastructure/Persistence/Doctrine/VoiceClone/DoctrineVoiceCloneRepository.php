<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\VoiceClone;

use App\Application\VoiceClone\VoiceCloneJsonMapper;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\Video\VideoId;
use App\Domain\VoiceClone\VoiceCloneArtifact;
use App\Domain\VoiceClone\VoiceCloneRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineVoiceCloneRepository implements VoiceCloneRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly VoiceCloneJsonMapper $voiceCloneJsonMapper,
    ) {
    }

    public function save(VideoId $videoId, VoiceCloneArtifact $artifact): void
    {
        $record = $this->entityManager->find(
            VoiceCloneRecord::class,
            [
                'videoId' => $videoId->value,
                'targetLanguage' => $artifact->targetLanguage()->value,
            ],
        );

        $payload = $this->voiceCloneJsonMapper->toJson($artifact);

        if (null === $record) {
            $this->entityManager->persist(new VoiceCloneRecord(
                $videoId->value,
                $artifact->targetLanguage()->value,
                $payload,
            ));
        } else {
            $record->syncPayload($payload);
        }

        $this->entityManager->flush();
    }

    public function findByVideoIdAndLanguage(VideoId $videoId, TranslationLanguage $targetLanguage): ?VoiceCloneArtifact
    {
        $record = $this->entityManager->find(
            VoiceCloneRecord::class,
            [
                'videoId' => $videoId->value,
                'targetLanguage' => $targetLanguage->value,
            ],
        );

        if (null === $record) {
            return null;
        }

        return $this->voiceCloneJsonMapper->fromJson($record->payload());
    }

    public function findAllByVideoId(VideoId $videoId): array
    {
        /** @var list<VoiceCloneRecord> $records */
        $records = $this->entityManager->getRepository(VoiceCloneRecord::class)->findBy(
            ['videoId' => $videoId->value],
            ['targetLanguage' => 'ASC'],
        );

        $artifacts = [];

        foreach ($records as $record) {
            $artifacts[] = $this->voiceCloneJsonMapper->fromJson($record->payload());
        }

        return $artifacts;
    }
}
