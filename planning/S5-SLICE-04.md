# S5-SLICE-04 — Create Artifact Internal API

Status: **Done**

Epic: **Epic 05 — Artifact Domain**

---

# Goal

Expose CreateArtifactHandler through an internal API endpoint for the Worker.

---

# Created

```text
Presentation/Http/Controller/Artifact/Internal/CreateArtifactInternalController.php
Presentation/Http/Request/Artifact/CreateArtifactRequest.php
Presentation/Http/Response/Artifact/CreateArtifactResponse.php

tests/Functional/Artifact/CreateArtifactInternalControllerTest.php
```

---

# Endpoint

`POST /internal/artifacts`

---

# Next

**S5-SLICE-05** — Worker integration to call internal artifact API (done)
