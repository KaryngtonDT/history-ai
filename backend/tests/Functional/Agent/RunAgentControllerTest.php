<?php

declare(strict_types=1);

namespace App\Tests\Functional\Agent;

use App\Domain\Content\ContentId;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RunAgentControllerTest extends WebTestCase
{
    private const string CONTENT_ID = '550e8400-e29b-41d4-a716-446655440000';
    private const string CONVERSATION_ID = '550e8400-e29b-41d4-a716-446655440001';

    public function testPostReturnsDefaultAgentExecutionTrace(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            sprintf('/api/contents/%s/agent/run', self::CONTENT_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => 'What is Rome?'], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(
            ['semantic_search', 'multi_document_chat'],
            array_column($response['plan'], 'tool'),
        );
        self::assertSame(
            ['semantic_search', 'multi_document_chat'],
            array_column($response['steps'], 'tool'),
        );
        self::assertSame('completed', $response['steps'][0]['status']);
        self::assertSame('Semantic search found no relevant chunks.', $response['steps'][0]['summary']);
        self::assertSame('Multi-document chat requires a conversation.', $response['steps'][1]['summary']);
        self::assertSame('Agent workflow completed.', $response['finalSummary']);
    }

    public function testPostReturnsComparisonPlanWithKnowledgeGraphStep(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            sprintf('/api/contents/%s/agent/run', self::CONTENT_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => 'Compare Rome versus Byzantium'], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertContains('knowledge_graph', array_column($response['plan'], 'tool'));
        self::assertSame('Knowledge graph is empty.', $response['steps'][1]['summary']);
    }

    public function testPostReturnsMemoryPlanWithConversationMemoryStep(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            sprintf('/api/contents/%s/agent/run', self::CONTENT_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => 'What did we discuss earlier?'], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertContains('conversation_memory', array_column($response['plan'], 'tool'));
        self::assertSame('No execution.', $response['steps'][1]['summary']);
    }

    public function testPostAcceptsOptionalConversationId(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            sprintf('/api/contents/%s/agent/run', self::CONTENT_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'question' => 'What is Rome?',
                'conversationId' => self::CONVERSATION_ID,
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(200);
    }

    public function testInvalidContentIdReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/contents/not-a-valid-uuid/agent/run',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => 'What is Rome?'], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testInvalidConversationIdReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            sprintf('/api/contents/%s/agent/run', self::CONTENT_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'question' => 'What is Rome?',
                'conversationId' => 'not-a-valid-uuid',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
    }

    public function testMissingQuestionReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            sprintf('/api/contents/%s/agent/run', ContentId::generate()->value),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
    }

    public function testEmptyQuestionReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            sprintf('/api/contents/%s/agent/run', self::CONTENT_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => '   '], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
    }

    public function testTooLongQuestionReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            sprintf('/api/contents/%s/agent/run', self::CONTENT_ID),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['question' => str_repeat('a', 2001)], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
    }
}
