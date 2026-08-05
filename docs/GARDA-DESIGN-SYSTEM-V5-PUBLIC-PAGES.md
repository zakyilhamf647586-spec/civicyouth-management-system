# GARDA 01 Design System V5 — Public Pages (V5.0C)

## Objective

V5.0C migrates the public content layer to one coherent GARDA 01 visual system after V5.0A established canonical tokens and V5.0B unified the public shell.

The phase is visual-only. It does not change routes, controllers, models, database schema, publication workflow, RBAC, backup, or monitoring.

## Pages covered

- Introducing (`/` and `/en`)
- Beranda (`/home` and `/en/home`)
- Profil / About
- Program index and program detail
- Kegiatan / Activities index
- Activity detail
- Pengurus / Team
- Kontak / Contact

## Design contracts

### Public hierarchy

- Editorial hero with controlled navy–gold treatment
- Consistent section heading scale and vertical rhythm
- One card geometry, border system, and elevation language
- Ivory and Midnight surfaces derived from canonical tokens
- Restrained gold used for labels, active states, and focus
- Consistent primary, secondary, and text-link behavior

### Responsive behavior

- Desktop: editorial split layouts and structured grids
- Tablet: two-column modules where appropriate
- Mobile: single-column flow, full-width actions, horizontal activity filters
- Activity detail summary becomes non-sticky below desktop
- Focus and reduced-motion behavior remain supported

### Page-specific improvements

#### Home

- Unified premium hero and featured activity
- Independent statistic cards
- Consistent values, program, activity, impact, and collaboration sections

#### Profile

- Stronger identity hierarchy
- Refined story, identity, vision, mission, and value cards

#### Programs

- Consistent pillar card language
- More refined program summary and detail hierarchy

#### Activities

- Premium segmented filter
- Consistent image ratio, card rhythm, metadata, and pagination

#### Activity detail

- Stable documentation/summary split
- Readable Midnight summary panel
- Editorial impact section
- Related activity cards with designed fallback media

#### Officials

- Unified structure and profile surfaces
- Clearer hierarchy between primary and supporting positions

#### Contact

- Readable Midnight form fields
- Consistent channel, location, response, and form panels

#### Introducing

- Cinematic concept preserved
- Color, focus, control geometry, and reduced-motion aligned with V5

## Scope discipline

The new page layer is loaded last and scoped to:

```text
.public-body.garda-design-v5
```

Introducing uses its own isolated scope:

```text
.g01-intro-body
```

No Portal selector is included in the V5.0C public page stylesheet.

## Runtime checklist

Test in Ivory and Midnight:

- desktop 1366×768;
- desktop non-fullscreen;
- tablet 768 px;
- mobile 360–390 px;
- browser zoom 80%, 100%, 125%;
- Indonesian and English routes;
- pages with and without images;
- empty states and pagination;
- keyboard focus and reduced motion.
