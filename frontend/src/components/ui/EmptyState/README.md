# EmptyState

## Purpose

Placeholder when a list or view has no content yet.

## Variants

None. Single layout with optional action slot.

## Props

| Prop | Type | Default | Description |
| ---- | ---- | ------- | ----------- |
| `title` | `string` | — | Primary heading |
| `description` | `string` | — | Supporting text |
| `action` | `ReactNode` | — | Optional CTA (e.g. Button) |
| `className` | `string` | — | Optional layout override |

## Accessibility

- Title renders as `<h3>` within the page heading hierarchy.
- Description is plain paragraph text.
- When `action` is provided, consumer must use an accessible interactive element.

## Rules

- Must never contain business logic.
- Must be reusable.
- Action slot accepts any React node; no built-in routing or API calls.
- Styles use design tokens only.

## Example

```tsx
<EmptyState
  title="No content yet"
  description="Import your first PDF."
  action={<Button variant="primary">Import PDF</Button>}
/>
```
