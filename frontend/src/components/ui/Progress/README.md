# Progress

## Purpose

Determinate progress indicator for operations with a known completion percentage (upload, processing, generation).

## Variants

None. Single determinate bar.

## Props

| Prop | Type | Default | Description |
| ---- | ---- | ------- | ----------- |
| `value` | `number` | — | Progress from 0 to 100 (clamped) |
| `className` | `string` | — | Optional layout override |

## Accessibility

- Renders with `role="progressbar"`.
- Exposes `aria-valuenow`, `aria-valuemin` (0), `aria-valuemax` (100).
- Value is clamped to 0–100.

## Rules

- Must never contain business logic.
- Must be reusable.
- No animation of value changes required in MVP.
- Styles use design tokens only.
- No inline styles.

## Example

```tsx
<Progress value={62} />
```
