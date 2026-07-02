# Shadow — Getting Started

1. Open **Settings → Shadow → Identity** to configure persona and voice.
2. Use **Relationship** to teach Shadow durable preferences (with approval).
3. Ask questions on **Watch** — Memory and Teaching plans evolve from activity.
4. Review progress in **Memory**, **Teaching**, and **Knowledge** tabs.

## Quick test

```bash
make prod-rebuild && make doctor
```

1. `/settings/shadow/teaching` — verify learning path and current lesson
2. `/settings/shadow/knowledge` — verify graph, paths, and gaps
3. `/video/{id}/watch` — verify Teaching and Knowledge panels in sidebar
4. Ask a technical question — memory, teaching, and knowledge prompt enrichment over time
