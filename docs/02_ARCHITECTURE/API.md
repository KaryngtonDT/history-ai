# API SPECIFICATION

Project: History AI

Version: 1.0

Status: Draft

---

# 1. API Philosophy

The API follows REST principles.

JSON is the default response format.

Authentication will be introduced after the MVP.

The API is versioned.

Example:

/api/v1/jobs

---

# 2. Standard Response Format

Successful response

```json
{
    "success": true,
    "data": {},
    "meta": {},
    "errors": []
}
```

Failed response

```json
{
    "success": false,
    "data": null,
    "errors": [
        {
            "code": "DOWNLOAD_FAILED",
            "message": "Unable to download video."
        }
    ]
}
```

---

# 3. Authentication

MVP

Authentication optional.

Future

JWT Authentication

OAuth Google

GitHub Login

Apple Login

Microsoft Login

---

# 4. Jobs

## Create Job

POST

/api/v1/jobs

Purpose

Create a new processing job.

Request

```json
{
    "url":"https://youtube.com/watch?v=xxxxx",
    "targetLanguage":"fr",
    "voice":"male_01"
}
```

Response

```json
{
  "success":true,
  "data":{
      "jobId":"123",
      "status":"pending"
  }
}
```

---

## Get Job

GET

/api/v1/jobs/{id}

Returns

* current status
* progress
* generated assets
* timestamps

---

## List Jobs

GET

/api/v1/jobs

Supports

page

limit

status

language

sort

search

---

## Delete Job

DELETE

/api/v1/jobs/{id}

Deletes

* metadata

Optionally

* generated files

---

# 5. Job Status

GET

/api/v1/jobs/{id}/status

Returns

```json
{
 "status":"translating",
 "progress":63
}
```

---

# 6. Downloads

Download MP3

GET

/api/v1/jobs/{id}/download/audio

Download Transcript

GET

/api/v1/jobs/{id}/download/transcript

Download Translation

GET

/api/v1/jobs/{id}/download/translation

Download Summary

GET

/api/v1/jobs/{id}/download/summary

Download PDF

GET

/api/v1/jobs/{id}/download/pdf

---

# 7. Transcript

GET

/api/v1/jobs/{id}/transcript

Response

Original transcript.

---

# 8. Translation

GET

/api/v1/jobs/{id}/translation

Returns translated transcript.

---

# 9. AI Analysis

GET

/api/v1/jobs/{id}/analysis

Returns

Summary

Timeline

Glossary

Concepts

Quiz

Flashcards

Entities

References

---

# 10. Podcast

GET

/api/v1/podcasts

Returns

User library.

---

GET

/api/v1/podcasts/{id}

Returns

Podcast details.

---

# 11. Search

GET

/api/v1/search

Query parameters

keyword

topic

author

country

language

---

# 12. Future AI Tutor

POST

/api/v1/tutor/chat

Body

```json
{
 "podcastId":12,
 "question":"Explain Toynbee."
}
```

Returns

Answer generated using only podcast knowledge.

---

# 13. User

Future endpoints

GET /me

PATCH /me

DELETE /me

---

# 14. Settings

Future

GET

/settings

PATCH

/settings

---

# 15. Notifications

Future

GET

/notifications

PATCH

/notifications/read

---

# 16. Error Codes

Examples

INVALID_URL

VIDEO_TOO_LONG

VIDEO_PRIVATE

VIDEO_NOT_FOUND

DOWNLOAD_FAILED

TRANSCRIPTION_FAILED

TRANSLATION_FAILED

VOICE_GENERATION_FAILED

INSUFFICIENT_CREDITS

UNKNOWN_ERROR

---

# 17. Rate Limits

Anonymous users

5 jobs/day

Registered users

20 jobs/day

Premium

Unlimited (fair usage)

---

# 18. Validation Rules

Supported platforms

YouTube

Maximum duration MVP

2 hours

Maximum audio size

500 MB

Supported languages MVP

English

French

German

Spanish

Italian

---

# 19. Versioning

Every endpoint begins with

/api/v1/

Future versions

/api/v2/

/api/v3/

Older versions remain supported during migration.

---

# 20. Documentation

The API must expose OpenAPI documentation.

Swagger UI should be enabled in development.

Every endpoint must include:

Description

Example request

Example response

Possible errors

Validation rules

Authentication requirements

---

# 21. API Design Principles

Every endpoint must be predictable.

No endpoint performs multiple unrelated actions.

Heavy processing is asynchronous.

The frontend never waits for long-running jobs.

Polling is acceptable for MVP.

WebSockets or Server-Sent Events can replace polling in future versions.
