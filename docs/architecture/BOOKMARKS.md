# Bookmarks & Personal Notes

User-owned overlays on the Second Brain — **never merged** into auto-generated `KnowledgeEntry` content.

## Actions

| Action | API |
|--------|-----|
| Favorite concept | `POST /api/shadow/brain/bookmark` |
| Personal note | `POST /api/shadow/brain/note` |
| Remove bookmark | `DELETE /api/shadow/brain/bookmark/{id}` |
| Pin resource | bookmark with `resourceType` + `resourceId` |
| Tags | on bookmark or note |

## Domain types

- `KnowledgeBookmark` — conceptKey or resource ref, optional tags
- Personal notes stored separately from Shadow Memory (user editorial layer)

## Frontend

`Bookmarks/`, `PersonalNotes/` under `features/shadowBrain/`
