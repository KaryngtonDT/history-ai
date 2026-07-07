# Sprint 70.8 Verification

## Scope

Background pipeline orchestration, YouTube transcript choice, progress UX, stage confirmation.

## Acceptance checklist

- [x] YouTube import fetches original-language captions first
- [x] User chooses YouTube transcript vs local STT when captions exist
- [x] Local STT starts in background with estimated duration when captions missing
- [x] No fake 5-minute Shadow Watch failure while backend STT still running
- [x] Page refresh reads existing pipeline job (no duplicate via `getOrCreateJob`)
- [x] One active process per source/stage locking
- [x] Progress visible in `PipelineProgressPanel` and video overview
- [x] Stage completion → `waiting_user_confirmation` + notification
- [x] Restart invalidates downstream stages
- [x] Transcript metadata exposed via API

## Validation commands

```bash
make migrate
docker compose -f docker-compose.prod-like.yml exec backend php bin/phpunit
cd frontend && npm run build && npm run test
```
