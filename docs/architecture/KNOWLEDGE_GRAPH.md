# Knowledge Graph

Shadow Knowledge composes Memory and Teaching artefacts into a directed graph of concepts, technologies, and missions.

## Sources

| Source | Contribution |
|--------|--------------|
| Shadow Memory | Concepts, exposure, recall signals |
| Shadow Teaching | Objectives, missions, exercise progress |

## Node types

- `concept` — abstract ideas (DI, CQRS)
- `technology` — tools and platforms (Docker, Kubernetes)
- `framework` — Symfony, Doctrine
- `mission` — teaching missions
- `skill` — applied capabilities

## Edge types

- `prerequisite` — must know A before B
- `introduces` — A introduces B
- `depends_on` — runtime dependency
- `related_to` — soft association
- `used_by` — pattern usage
- `extends` — builds on

## Precedence in Shadow prompts

```text
Opt-out → manual edits → identity → relationship traits
→ teaching plan (S63) → knowledge reasoning (S64)
→ memory recall (S62) → session strategy (S60) → defaults
```

## Persistence

File store under `storage/shadow/knowledge/`. Rebuilt on question events and via `POST /api/shadow/knowledge/rebuild`.
