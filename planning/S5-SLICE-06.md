# S5-SLICE-06 — Public Artifact Read API

Status: **Done**

Epic: **Epic 05 — Artifact Domain**

---

# Goal

Expose public read access to Artifacts for a Content.

---

# Created

```text
Application/Artifact/
├── Queries/ListArtifactsByContentQuery.php
├── Handlers/ListArtifactsByContentHandler.php
└── DTO/
    ├── ArtifactListItem.php
    └── ListArtifactsByContentResult.php

Presentation/Http/Controller/Artifact/ListArtifactsByContentController.php
Presentation/Http/Response/Artifact/ListArtifactsResponse.php

Domain: ArtifactRepositoryInterface::findByContentId()
Infrastructure: DoctrineArtifactRepository::findByContentId()
```

---

# Endpoint

`GET /api/contents/{contentId}/artifacts`

---

# Next

**S5-SLICE-07** — Frontend displays summary artifact after processing completes
