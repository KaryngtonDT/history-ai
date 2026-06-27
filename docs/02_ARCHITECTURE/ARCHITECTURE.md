# ARCHITECTURE SPECIFICATION

Project: History AI

Version: 1.0

Status: Draft

---

# 1. Architecture Goal

History AI must start as a simple MVP, but its architecture must allow future growth.

The first version will use a modular monolith with asynchronous workers.

This avoids unnecessary complexity while keeping the system scalable.

---

# 2. Core Architecture

The system contains five main parts:

1. Frontend application
2. Backend API
3. Worker system
4. Database
5. File storage

---

# 3. Technology Choices

Frontend:

* React
* TypeScript
* Vite
* Tailwind CSS

Backend:

* Symfony
* PHP 8.3+
* API Platform optional
* Symfony Messenger
* Doctrine ORM

Worker:

* Python
* yt-dlp
* Whisper
* OpenAI API
* Text-to-speech provider

Database:

* SQLite for local MVP
* PostgreSQL for production

Storage:

* Local storage for MVP
* S3-compatible object storage for production

Queue:

* Symfony Messenger with Doctrine transport for MVP
* Redis or RabbitMQ for production

---

# 4. High-Level Flow

User submits YouTube URL.

Backend creates a VideoJob.

Job is saved in database.

Backend dispatches async message.

Worker processes job.

Worker downloads audio.

Worker creates transcript.

Worker translates transcript.

Worker generates learning assets.

Worker generates French MP3.

Files are stored.

Job status becomes completed.

User downloads result.

---

# 5. System Diagram

```txt
User
 ↓
Frontend
 ↓
Symfony API
 ↓
Database
 ↓
Message Queue
 ↓
Python Worker
 ↓
AI Services
 ↓
File Storage
 ↓
Frontend Download
```

---

# 6. Backend Responsibilities

The backend is responsible for:

* authentication
* job creation
* job validation
* status tracking
* API responses
* file access control
* user library
* billing later
* admin tools later

The backend must not perform heavy audio processing directly.

Heavy processing belongs to workers.

---

# 7. Worker Responsibilities

Workers are responsible for:

* audio download
* audio conversion
* transcription
* translation
* content analysis
* text-to-speech
* PDF generation later
* cleanup
* retry handling

Workers must be stateless.

Every important state must be stored in the database.

---

# 8. Job Status Lifecycle

Allowed statuses:

* pending
* downloading
* transcribing
* translating
* analyzing
* generating_audio
* packaging
* completed
* failed
* cancelled

Every status change must include:

* timestamp
* optional error message
* optional progress percentage

---

# 9. File Storage Structure

```txt
storage/
  jobs/
    {jobId}/
      source_audio/
      transcript/
      translation/
      analysis/
      audio/
      exports/
      logs/
```

Example:

```txt
storage/jobs/123/audio/history_ai_123_fr.mp3
storage/jobs/123/transcript/original_en.txt
storage/jobs/123/translation/fr.txt
storage/jobs/123/analysis/summary.json
```

---

# 10. Backend Modules

The backend must be organized into modules:

```txt
src/
  Controller/
  Entity/
  Repository/
  Service/
  Message/
  MessageHandler/
  DTO/
  Enum/
  Security/
```

Recommended domain modules:

```txt
src/Domain/VideoJob/
src/Domain/User/
src/Domain/Podcast/
src/Domain/AI/
src/Domain/Storage/
```

---

# 11. Main Services

Required services:

* VideoJobService
* YouTubeUrlValidator
* JobStatusService
* StorageService
* DownloadService
* TranscriptionService
* TranslationService
* AnalysisService
* TextToSpeechService
* PodcastPackagingService

---

# 12. API Design

Initial endpoints:

```txt
POST   /api/jobs
GET    /api/jobs/{id}
GET    /api/jobs
GET    /api/jobs/{id}/download/audio
GET    /api/jobs/{id}/transcript
GET    /api/jobs/{id}/translation
GET    /api/jobs/{id}/analysis
DELETE /api/jobs/{id}
```

All endpoints must return JSON except file download endpoints.

---

# 13. Frontend Pages

Required MVP pages:

```txt
/
Dashboard
/jobs/{id}
```

Future pages:

```txt
/login
/register
/library
/settings
/pricing
/admin
```

---

# 14. Frontend Components

Required components:

* UrlInputForm
* JobStatusCard
* ProgressTimeline
* AudioPlayer
* DownloadButton
* TranscriptViewer
* SummaryCard
* ErrorMessage
* EmptyState
* LoadingSpinner

---

# 15. Error Handling

All errors must be stored.

Each failed job must have:

* failed status
* error code
* human-readable message
* technical details hidden from user
* retry possibility later

Example user message:

Processing failed because the audio could not be downloaded.

Example internal message:

yt-dlp returned exit code 1 for URL xyz.

---

# 16. Security

Security principles:

* never store API keys in code
* use environment variables
* validate all URLs
* block local network URLs
* block private IP ranges
* restrict file downloads to job owner
* sanitize filenames
* limit video duration
* limit file size
* log abuse patterns

---

# 17. Copyright and Compliance

History AI must respect copyright.

The MVP is for personal learning use.

The application must not publicly redistribute generated audio.

Generated files belong to the user library.

Future public sharing requires legal review.

---

# 18. Scalability Strategy

MVP:

* one backend
* one worker
* SQLite
* local storage

Small production:

* Symfony backend
* PostgreSQL
* Redis queue
* local or S3 storage
* one or more workers

Large production:

* multiple backend instances
* multiple worker pools
* S3-compatible storage
* monitoring
* rate limiting
* billing system
* queue priority system

---

# 19. Observability

The system must track:

* job creation time
* processing duration
* error rate
* transcription duration
* translation duration
* audio generation duration
* storage size
* API cost per job
* user activity

---

# 20. Cost Control

Every job must record estimated cost.

Cost sources:

* transcription
* translation
* text-to-speech
* storage
* compute

Premium limits must be based on cost.

---

# 21. Architecture Principle

Do not build microservices too early.

Start with a modular monolith.

Use clear boundaries.

Move modules into services only when scaling pressure is real.
