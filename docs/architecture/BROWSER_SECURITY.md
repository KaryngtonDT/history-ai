# Browser Security

## Extension

- Manifest V3 only
- Minimal host permissions (`activeTab` + explicit allowlist patterns)
- No private GitHub repo analysis without user consent
- API calls to Lumen over HTTPS in production

## Backend

- Browser workspace scoped by `scopeKey`
- Integrates with Shadow Presence audit log
- CORS limited to known Lumen origins + localhost dev

## Explainability

`GET /api/shadow/browser/explain` — why Shadow appeared or acted.
