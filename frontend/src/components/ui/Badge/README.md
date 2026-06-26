# Badge

## Purpose

Compact label for status, category, or metadata display.

## Variants

- `success`
- `warning`
- `danger`
- `info`
- `neutral` (default)

## Props

| Prop | Type | Default | Description |
| ---- | ---- | ------- | ----------- |
| `variant` | see Variants | `neutral` | Semantic color style |
| `children` | `ReactNode` | — | Badge label |
| `className` | `string` | — | Optional layout override |

Extends native `<span>` attributes.

## Accessibility

- Renders with `role="status"` for live status text.
- Non-interactive; do not use as a button or link.

## Rules

- Must never contain business logic.
- Must be reusable.
- Must not be used as the sole indicator of state (pair with text when critical).
- Styles use design tokens only.

## Example

```tsx
<Badge variant="success">Completed</Badge>
```
