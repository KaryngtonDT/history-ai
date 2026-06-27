# DATABASE SPECIFICATION

Project: History AI

Version: 1.0

Status: Draft

---

# 1. Database Goal

The database stores users, video jobs, generated podcasts, processing status, files, AI outputs and future billing information.

The MVP starts with SQLite.

Production should use PostgreSQL.

---

# 2. Main Entities

The MVP requires these entities:

* User
* VideoJob
* JobStatusHistory
* GeneratedFile
* Transcript
* Translation
* AnalysisResult
* Podcast

---

# 3. User

Table: users

Purpose: stores application users.

Fields:

* id
* email
* password_hash
* preferred_language
* preferred_voice
* created_at
* updated_at

Notes:

For the MVP, authentication can be optional.

If authentication is skipped, every job can belong to a temporary anonymous user.

---

# 4. VideoJob

Table: video_jobs

Purpose: stores each YouTube processing request.

Fields:

* id
* user_id
* source_url
* source_platform
* original_title
* original_channel
* original_language
* target_language
* status
* progress
* error_code
* error_message
* duration_seconds
* estimated_cost
* created_at
* updated_at
* started_at
* completed_at
* failed_at

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

Indexes:

* user_id
* status
* created_at
* source_url

---

# 5. JobStatusHistory

Table: job_status_history

Purpose: stores every job status transition.

Fields:

* id
* video_job_id
* from_status
* to_status
* progress
* message
* created_at

Why this matters:

It gives observability.

It helps debugging.

It allows a visible progress timeline in the frontend.

---

# 6. GeneratedFile

Table: generated_files

Purpose: stores file metadata.

Fields:

* id
* video_job_id
* file_type
* language
* storage_path
* original_filename
* mime_type
* size_bytes
* checksum
* created_at

Allowed file types:

* source_audio
* transcript_txt
* translation_txt
* analysis_json
* podcast_mp3
* export_pdf
* export_markdown
* export_json

---

# 7. Transcript

Table: transcripts

Purpose: stores transcription text and metadata.

Fields:

* id
* video_job_id
* language
* text
* word_count
* provider
* confidence_score
* created_at

---

# 8. Translation

Table: translations

Purpose: stores translated text.

Fields:

* id
* video_job_id
* source_language
* target_language
* text
* provider
* model
* word_count
* created_at

---

# 9. AnalysisResult

Table: analysis_results

Purpose: stores AI-generated educational assets.

Fields:

* id
* video_job_id
* summary
* short_summary
* key_ideas_json
* timeline_json
* entities_json
* glossary_json
* quiz_json
* flashcards_json
* created_at

JSON fields are acceptable for the MVP.

Later, frequently queried structures can be normalized.

---

# 10. Podcast

Table: podcasts

Purpose: stores final generated podcast metadata.

Fields:

* id
* video_job_id
* title
* description
* language
* duration_seconds
* voice_provider
* voice_id
* audio_file_id
* created_at

---

# 11. Future Billing Tables

Later tables:

* subscriptions
* invoices
* usage_records
* api_cost_records
* payment_events

Do not implement billing in the MVP.

---

# 12. Entity Relationships

```txt id="73s05r"
User
 └── VideoJob
      ├── JobStatusHistory
      ├── GeneratedFile
      ├── Transcript
      ├── Translation
      ├── AnalysisResult
      └── Podcast
```

---

# 13. MVP Simplification

For the first implementation, it is acceptable to start with only:

* VideoJob
* JobStatusHistory
* GeneratedFile

Then add:

* Transcript
* Translation
* AnalysisResult
* Podcast

This reduces implementation risk.

---

# 14. Data Retention

MVP:

Generated files are kept indefinitely.

Production:

Free users:

* keep generated files for 7 days

Premium users:

* keep generated files permanently or according to plan limits

---

# 15. Privacy

User-generated content must be private by default.

No podcast is publicly visible unless explicitly shared.

Generated files must not be indexable by search engines.

---

# 16. Database Design Principles

Use UUIDs or integer IDs consistently.

Use immutable created_at fields.

Use updated_at for mutable records.

Avoid storing secrets.

Avoid storing raw API responses unless needed for debugging.

Use JSON fields for MVP speed.

Normalize later when query patterns become clear.

---

# 17. Initial Doctrine Entities

Recommended first entities:

```txt id="qsd4rf"
VideoJob
JobStatusHistory
GeneratedFile
```

Do not create all future entities immediately.

Start small.

Add complexity only when the MVP pipeline works.
