# Card

## Purpose

Simple elevated container for grouping related content.

## Variants

None. Single visual style.

## Props

| Prop | Type | Default | Description |
| ---- | ---- | ------- | ----------- |
| `children` | `ReactNode` | — | Card content |
| `className` | `string` | — | Optional layout override |

Extends native `<div>` attributes.

## Accessibility

- Renders a plain `<div>`. No interactive semantics.
- When the card wraps interactive content, consumers must provide appropriate labels and roles.

## Rules

- Must never contain business logic.
- Must be reusable.
- No internal state.
- Styles use design tokens only.

## Example

```tsx
<Card>
  <p>Welcome to History AI</p>
</Card>
```
