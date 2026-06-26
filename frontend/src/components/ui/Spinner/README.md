# Spinner

## Purpose

Indeterminate loading indicator for async operations (upload, processing, AI generation).

## Variants

None. Single spinning indicator.

## Props

| Prop | Type | Default | Description |
| ---- | ---- | ------- | ----------- |
| `label` | `string` | `"Loading"` | Accessible name (`aria-label`) |
| `className` | `string` | — | Optional size override |

## Accessibility

- Renders with `role="status"`.
- `aria-label` describes the loading state for screen readers.

## Rules

- Must never contain business logic.
- Must be reusable.
- CSS-only animation (no JS timers).
- Styles use design tokens only.

## Example

```tsx
<Spinner label="Uploading file" />
```
