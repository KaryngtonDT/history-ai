# OpenAPI Documentation

Version: 1.0

Status: Active

---

# Purpose

History AI exposes a **machine-readable OpenAPI 3.1** specification for the public REST API. Interactive documentation is served via **Swagger UI** at `/api/docs`.

Internal processing routes (`/api/processing/*`) remain intentionally excluded. **Platform Sprint 23** documents one internal diagnostic route (`GET /internal/platform/metrics`) under the `Platform` tag for operators and contract tests.

---

# How documentation is generated

```text
Presentation controllers (#[OA\Post], #[OA\Get], …)
        │
        ▼
NelmioApiDocBundle + swagger-php
        │
        ├── /api/docs       → Swagger UI (HTML)
        └── /api/docs.json  → OpenAPI 3.1 JSON
```

| Component | Location |
| --------- | -------- |
| Bundle | `nelmio/api-doc-bundle` |
| Global metadata | `backend/src/Presentation/OpenApi/OpenApiSpec.php` |
| Shared schemas | `backend/src/Presentation/OpenApi/Schema/` |
| Route annotations | `backend/src/Presentation/Http/Controller/` |
| Nelmio config | `backend/config/packages/nelmio_api_doc.yaml` |
| Doc routes | `backend/config/routes/nelmio_api_doc.yaml` |

The **`default`** area uses `disable_default_routes: true`, so only controller actions annotated with OpenAPI attributes (`#[OA\Post]`, `#[OA\Get]`, …) appear in the spec. Processing endpoints stay excluded without a deny list. The area includes `^/internal/platform` so the metrics diagnostic endpoint is documented when annotated.

---

# Documented endpoints

| Tag | Method | Path |
| --- | ------ | ---- |
| Contents | POST | `/api/contents` |
| Contents | GET | `/api/contents` |
| Artifacts | GET | `/api/contents/{contentId}/artifacts` |
| Library | POST | `/api/library/items` |
| Library | GET | `/api/library/items` |
| Collections | POST | `/api/collections` |
| Collections | GET | `/api/collections` |
| Collections | POST | `/api/collections/{collectionId}/items` |
| Search | GET | `/api/search/library` |
| Timeline | GET | `/api/timeline/{artifactId}` |
| Map | GET | `/api/maps/timeline/{artifactId}` |
| Relations | GET | `/api/contents/{contentId}/relations` |
| Graph | GET | `/api/contents/{contentId}/graph` |
| Graph | GET | `/api/contents/{contentId}/graph/artifacts/{artifactId}/neighborhood` |
| Graph | GET | `/api/conversations/{conversationId}/graph` |
| Recommendations | GET | `/api/contents/{contentId}/artifacts/{artifactId}/recommendations` |
| Semantic | GET | `/api/contents/{contentId}/semantic-search` |
| Agent | POST | `/api/contents/{contentId}/agent/run` |
| Chat | POST | `/api/contents/{contentId}/chat` |
| Chat | POST | `/api/contents/{contentId}/chat/stream` |
| Chat | POST | `/api/contents/{contentId}/conversations/{conversationId}/chat` |
| Chat | POST | `/api/contents/{contentId}/conversations/{conversationId}/chat/stream` |
| Chat | PUT | `/api/conversations/{conversationId}/documents` |
| Video | POST | `/api/videos` |
| Audio | POST | `/api/audio` |
| Audio | GET | `/api/audio` |
| Audio | GET | `/api/audio/{audioId}` |
| YouTube | POST | `/api/youtube` |
| YouTube | POST | `/api/youtube/preview` |
| YouTube | GET | `/api/youtube` |
| YouTube | GET | `/api/youtube/{youtubeId}` |
| Video | GET | `/api/videos/{videoId}/transcript` |
| Video | GET | `/api/videos/{videoId}/translations` |
| Video | GET | `/api/videos/{videoId}/translations/{language}` |
| Video | POST | `/api/videos/{videoId}/translations` |
| AI | GET | `/api/ai/providers` |
| Pipeline | GET | `/api/pipeline` |
| Pipeline | PUT | `/api/pipeline` |
| Pipeline | POST | `/api/pipeline/reset` |
| Orchestrator | GET | `/api/orchestrator/recommendation` |
| Orchestrator | POST | `/api/orchestrator/recommendation` |
| Video Intelligence | GET | `/api/videos/{videoId}/intelligence` |
| Platform | GET | `/internal/platform/metrics` |

---

# Updating annotations

1. Open the controller in `backend/src/Presentation/Http/Controller/`.
2. Add or edit `OpenApi\Attributes` on the action method (before the `#[Route]` attribute).
3. Reuse `#/components/schemas/ErrorResponse` for standard 400 responses.
4. For shared response shapes used in multiple places, add a class under `Presentation/OpenApi/Schema/` with `#[OA\Schema(schema: '…')]`.
5. Run tests (see below) and open `/api/docs` to verify.

Example:

```php
#[OA\Post(
    operationId: 'createContent',
    summary: 'Create content',
    tags: ['Contents'],
    // requestBody, responses …
)]
#[Route('/api/contents', methods: ['POST'])]
public function __invoke(/* … */): JsonResponse
```

Do **not** add OpenAPI attributes to Domain or Application layers — documentation belongs in Presentation only.

---

# Local commands

Start the stack:

```bash
docker compose up -d
```

Browse Swagger UI:

```text
http://localhost:8000/api/docs
```

Fetch raw JSON spec:

```bash
curl -s http://localhost:8000/api/docs.json | jq .openapi
```

Run documentation tests:

```bash
docker compose exec backend php bin/phpunit tests/Functional/OpenApi/
```

Full backend suite:

```bash
docker compose exec backend php bin/phpunit
```

---

# Supported artifact types

The public API documents these **artifact types** (worker-generated learning outputs):

| Type | Value | Status |
| ---- | ----- | ------ |
| Transcript | `transcript` | ✅ Documented |
| Translation | `translation` | ✅ Documented |
| Summary | `summary` | ✅ Documented |
| Quiz | `quiz` | ✅ Documented |
| Flashcards | `flashcards` | ✅ Documented |
| Timeline | `timeline` | ✅ Documented (Sprint 13) |
| Podcast | `podcast` | ✅ Documented (not yet generated by worker) |

Shared OpenAPI schemas:

| Schema | Location |
| ------ | -------- |
| `ArtifactType` | `Presentation/OpenApi/Schema/ArtifactTypeSchema.php` |
| `LibraryItemType` | `Presentation/OpenApi/Schema/LibraryItemTypeSchema.php` |
| `Artifact` | `Presentation/OpenApi/Schema/Artifact.php` |
| `LibraryItem` | `Presentation/OpenApi/Schema/LibraryItem.php` |
| `SearchLibraryItem` | `Presentation/OpenApi/Schema/SearchLibraryItem.php` |
| `Timeline` | `Presentation/OpenApi/Schema/Timeline.php` |
| `TimelineSection` | `Presentation/OpenApi/Schema/TimelineSection.php` |
| `TimelineEvent` | `Presentation/OpenApi/Schema/TimelineEvent.php` |
| `Map` | `Presentation/OpenApi/Schema/Map.php` |
| `HistoricalPlace` | `Presentation/OpenApi/Schema/HistoricalPlace.php` |
| `Coordinates` | `Presentation/OpenApi/Schema/Coordinates.php` |
| `ArtifactRelation` | `Presentation/OpenApi/Schema/ArtifactRelation.php` |
| `ArtifactRelations` | `Presentation/OpenApi/Schema/ArtifactRelations.php` |
| `ArtifactRelationType` | `Presentation/OpenApi/Schema/ArtifactRelationTypeSchema.php` |
| `GraphNode` | `Presentation/OpenApi/Schema/GraphNode.php` |
| `GraphNeighborhoodNode` | `Presentation/OpenApi/Schema/GraphNeighborhoodNode.php` |
| `GraphEdge` | `Presentation/OpenApi/Schema/GraphEdge.php` |
| `GraphNeighborhood` | `Presentation/OpenApi/Schema/GraphNeighborhood.php` |
| `KnowledgeGraph` | `Presentation/OpenApi/Schema/KnowledgeGraph.php` |
| `RecommendedArtifact` | `Presentation/OpenApi/Schema/RecommendedArtifact.php` |
| `ArtifactRecommendations` | `Presentation/OpenApi/Schema/ArtifactRecommendations.php` |
| `RecommendationReason` | `Presentation/OpenApi/Schema/RecommendationReasonSchema.php` |
| `RetrievedChunk` | `Presentation/OpenApi/Schema/RetrievedChunk.php` |
| `SemanticSearchResult` | `Presentation/OpenApi/Schema/SemanticSearchResult.php` |
| `ChatRequest` | `Presentation/OpenApi/Schema/ChatRequest.php` |
| `ChatAnswer` | `Presentation/OpenApi/Schema/ChatAnswer.php` |
| `ChatSource` | `Presentation/OpenApi/Schema/ChatSource.php` |
| `ChatCitation` | `Presentation/OpenApi/Schema/ChatCitation.php` |
| `ChatStreamToken` | `Presentation/OpenApi/Schema/ChatStreamToken.php` |
| `ConversationStreamEvent` | `Presentation/OpenApi/Schema/ConversationStreamEvent.php` |
| `Conversation` | `Presentation/OpenApi/Schema/Conversation.php` |
| `ConversationMessage` | `Presentation/OpenApi/Schema/ConversationMessage.php` |
| `ConversationChatResponse` | `Presentation/OpenApi/Schema/ConversationChatResponse.php` |
| `PerformanceMetric` | `Presentation/OpenApi/Schema/PerformanceMetric.php` |
| `PerformanceMetricSnapshot` | `Presentation/OpenApi/Schema/PerformanceMetricSnapshot.php` |
| `PlatformMetricsResponse` | `Presentation/OpenApi/Schema/PlatformMetricsResponse.php` |

`GET /api/timeline/{artifactId}` returns a `Timeline` with nested `sections[].events[].text`.

`GET /api/maps/timeline/{artifactId}` returns a `Map` with nested `places[].name`, `places[].coordinates`, and optional `places[].description`.

`GET /api/contents/{contentId}/relations` returns an `ArtifactRelations` envelope with `relations[]` entries (`sourceArtifactId`, `targetArtifactId`, `type`). The `type` field uses the `ArtifactRelationType` enum: `related`, `derived_from`, `references`, `next`, `previous`.

`GET /api/contents/{contentId}/graph` returns a `KnowledgeGraph` with `nodes[]` (`artifactId`, `type`, `title`) and `edges[]` (`sourceArtifactId`, `targetArtifactId`, `type`). Node `type` uses `ArtifactType`; edge `type` reuses `ArtifactRelationType`.

`GET /api/contents/{contentId}/graph/artifacts/{artifactId}/neighborhood` returns a `GraphNeighborhood` with `center` and `neighbors[]` as `GraphNeighborhoodNode` entries (`artifactId`, `type`, `label`) and `edges[]` as `GraphEdge` entries (`sourceArtifactId`, `targetArtifactId`, `type`, `weight`). Only direct neighbors (one hop) are returned. Invalid UUID returns HTTP 400 with `ErrorResponse`. Unknown artifact in the content graph returns HTTP 404 with `ErrorResponse`.

`GET /api/conversations/{conversationId}/graph` returns a conversation-scoped `KnowledgeGraph` built from artifacts belonging to the documents selected in the conversation (`documents[]` order preserved). Invalid UUID returns HTTP 400 with `ErrorResponse`. Unknown conversation returns HTTP 404 with `ErrorResponse`.

**Platform Sprint 27 note:** Knowledge Graph Explorer 2.0 adds neighborhood and conversation-scoped graph endpoints. Content-level `GET …/graph` behavior is unchanged. Neighborhood nodes use `label` (not `title`) in JSON; content graph nodes continue to use `title`.

`GET /api/contents/{contentId}/artifacts/{artifactId}/recommendations` returns an `ArtifactRecommendations` envelope with `recommendations[]` entries (`artifactId`, `type`, `title`, `reason`, `score`). The `type` field uses `ArtifactType`; `reason` uses the `RecommendationReason` enum: `related`, `derived_from`, `references`, `next`, `previous`. The `score` field is an integer from 0 to 100 (relevance weight derived from `reason`; higher scores appear first in the API response).

`GET /api/contents/{contentId}/semantic-search?q=…` returns a `SemanticSearchResult` envelope with `results[]` entries (`artifactId`, `chunkId`, `position`, `text`, `score`). The `score` field is a float from 0.0 to 1.0 (cosine similarity; higher scores appear first in the API response). The required query parameter `q` accepts 1–500 characters.

`POST /api/contents/{contentId}/chat` accepts a `ChatRequest` body (`question`, 1–2000 characters) and returns a `ChatAnswer` with `answer`, `sources[]` entries (`artifactId`, `chunkId`, `text`, `score`), and `citations[]` entries (`number`, `artifactId`, `chunkId`, `score`). Citations reference numbered markers in the answer (e.g. `[1]`) without duplicating chunk text — use `sources[]` for excerpt text. Chat sources omit the `position` field present on semantic-search chunks. Invalid UUID, malformed JSON, or invalid question returns HTTP 400 with `ErrorResponse`.

**Sprint 22 note:** Embedding provider selection (`EMBEDDING_PROVIDER`, Gemini adapter) is internal to the backend. The semantic-search HTTP contract, OpenAPI schemas, and response shape are unchanged from Sprint 20.

**UX-01 note:** Chat provider selection (`CHAT_PROVIDER`, Gemini adapter) is internal to the backend. The chat HTTP contract and OpenAPI schemas document the stable request/response shape only.

**UX-02 note:** Interactive citations (`citations[]`) are documented in OpenAPI slice 5. Frontend navigation (click `[1]` → scroll + highlight) is a UI concern; the HTTP contract exposes citation metadata only.

`POST /api/contents/{contentId}/chat/stream` accepts a `ChatRequest` body (`question`, 1–2000 characters) and returns `text/event-stream`. The stream emits SSE `token` events with JSON payloads matching `ChatStreamToken` (`index`, `text`), followed by a `done` event with `{}`. Invalid UUID, malformed JSON, or invalid question returns HTTP 400 with `ErrorResponse`. Streaming uses the mock provider by default; Gemini true streaming is not yet exposed.

**UX-03 note:** The streaming chat endpoint is documented in OpenAPI slice 6. Token payloads use `ChatStreamToken`; sources and citations are not included in the SSE stream (non-streaming `POST /chat` remains available for full answers with metadata).

`POST /api/contents/{contentId}/conversations/{conversationId}/chat` accepts a `ChatRequest` body (`question`, 1–2000 characters) and returns a `ConversationChatResponse` with `conversation` (`id`, `contentId`, `messages[]` of `role` + `text`, `documents[]` of `contentId`) and `answer` (`ChatAnswer` with `answer`, `sources[]`, `citations[]`). The client supplies `conversationId` (UUID); the backend creates the conversation on first use and appends user/assistant messages on each call. RAG retrieval uses all documents listed in `conversation.documents[]`. Invalid UUID, malformed JSON, invalid question, or conversation/content mismatch returns HTTP 400 with `ErrorResponse`.

**Sprint 24 note:** Persistent conversations also support streaming via `POST …/conversations/{conversationId}/chat/stream` (Platform Sprint 26). The synchronous JSON endpoint remains available for full answers with citation metadata.

`POST /api/contents/{contentId}/conversations/{conversationId}/chat/stream` accepts a `ChatRequest` body (`question`, 1–2000 characters) and returns `text/event-stream`. The stream emits SSE `token` events with JSON payloads matching `ChatStreamToken` (`index`, `text`), a `conversation` event with JSON matching `ConversationStreamEvent` (`conversation` with `id`, `contentId`, `messages[]`, `documents[]`), and a final `done` event with `{}`. The backend creates the conversation on first use, runs multi-document RAG across `conversation.documents[]`, persists the assistant reply, and returns the updated conversation as the authoritative payload. Invalid UUID, malformed JSON, invalid question, or conversation/content mismatch returns HTTP 400 with `ErrorResponse`. Streaming uses the mock provider by default; Gemini true streaming is not yet exposed.

`PUT /api/conversations/{conversationId}/documents` accepts an `UpdateConversationDocumentsRequest` body (`contentIds[]`, at least one UUID) and returns a `ConversationResponse` envelope with the updated `conversation` (`id`, `contentId`, `messages[]`, `documents[]`). The selection is replaced entirely; messages are preserved; duplicate ids are deduplicated server-side while preserving order. Invalid UUID, malformed JSON, empty `contentIds`, or invalid content id returns HTTP 400 with `ErrorResponse`. Unknown conversation returns HTTP 404 with `ErrorResponse`. This endpoint does not generate a chat answer.

**Platform Sprint 25 note:** Multi-document selection is persisted through `PUT …/documents` before subsequent chat or stream calls use the updated document set.

**Platform Sprint 26 note:** Conversation streaming reuses `ChatStreamToken` for token payloads. Sources and citations are not included in the SSE stream; the frontend treats the `conversation` event as source of truth for `messages[]`. Use synchronous `POST …/chat` when full `ChatAnswer` metadata is required.

`GET /internal/platform/metrics` returns a `PlatformMetricsResponse` envelope with `snapshots[]` entries (`correlationId`, `recordedAt`, `metrics[]`). Each metric has `name` and `durationMs` (integer milliseconds). The optional query parameter `limit` accepts 1–100 (default 20). Invalid `limit` returns HTTP 400 with `ErrorResponse`. This endpoint is internal diagnostics only — not consumed by the frontend.

**Platform Sprint 23 note:** Correlation IDs, performance timers, metrics store, and embedding cache are internal platform concerns. Only the metrics read API is documented in OpenAPI slice 5.

**Platform Sprint 24 note:** Conversation memory OpenAPI schemas (`Conversation`, `ConversationMessage`, `ConversationChatResponse`) document the stable HTTP contract only; persistence and RAG orchestration remain internal.

**Platform Sprint 25 note:** Multi-document selection adds `SelectedDocument`, `UpdateConversationDocumentsRequest`, `ConversationResponse`, and `PUT /api/conversations/{conversationId}/documents`. The `Conversation` schema now includes `documents[]`.

**Platform Sprint 26 note:** Conversation streaming adds `ConversationStreamEvent` and `POST /api/contents/{contentId}/conversations/{conversationId}/chat/stream` (`text/event-stream` with `token`, `conversation`, and `done` events).

**Platform Sprint 27 note:** Knowledge Graph Explorer 2.0 adds `GraphNeighborhood`, `GraphNeighborhoodNode`, and documents `GraphEdge.weight` for neighborhood edges. Content-level `KnowledgeGraph` responses omit `weight` on edges. New paths: `GET …/graph/artifacts/{artifactId}/neighborhood` and `GET /api/conversations/{conversationId}/graph`.

**Platform Sprint 28 note:** Agent Workflows adds `POST /api/contents/{contentId}/agent/run` with `AgentRunRequest`, `AgentExecution`, `AgentPlanStep`, `AgentExecutionStep`, `AgentTool`, and `AgentExecutionStatus`. The endpoint returns a deterministic plan and execution trace only — no real tool calls, persistence, or streaming.

**Platform Sprint 29 note:** Real Tool Execution documents `AgentExecutionStep.metadata` (`object<string, mixed>`) on the agent run response contract. Metadata is tool-specific — see [Agent execution metadata](#agent-execution-metadata) below. Three tools execute real Application handlers; `conversation_memory` remains stubbed.

**Platform Sprint 30 note:** Conversation Memory & Metadata Aggregation documents top-level `AgentExecution.metadata` (merged from all executed steps; later tools overwrite duplicate keys). All four agent tools execute real handlers. The frontend `AgentMetadataPanel` surfaces per-step metadata in the agent trace UI.

**Platform Sprint 31 note:** Video Processing Foundation adds `POST /api/videos` with multipart upload (`video` field), `UploadVideoResponse`, and `VideoStatus`. Supported formats: mp4, mov, mkv. Successful uploads return HTTP 201 with `{ videoId, status: "queued" }`. Maximum file size is controlled by `VIDEO_UPLOAD_MAX_BYTES` (default 500 MB). Processing and transcript retrieval are documented in Sprint 32.

**Platform Sprint 32 note:** Speech-to-Text Foundation adds `GET /api/videos/{videoId}/transcript` with `Transcript`, `TranscriptSegment`, and `TranscriptLanguage` schemas. The endpoint returns segmented timestamps after the video job completes and a transcript artifact is generated. No TTS or lip-sync endpoints are documented yet.

**Platform Sprint 33 note:** Multilingual Translation Foundation adds `GET /api/videos/{videoId}/translations`, `GET /api/videos/{videoId}/translations/{language}`, and `POST /api/videos/{videoId}/translations` with `Translation`, `TranslationSegment`, `TranslationLanguage`, and `TranslationProvider` schemas. The worker auto-translates configured languages (`TRANSLATION_LANGUAGES`, default `fr,de`) after transcription. No TTS, lip-sync, or video rendering endpoints are documented yet.

**Platform Sprint 34 note:** AI Engine Platform adds `GET /api/ai/providers` with `AIEngine`, `AIProvider`, and `AIEngineCapability` schemas. Application handlers resolve providers by capability through `AIProviderResolverInterface`. Future providers (F5-TTS, Kokoro, OpenVoice, LatentSync) are registered but disabled.

Library save (`POST /api/library/items`) accepts any `LibraryItemType`, including `timeline`.

---

# Agent execution metadata

`POST /api/contents/{contentId}/agent/run` returns `AgentExecution` with `plan[]`, `steps[]`, `finalSummary`, and aggregated `metadata`.

Each `AgentExecution.steps[]` entry includes `order`, `tool`, `status`, `summary`, and per-step `metadata`.

| Tool | Metadata keys | Example |
| ---- | ------------- | ------- |
| `semantic_search` | `resultCount`, `topScore` (when results exist) | `{ "resultCount": 3, "topScore": 0.91 }` |
| `knowledge_graph` | `nodeCount`, `edgeCount` | `{ "nodeCount": 12, "edgeCount": 18 }` |
| `conversation_memory` | `messageCount`, `userMessages`, `assistantMessages` | `{ "messageCount": 9, "userMessages": 5, "assistantMessages": 4 }` |
| `multi_document_chat` | `messageCount`, `sourceCount`, `citationCount` | `{ "messageCount": 4, "sourceCount": 3, "citationCount": 3 }` |
| `multi_document_chat` (no conversation) | `requiresConversation` | `{ "requiresConversation": true }` |

**Aggregated `metadata`:** `RunAgentHandler` merges all step metadata into the top-level `metadata` object using a later-wins policy for duplicate keys. Example after a comparison workflow with conversation:

```json
{
  "resultCount": 3,
  "topScore": 0.91,
  "nodeCount": 12,
  "edgeCount": 18,
  "messageCount": 9,
  "sourceCount": 3,
  "citationCount": 3
}
```

Zero-result semantic search returns `{ "resultCount": 0 }`. Empty knowledge graph returns `{ "nodeCount": 0, "edgeCount": 0 }`. Missing or empty conversation memory returns `{}` on that step. Multi-document chat requires `conversationId` in `AgentRunRequest` to invoke `AskConversationChatHandler`.

---

# Video upload

`POST /api/videos` accepts `multipart/form-data` with a single required field `video` (binary).

| Response | Body |
| -------- | ---- |
| 201 Created | `{ "videoId": "<uuid>", "status": "queued" }` |
| 400 Bad Request | `{ "error": "Invalid request" }` |

| Validation | Rule |
| ---------- | ---- |
| Formats | `.mp4`, `.mov`, `.mkv` |
| Max size | `VIDEO_UPLOAD_MAX_BYTES` env (default 524288000) |
| Field name | `video` |

After upload the backend stores the file locally, persists a `VideoJob`, transitions status to `queued`, and dispatches `ProcessVideoMessage`. The worker runs Faster-Whisper transcription, persists the transcript, and creates a transcript artifact. The frontend `VideoUploadPanel` at `/video/upload` performs client-side format validation and reports upload progress.

---

# Video transcript

`GET /api/videos/{videoId}/transcript` returns the speech-to-text transcript for a completed video job.

| Response | Body |
| -------- | ---- |
| 200 OK | `Transcript` — `{ videoId, transcriptId, language, text, duration, segmentCount, segments[] }` |
| 400 Bad Request | `{ "error": "Invalid request" }` (invalid UUID or transcript not found) |

| Schema | Fields |
| ------ | ------ |
| `TranscriptLanguage` | `english`, `french`, `german`, `unknown` |
| `TranscriptSegment` | `index`, `startTime`, `endTime`, `text` |
| `Transcript` | Full transcript with `segments[]` |

The frontend `TranscriptPanel` at `/video/:videoId/transcript` loads the transcript via `TranscriptService` and renders a read-only timeline with timestamps and segment highlighting.

# Video translations

`GET /api/videos/{videoId}/translations` returns summaries of all available translations for a video job.

| Status | Body |
| ------ | ---- |
| 200 OK | `VideoTranslationsList` — `{ videoId, translations[] }` |
| 400 Bad Request | `{ "error": "Invalid request" }` |

`GET /api/videos/{videoId}/translations/{language}` returns the full translated transcript for one target language.

| Status | Body |
| ------ | ---- |
| 200 OK | `Translation` — `{ videoId, translationId, sourceLanguage, targetLanguage, provider, text, segmentCount, segments[] }` |
| 400 Bad Request | `{ "error": "Invalid request" }` (invalid UUID, language, or translation not found) |

`POST /api/videos/{videoId}/translations` accepts `GenerateVideoTranslationsRequest` (`targetLanguages[]`, optional `provider`) and returns HTTP 202 with `{ "status": "generated" }`.

| Schema | Values / fields |
| ------ | ---------------- |
| `TranslationLanguage` | `english`, `french`, `german`, `spanish`, `italian`, `unknown` |
| `TranslationProvider` | `qwen`, `deepseek`, `gemini`, `gpt`, `mock` |
| `TranslationSegment` | `index`, `sourceText`, `translatedText` |
| `Translation` | Full translation with side-by-side segments |

The frontend `TranslationPanel` at `/video/:videoId/translations` lets users select target languages and a translation engine, generate translations, and view source vs translated text side by side via `TranslationService`.

---

# AI engine providers (Platform Sprint 34)

| Method | Path | Description |
| ------ | ---- | ----------- |
| GET | `/api/ai/providers` | List AI engines grouped by capability with provider metadata |

## Response shape

```json
{
  "engines": [
    {
      "id": "speech_to_text",
      "capability": "speech_to_text",
      "displayName": "Speech Recognition",
      "providers": [
        {
          "providerId": "faster_whisper",
          "displayName": "Faster Whisper",
          "capability": "speech_to_text",
          "enabled": true
        }
      ]
    }
  ]
}
```

## Schemas

| Schema | Values / fields |
| ------ | ---------------- |
| `AIEngineCapability` | `speech_to_text`, `translation`, `text_to_speech`, `voice_clone`, `lip_sync`, `video_render` |
| `AIProvider` | `providerId`, `displayName`, `capability`, `enabled` |
| `AIEngine` | `id`, `capability`, `displayName`, `providers[]` |

The frontend `AIEngineSettings` at `/settings/ai` displays available engines and providers (read-only) via `AIEngineService`.

---

# Video audio (Platform Sprint 35)

| Method | Path | Description |
| ------ | ---- | ----------- |
| GET | `/api/videos/{videoId}/audio` | List generated audio artifacts for a video |
| POST | `/api/videos/{videoId}/audio` | Generate audio for a translation language with selected provider and voice |
| GET | `/api/videos/{videoId}/audio/{language}` | Get audio metadata for a specific language |
| GET | `/api/videos/{videoId}/audio/{language}/stream` | Stream WAV audio file for preview/download |

## Request body (POST)

```json
{
  "language": "French",
  "provider": "f5_tts",
  "voiceId": "f5-female-01"
}
```

## Response shape (list)

```json
{
  "items": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "videoId": "660e8400-e29b-41d4-a716-446655440001",
      "translationId": "770e8400-e29b-41d4-a716-446655440002",
      "language": "French",
      "provider": "f5_tts",
      "voice": {
        "voiceId": "f5-female-01",
        "displayName": "Female 01",
        "language": "French",
        "gender": "female"
      },
      "durationSeconds": 201.5,
      "format": "wav",
      "downloadUrl": "/api/videos/{videoId}/audio/French/stream"
    }
  ]
}
```

## Schemas

| Schema | Values / fields |
| ------ | ---------------- |
| `TextToSpeechProvider` | `f5_tts`, `kokoro`, `xtts`, `mock` |
| `VoiceGender` | `male`, `female`, `neutral` |
| `VoiceLanguage` | `English`, `French`, `German`, `Spanish`, `Italian` |
| `AudioArtifact` | `id`, `translationId`, `provider`, `voice`, `durationSeconds`, `format`, `language` |
| `GenerateVideoAudioRequest` | `language`, `provider`, `voiceId` |

The frontend `AudioPlayerPanel` at `/video/:videoId/audio` lets users select a TTS provider and voice, generate audio, play/pause preview, and download WAV via `AudioService`.

---

# Video voice clone (Platform Sprint 36)

| Method | Path | Description |
| ------ | ---- | ----------- |
| GET | `/api/videos/{videoId}/voice-clone` | List cloned voice artifacts for a video |
| POST | `/api/videos/{videoId}/voice-clone` | Generate cloned audio from generic F5 audio using OpenVoice V2 |
| GET | `/api/videos/{videoId}/voice-clone/{language}` | Get voice clone metadata with original and cloned stream URLs |
| GET | `/api/videos/{videoId}/voice-clone/{language}/stream` | Stream cloned WAV audio file |

## Request body (POST)

```json
{
  "targetLanguages": ["french"],
  "provider": "openvoice",
  "voiceMode": "clone"
}
```

## Response shape (list)

```json
{
  "videoId": "660e8400-e29b-41d4-a716-446655440001",
  "voiceClones": [
    {
      "artifactId": "550e8400-e29b-41d4-a716-446655440050",
      "sourceAudioId": "550e8400-e29b-41d4-a716-446655440030",
      "clonedAudioId": "550e8400-e29b-41d4-a716-446655440060",
      "targetLanguage": "french",
      "provider": "openvoice",
      "duration": 201.5,
      "sampleRate": 44100
    }
  ]
}
```

## Schemas

| Schema | Values / fields |
| ------ | ---------------- |
| `VoiceCloneProvider` | `openvoice`, `seedvc`, `mock` |
| `VoiceProfile` | `profileId`, `sourceLanguage`, `duration`, `sampleRate` |
| `VoiceCloneArtifact` | `artifactId`, `sourceAudioId`, `clonedAudioId`, `provider`, stream URLs |
| `GenerateVideoVoiceCloneRequest` | `targetLanguages`, `provider`, `voiceMode` |

The frontend `VoiceClonePanel` at `/video/:videoId/voice-clone` lets users toggle generic vs cloned voice, generate cloned audio, and compare original vs cloned playback via `VoiceCloneService`.

---

# Video lip sync (Platform Sprint 37)

| Method | Path | Description |
| ------ | ---- | ----------- |
| GET | `/api/videos/{videoId}/lip-sync` | List lip-synced video artifacts for a video |
| POST | `/api/videos/{videoId}/lip-sync` | Generate lip-synced video from original video + cloned audio using LatentSync |
| GET | `/api/videos/{videoId}/lip-sync/{language}` | Get lip sync metadata with original and synced stream URLs |
| GET | `/api/videos/{videoId}/lip-sync/{language}/stream` | Stream lip-synced MP4 preview file |

## Request body (POST)

```json
{
  "targetLanguages": ["french"],
  "provider": "latentsync"
}
```

## Response shape (list)

```json
{
  "videoId": "660e8400-e29b-41d4-a716-446655440001",
  "lipSyncs": [
    {
      "artifactId": "550e8400-e29b-41d4-a716-446655440080",
      "clonedAudioId": "550e8400-e29b-41d4-a716-446655440060",
      "targetLanguage": "french",
      "provider": "latentsync",
      "synchronizedVideoId": "550e8400-e29b-41d4-a716-446655440070",
      "duration": 120.5,
      "syncedVideoUrl": "/api/videos/{videoId}/lip-sync/french/stream"
    }
  ]
}
```

## Schemas

| Schema | Values / fields |
| ------ | ---------------- |
| `LipSyncProvider` | `latentsync`, `wav2lip`, `mock` |
| `LipSyncArtifact` | `artifactId`, `clonedAudioId`, `provider`, `synchronizedVideoId`, stream URLs |
| `GenerateVideoLipSyncRequest` | `targetLanguages`, `provider` |

The frontend `LipSyncPanel` at `/video/:videoId/lip-sync` lets users select LatentSync, generate lip-synced previews, and compare original vs synced video via `LipSyncService`.

---

# Video final render (Platform Sprint 38)

| Method | Path | Description |
| ------ | ---- | ----------- |
| GET | `/api/videos/{videoId}/render` | List final rendered MP4 artifacts for a video |
| POST | `/api/videos/{videoId}/render` | Render lip-synced preview into downloadable MP4 using FFmpeg |
| GET | `/api/videos/{videoId}/render/{language}` | Get final render metadata with stream and download URLs |
| GET | `/api/videos/{videoId}/render/{language}/stream` | Stream or download final MP4 file |

## Request body (POST)

```json
{
  "targetLanguages": ["french"],
  "provider": "ffmpeg",
  "format": "mp4",
  "quality": "standard"
}
```

## Response shape (list)

```json
{
  "videoId": "660e8400-e29b-41d4-a716-446655440001",
  "renders": [
    {
      "finalVideoId": "550e8400-e29b-41d4-a716-446655440091",
      "targetLanguage": "french",
      "provider": "ffmpeg",
      "format": "mp4",
      "quality": "standard",
      "duration": 120.5,
      "fileSizeBytes": 1048576,
      "streamUrl": "/api/videos/{videoId}/render/french/stream"
    }
  ]
}
```

## Schemas

| Schema | Values / fields |
| ------ | ---------------- |
| `VideoRenderProvider` | `ffmpeg`, `mock` |
| `VideoRenderFormat` | `mp4`, `webm` |
| `VideoRenderQuality` | `preview`, `standard`, `high` |
| `FinalVideoArtifact` | `finalVideoId`, `provider`, `format`, `quality`, stream/download URLs |
| `GenerateVideoRenderRequest` | `targetLanguages`, `provider`, `format`, `quality` |

The frontend `FinalVideoPanel` at `/video/:videoId/render` lets users render final MP4s, preview the result, and download via `VideoRenderService`.

---

# Pipeline configuration (Platform Sprint 39)

| Method | Path | Description |
| ------ | ---- | ----------- |
| GET | `/api/pipeline` | Get latest saved pipeline configuration or platform defaults |
| PUT | `/api/pipeline` | Save selected AI provider for each pipeline stage |
| POST | `/api/pipeline/reset` | Delete saved configuration and restore defaults |

## Request body (PUT)

```json
{
  "stages": [
    { "stage": "speech_to_text", "providerId": "faster_whisper" },
    { "stage": "translation", "providerId": "ollama" },
    { "stage": "text_to_speech", "providerId": "f5" },
    { "stage": "voice_clone", "providerId": "openvoice" },
    { "stage": "lip_sync", "providerId": "latentsync" },
    { "stage": "video_render", "providerId": "ffmpeg" }
  ]
}
```

## Response shape

```json
{
  "id": "550e8400-e29b-41d4-a716-446655440001",
  "version": 1,
  "createdAt": "2026-06-26T12:00:00+00:00",
  "updatedAt": "2026-06-26T12:00:00+00:00",
  "stages": [
    { "stage": "speech_to_text", "providerId": "faster_whisper" }
  ]
}
```

## Schemas

| Schema | Values / fields |
| ------ | ---------------- |
| `PipelineStageType` | `speech_to_text`, `translation`, `text_to_speech`, `voice_clone`, `lip_sync`, `video_render` |
| `PipelineStage` | `stage`, `providerId` |
| `PipelineConfiguration` | `id`, `version`, `createdAt`, `updatedAt`, `stages[]` |
| `SavePipelineConfigurationRequest` | `stages[]` |

The frontend `PipelineBuilder` at `/settings/pipeline` loads enabled providers from the AI engine registry, lets users pick one provider per stage, save configuration, and reset to defaults via `PipelineService`.

At runtime, `AIProviderResolver` reads the latest saved configuration and falls back to platform defaults when none is stored.

---

# AI Orchestrator (Platform Sprint 40)

| Method | Path | Description |
| ------ | ---- | ----------- |
| GET | `/api/orchestrator/recommendation` | Get pipeline recommendation from optional query parameters |
| POST | `/api/orchestrator/recommendation` | Request pipeline recommendation from video analysis payload |

## Response shape

```json
{
  "id": "550e8400-e29b-41d4-a716-446655440001",
  "strategy": "balanced",
  "explanation": "Balanced pipeline for English content targeting French and German translations.",
  "estimatedDurationSeconds": 240,
  "estimatedQuality": 4,
  "estimatedVramGb": 8.0,
  "stages": [
    { "stage": "speech_to_text", "providerId": "faster_whisper" }
  ],
  "reasons": [
    "Two speakers detected.",
    "High STT confidence.",
    "Balanced strategy selected."
  ]
}
```

## Schemas

| Schema | Values / fields |
| ------ | ---------------- |
| `ProcessingMode` | `manual`, `automatic` |
| `ProcessingStrategy` | `balanced`, `quality`, `speed`, `low_memory` |
| `VideoAnalysis` | `detectedLanguage`, `durationSeconds`, `resolution`, `fps`, `segmentCount`, `transcriptText`, `gpuAvailable`, `estimatedVramGb`, `hasSlidesHint`, `strategy` |
| `PipelineRecommendation` | `id`, `strategy`, `explanation`, estimates, `stages[]`, `reasons[]` |

Video upload (`POST /api/videos`) accepts optional `processingMode` and `strategy` form fields. Automatic mode generates an ephemeral `PipelineConfiguration` at runtime without overwriting saved pipeline settings.

The frontend `ProcessingModeSelector` and `VideoIntelligenceDashboard` on `/video/upload` use `OrchestratorService` and `VideoIntelligenceService`.

---

# AI Director — Video Intelligence (Platform Sprint 41)

| Method | Path | Description |
| ------ | ---- | ----------- |
| GET | `/api/videos/{videoId}/intelligence` | Get AI Director video intelligence report for a video |

## Schemas

| Schema | Values / fields |
| ------ | ---------------- |
| `VideoIntelligence` | `id`, `videoId`, `durationSeconds`, `scene`, `audio`, `visual`, `speech`, `speakers[]`, `gpuAvailable`, `estimatedVramGb` |
| `AudioCharacteristics` | `language`, `speakerCount`, `backgroundNoise`, `backgroundMusic`, `speechSpeed`, `confidence` |
| `VisualCharacteristics` | `resolution`, `fps`, `lighting`, `lipVisibility`, `faceCount` |
| `SpeechCharacteristics` | `dominantEmotion`, `averageSpeakingRate`, `pauseCount`, `hasOverlaps` |
| `VideoSpeaker` | `index`, `label` |

---

# Execution Optimization (Platform Sprint 42)

| Method | Path | Description |
| ------ | ---- | ----------- |
| GET | `/api/videos/{videoId}/optimization` | Get AI Director execution parameter optimization for a video |

## Schemas

| Schema | Values / fields |
| ------ | ---------------- |
| `ExecutionOptimization` | `id`, `videoId`, `profile`, `summary`, `estimatedImpact`, `stages[]`, `explanations[]` |
| `OptimizationStage` | `stage`, `parameters[]`, `explanations[]` |
| `OptimizationParameter` | `key`, `value` |
| `OptimizationProfile` | `balanced`, `quality`, `speed`, `low_memory` |

The frontend `OptimizationDashboard` on `/video/upload` uses `OptimizationService` alongside `VideoIntelligenceService` and `OrchestratorService`.

---

# Resource Scheduling (Platform Sprint 43)

| Method | Path | Description |
| ------ | ---- | ----------- |
| GET | `/api/videos/{videoId}/schedule` | Get resource-aware execution schedule for a video |

## Schemas

| Schema | Values / fields |
| ------ | ---------------- |
| `ExecutionSchedule` | `id`, `videoId`, `strategy`, `estimatedCompletionSeconds`, `currentStage`, `currentResource`, `stages[]`, `resources[]` |
| `ScheduledStage` | `stage`, `order`, `status`, `estimatedDurationSeconds`, `parallelGroup`, `requirements[]` |
| `ExecutionResource` | `type`, `running`, `pending`, `maxConcurrency` |
| `ResourceRequirement` | `type`, `weight` |
| `ResourceType` | `cpu`, `gpu`, `io` |
| `SchedulingStrategy` | `balanced`, `quality`, `speed`, `low_memory` |

The frontend `ProcessingResourceMonitor` on `/video/upload` uses `SchedulerService`.

---

# Quality Assessment (Platform Sprint 44)

| Method | Path | Description |
| ------ | ---- | ----------- |
| GET | `/api/videos/{videoId}/quality` | Get AI quality assessment report for a video |

## Schemas

| Schema | Values / fields |
| ------ | ---------------- |
| `QualityReport` | `id`, `videoId`, `overallScore`, `recommendation`, `metrics[]`, `explanations[]` |
| `QualityMetric` | `category`, `score`, `explanation` |
| `QualityScore` | Integer `0`–`100` |
| `QualityCategory` | `audio`, `translation`, `voice_clone`, `lip_sync`, `rendering` |
| `PublicationRecommendation` | `ready`, `review_recommended`, `regenerate_required` |

The frontend `QualityDashboard` on `/video/upload` uses `QualityService`.

---

# Project Workspace (Platform Sprint 45)

| Method | Path | Description |
| ------ | ---- | ----------- |
| GET | `/api/projects` | List workspace projects |
| POST | `/api/projects` | Create a project |
| GET | `/api/projects/{id}` | Get project details with videos and batch status |
| POST | `/api/projects/{id}/process` | Start batch processing for all project videos |

## Schemas

| Schema | Values / fields |
| ------ | ---------------- |
| `Project` | `id`, `name`, `createdAt`, `videos[]`, `batchJobId`, `batchStatus`, `batchProgress`, `targetLanguages[]` |
| `ProjectVideo` | `videoId`, `filename`, `addedAt` |
| `BatchJob` | `id`, `projectId`, `status`, `progress`, `totalVideos`, `queuedVideos`, `targetLanguages[]`, `failedVideoIds[]` |
| `BatchJobStatus` | `pending`, `running`, `completed`, `partial_failure`, `failed` |

The frontend `WorkspacePage` on `/workspace` uses `WorkspaceService`.

---

# Execution History (Platform Sprint 46)

| Method | Path | Description |
| ------ | ---- | ----------- |
| GET | `/api/videos/{videoId}/history` | Get execution history for a video |
| GET | `/api/videos/{videoId}/history/{version}` | Get a specific execution version |
| GET | `/api/videos/{videoId}/history/compare` | Compare two versions (`left`, `right` query params) |
| POST | `/api/videos/{videoId}/history/{version}/reprocess` | Reprocess from a previous version |

## Schemas

| Schema | Values / fields |
| ------ | ---------------- |
| `ExecutionHistory` | `id`, `videoId`, `versions[]` |
| `ExecutionVersion` | `versionNumber`, `pipelineConfigurationId`, `optimizationId`, `qualityReportId`, `renderedVideoId`, `createdAt`, `optimizationProfile`, `qualityScore` |
| `ExecutionSnapshot` | Full persisted snapshot with pipeline, optimization, and quality payloads |
| `ComparisonResult` | `providerDifferences[]`, `optimizationDifference`, `qualityScoreDifference` |

The frontend `ExecutionHistoryPanel` on `/workspace` uses `HistoryService`.

---

# AI Review & Feedback (Platform Sprint 47)

| Method | Path | Description |
| ------ | ---- | ----------- |
| GET | `/api/videos/{videoId}/reviews` | List reviews for a video |
| POST | `/api/videos/{videoId}/reviews` | Save a new review |
| GET | `/api/preferences` | Get the derived user preference profile |

## Schemas

| Schema | Values / fields |
| ------ | ---------------- |
| `Review` | `id`, `videoId`, `executionVersionNumber`, `scores`, `comment`, `createdAt` |
| `ReviewScore` | Category ratings (`overall`, `translation`, `voice_clone`, `lip_sync`, `rendering`) as integers `1`–`5` |
| `ReviewComment` | Free-text feedback up to 2000 characters |
| `PreferenceProfile` | `translationStyle`, `voiceStability`, `renderingPreset`, `lipSyncStrength`, `explanationLines[]` |

The frontend `ReviewPanel` on `/workspace` uses `ReviewService`.

---

# Team Collaboration (Platform Sprint 48)

| Method | Path | Description |
| ------ | ---- | ----------- |
| GET | `/api/workspaces/{id}/members` | List workspace members |
| POST | `/api/workspaces/{id}/members` | Invite a member (creates pending invitation) |
| PATCH | `/api/workspaces/{id}/members/{memberId}` | Update member role |
| DELETE | `/api/workspaces/{id}/members/{memberId}` | Remove a member |
| GET | `/api/workspaces/{id}/invitations` | List pending invitations |

## Schemas

| Schema | Values / fields |
| ------ | ---------------- |
| `WorkspaceMember` | `id`, `workspaceId`, `userId`, `displayName`, `role`, `joinedAt` |
| `WorkspaceInvitation` | `id`, `workspaceId`, `email`, `role`, `status`, `token`, `createdAt`, `expiresAt` |
| `WorkspaceRole` | `owner`, `editor`, `reviewer`, `viewer` |

Authorization uses the `X-Collaborator-Id` and `X-Collaborator-Name` headers until authentication is introduced in a later sprint.

The frontend `TeamPanel` on `/workspace` uses `CollaborationService`.

---

# Observability & Analytics (Platform Sprint 49)

| Method | Path | Description |
| ------ | ---- | ----------- |
| GET | `/api/workspaces/{id}/analytics` | Aggregated workspace analytics |
| GET | `/api/workspaces/{id}/providers` | Provider usage statistics |
| GET | `/api/workspaces/{id}/telemetry` | Pipeline telemetry records |

## Schemas

| Schema | Values / fields |
| ------ | ---------------- |
| `PipelineTelemetry` | `id`, `workspaceId`, `videoId`, `success`, `metrics[]`, `providerUsages[]`, `recordedAt`, optional `qualityScore`, `errorMessage` |
| `ExecutionMetric` | `type`, `value`, `unit` |
| `ProviderUsage` | `stage`, `providerId`, `invocationCount`, `totalDurationSeconds` |
| `WorkspaceAnalytics` | `processedVideos`, `averageProcessingTimeLabel`, `averageQuality`, `successRate`, `gpuUsagePercent`, top providers, `recentErrors[]` |
| `ProviderStatistics` | `providers[]` with invocation counts and average durations |

The frontend analytics panels on `/workspace` use `TelemetryService`.

---

# YouTube import (Platform Sprint 52)

| Method | Path | Role |
| ------ | ---- | ---- |
| `POST` | `/api/youtube` | Import URL → `VideoJob` + queue video pipeline |
| `POST` | `/api/youtube/preview` | Metadata preview (title, thumbnail, duration) |
| `GET` | `/api/youtube` | List recent imports |
| `GET` | `/api/youtube/{youtubeId}` | Import detail with linked `videoId` |

Schemas: `YouTubeMetadata`, `ImportYouTubeResponse`, `YouTubeImportResponse` in `Presentation/OpenApi/Schema/YouTubeSchemas.php`.

Invalid URLs return **400** (`InvalidYouTubeException`). Download failures return **502** (`YouTubeImporterException`).

After import, all video artifact endpoints (`/api/videos/{videoId}/…`) apply — no YouTube-specific routes.

The frontend `YouTubeImportPanel` at `/youtube/import` uses `youtubeSourceService`.

---

# Production considerations

| Topic | Recommendation |
| ----- | -------------- |
| Exposure | Keep `/api/docs` enabled in staging; restrict or disable in production if the API is not public-facing. |
| Caching | The spec is generated at request time; no build step required. |
| Versioning | Bump `info.version` in `nelmio_api_doc.yaml` when the public contract changes. |
| Contract tests | Future slices can consume `/api/docs.json` for consumer-driven contract tests or SDK generation. |
| Security | Documented routes match the public API plus the internal platform metrics diagnostic; processing worker callbacks stay undocumented by design. |

---

# Architectural decisions

1. **NelmioApiDocBundle** — Symfony-native, attribute-driven, no runtime change to business logic.
2. **OpenAPI attributes on controllers** — colocated with routes; avoids duplicating paths in YAML.
3. **`disable_default_routes: true`** — only actions with OpenAPI attributes are documented; internal routes stay excluded without maintaining deny lists.
4. **OpenAPI 3.1** — aligned with current JSON Schema draft used by modern tooling.
5. **Presentation-only schemas** — `OpenApi/` subtree keeps documentation types out of Domain.

---

# Related documentation

- [Architecture index](./README.md)
- [CI pipeline](./ci.md)
- [Architecture rules](./architecture-rules.md)
