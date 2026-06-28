<?php

declare(strict_types=1);

namespace App\Domain\Chat;

use App\Domain\Semantic\RetrievedChunkCollection;

final readonly class ChatContext
{
    public function __construct(
        private ChatQuestion $question,
        private RetrievedChunkCollection $retrievedChunks,
    ) {
    }

    public function question(): ChatQuestion
    {
        return $this->question;
    }

    public function retrievedChunks(): RetrievedChunkCollection
    {
        return $this->retrievedChunks;
    }

    public function sources(): ChatSourceCollection
    {
        /** @var list<ChatSource> $sources */
        $sources = [];

        foreach ($this->retrievedChunks->retrievedChunks() as $retrievedChunk) {
            $sources[] = ChatSource::fromRetrievedChunk($retrievedChunk);
        }

        return new ChatSourceCollection($sources);
    }
}
