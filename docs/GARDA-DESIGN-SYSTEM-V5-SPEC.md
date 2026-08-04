# GARDA 01 Unified Design System V5

## Product principle

**One identity, two experiences.**

The website and Portal use the same brand system, but they do not use the same information density or layout.

### Public website

- editorial;
- narrative;
- image-led;
- spacious;
- focused on trust, movement, and impact.

### Management Portal

- operational;
- data-led;
- compact but calm;
- focused on tasks, decisions, accountability, and speed.

## Canonical visual language

### Palette

- Navy 950: institutional depth and primary shell.
- Navy 900/800: elevated dark surfaces.
- Matte Gold 500/400: identity accent, active state, and controlled emphasis.
- Ivory 100/50: public and Portal canvas.
- White: primary card surface.
- Soft Slate: secondary text, dividers, disabled states.
- Soft green/yellow/red: semantic status only.

Gold must not be used as a decorative border on every component. It is reserved for:

- active navigation;
- kicker labels;
- short accent lines;
- status emphasis;
- primary numbers;
- high-value calls to action.

## Typography

The default interface stack is:

`Segoe UI Variable`, `Segoe UI`, system UI, then platform fallbacks.

- Display headings: strong weight, compact tracking, controlled line length.
- Body: 15–17px public, 13–15px Portal.
- Kicker/metadata: 9–11px, uppercase, tracked, limited use.
- Monospace: only for technical identifiers, sequence numbers, or field-directory details.

## Geometry

- Controls: 12px radius.
- Standard cards: 20px radius.
- Feature/editorial cards: 28px radius.
- Large shells/dialogs: 32px radius.
- Pills: only for status, filters, and compact metadata—not every button.

## Elevation

- Flat: tables, inline controls, navigation rows.
- Small: normal cards.
- Card: editorial modules and important work surfaces.
- Overlay: drawers, dialogs, and navigation deck.

Hover movement is limited to 1–4px. Visual hierarchy must come from scale, contrast, and spacing rather than excessive shadows.

## Interaction hierarchy

### Primary

One dominant action per surface. Navy on Ivory or matte gold on navy.

### Secondary

Outlined or soft-surface button. Used for edit, view, export, and supporting actions.

### Tertiary

Text link with a restrained arrow/icon. Used for navigation and low-risk actions.

### Destructive

Never permanently dominant in tables. Place inside an overflow menu or confirmation flow where possible.

## Theme contract

### Ivory

- warm canvas;
- white/ivory cards;
- navy text;
- restrained gold accent;
- shadows remain soft and cool.

### Midnight

- navy canvas;
- clearly separated dark surfaces;
- high-contrast white text;
- slate secondary text;
- gold remains an accent, not a text replacement;
- no white card with pale text;
- no element may rely on Ivory-only inherited colors.

## Responsive contract

- Desktop: use available width but preserve readable line length.
- Tablet: restructure grids; do not simply shrink desktop components.
- Mobile: prioritize one clear task/story per row.
- Tables: scroll within a deliberate wrapper or transform into a worklist where appropriate.
- Floating controls: must not overlap each other or primary content.
- Zoom: layouts must remain usable at 80%, 100%, 110%, and 125%.

## Accessibility contract

- visible `:focus-visible` state;
- minimum 44px touch targets for primary interactive elements;
- text contrast suitable for the active theme;
- semantic headings;
- reduced motion support;
- hover cannot be the only way to reveal essential information;
- status is expressed by text, not color alone.

## Migration policy

1. New V5 CSS is scoped with `.garda-design-v5`.
2. Existing routes, queries, and data contracts are preserved.
3. A module moves to V5 only after its existing behavior is mapped.
4. Old rules are removed only after visual and runtime regression tests pass.
5. Every phase ends with a checkpoint before the next module begins.
