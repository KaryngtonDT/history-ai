# Components

Version: 1.0

Status: Draft

---

# Priority Components (MVP)

Build these before any Feature UI:

```text
Button
Card
PageHeader
Section
Input
Textarea
Select
Dialog
Modal
Badge
StatusChip
Progress
Dropzone
EmptyState
ErrorState
LoadingState
Toast
```

Everything else comes later.

---

# Component Principles

| Rule | Detail |
| ---- | ------ |
| Generic | Components in `frontend/src/components/` know nothing about History AI domain |
| Composable | Features compose generic pieces |
| States | Every interactive component documents all states in Storybook |
| Tokens only | No raw hex or px in component files |

---

# Variants Summary

## Button

Primary (slate), Secondary (outline), Danger (rose), Disabled

## StatusChip

Draft, Processing, Ready, Failed — maps to RFC-0001 lifecycle language

## Dropzone

Empty, Hover, Dragging, Uploading, Error, Completed

## Progress

Determinate (upload %) and indeterminate (processing)

## EmptyState

Icon + title + description + optional action

---

# Documentation

Each component spec:

1. Purpose
2. Anatomy (parts)
3. States
4. Accessibility requirements
5. Storybook story list

Detailed mockups: `design/components/`

Implementation catalog: [COMPONENT_CATALOG.md](../../planning/Epics/Epic-00-UI-Foundation/Feature-UI-0001-Design-System/COMPONENT_CATALOG.md)

---

# Domain Components (Phase B+)

Live under `frontend/src/features/` — never in `components/`:

```text
UploadPdfButton
ContentCard
ProcessingTimeline
```

Compose generic components only.
