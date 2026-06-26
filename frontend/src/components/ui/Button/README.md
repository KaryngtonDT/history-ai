# Button

## Purpose

Generic action button for user-triggered interactions across the application.

## Variants

- `primary` — main call to action (slate background)
- `secondary` — secondary action (outlined)
- `ghost` — low-emphasis action (transparent)

## Sizes

- `sm`
- `md` (default)
- `lg`

## Props

| Prop | Type | Default | Description |
| ---- | ---- | ------- | ----------- |
| `variant` | `primary` \| `secondary` \| `ghost` | `primary` | Visual style |
| `size` | `sm` \| `md` \| `lg` | `md` | Padding and font size |
| `disabled` | `boolean` | `false` | Disables interaction |
| `children` | `ReactNode` | — | Button label |
| `onClick` | `MouseEventHandler` | — | Click handler |
| `type` | `button` \| `submit` \| `reset` | `button` | Native button type |
| `className` | `string` | — | Optional layout override |

Extends native `<button>` attributes.

## Accessibility

- Renders a native `<button>` element.
- Keyboard: focusable, activatable with Enter and Space.
- `disabled` sets the native `disabled` attribute and visual muted state.
- Visible focus ring on `:focus-visible`.

## Rules

- Must never contain business logic.
- Must be reusable across all features.
- Must remain accessible.
- Must support keyboard navigation.
- Must expose disabled state via the native attribute.
- Styles use design tokens only via `variables.css` (no hardcoded colors or spacing).

## Design tokens

Consumes semantic variables from `frontend/src/styles/variables.css` (e.g. `--button-primary-bg`).

## Example

```tsx
<Button variant="primary" size="md">
  Import PDF
</Button>
```
