# PR Quality Checklist

Use this checklist for every frontend pull request.

```text
□ Aucun fetch hors HttpClient
□ Aucun import.meta.env hors config
□ Aucun repository appelé par React
□ Aucun composant métier dans ui/
□ Aucun type API utilisé directement par les composants
□ Aucun mock importé dans une feature
□ Aucun style inline
□ Aucun any TypeScript
```

## English reference

| Rule | Rationale |
|------|-----------|
| No `fetch` outside `HttpClient` | Single HTTP gateway, consistent errors |
| No `import.meta.env` outside `src/config/` | Centralized configuration |
| No repository called from React | Features → services → repositories |
| No domain components in `ui/` | `ui/` is generic primitives only |
| No API DTO types in components | Use domain models from `domain/` |
| No mock imports in features | Mocks live in repositories / `src/mock/` |
| No inline styles | CSS Modules for all styling |
| No TypeScript `any` | Strict typing end-to-end |

## Verification commands

```bash
cd frontend
npm run check    # Biome lint + format
npm test         # Vitest
npm run build    # Production build
```

Optional grep checks:

```bash
# fetch outside HttpClient (should only hit HttpClient.ts)
rg "fetch\(" src --glob '!**/HttpClient.ts'

# import.meta.env outside config
rg "import\.meta\.env" src --glob '!**/config/**'

# repository imports in features
rg "Repository" src/features
```
