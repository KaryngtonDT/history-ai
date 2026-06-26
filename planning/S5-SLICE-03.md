# S5-SLICE-03 — Create Artifact Use Case

Status: **Done**

Epic: **Epic 05 — Artifact Domain**

---

# Goal

Application use case for the Worker to produce an Artifact.

---

# Created

```text
Application/Artifact/
├── Commands/CreateArtifactCommand.php
├── Handlers/CreateArtifactHandler.php
└── DTO/CreateArtifactResult.php

tests/Unit/Application/Artifact/CreateArtifactHandlerTest.php
```

---

# Next

**S5-SLICE-04** — Internal HTTP endpoint for Worker to call CreateArtifactHandler (done)
