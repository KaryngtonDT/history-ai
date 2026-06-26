# Component Contracts

Status: **Active**

---

# Rule

Every shared component in `frontend/src/components/` **must** include a `README.md` that documents its contract before or alongside implementation.

**Design tokens:** components must consume `frontend/src/styles/variables.css` only. No hardcoded visual values (Engineering Principle 16).

Treat each component as a public API — with the same rigour as a REST endpoint or RFC.

---

# README structure

Each `Component/README.md` describes:

1. **Purpose** — what problem it solves
2. **Variants** — visual or behavioural options
3. **Props** — typed API surface
4. **Accessibility** — roles, keyboard, ARIA
5. **Rules** — what the component must never do
6. **Example** — minimal usage

---

# File structure (target)

```text
Component/
    Component.tsx
    Component.test.tsx      (when relevant)
    Component.module.css
    README.md
    index.ts
```

Future additions (Storybook, tokens) extend this folder — the structure stays stable.

---

# Folder taxonomy (future)

```text
components/
    ui/           — current Sprint 1 location
    forms/        — Button, Input, Dropzone…
    layout/       — Card, PageContainer, Section…
    feedback/     — Spinner, Progress, Badge, EmptyState…
    navigation/   — Sidebar, Topbar, Breadcrumb…
```

Reorganization is deferred until the library grows. Sprint 1 remains under `components/ui/`.

---

# Precedence

Component README → Design System docs (`docs/07_DESIGN_SYSTEM/`) → implementation.
