# Platform Detection

Deterministic URL-based detection — no ML, no aggressive scraping.

## MVP platforms

YouTube, Wikipedia, MDN, Symfony Docs, PHP Docs, GitHub, GitLab, Stack Overflow, Reddit, PDF viewer, unknown.

## Backend

`App\Application\ShadowBrowser\PlatformDetectionEngine`

## Extension

`browser-extension/shared/platforms.ts` (mirrors backend rules)

## API

`POST /api/shadow/browser/platform` with `{ "url": "..." }`
