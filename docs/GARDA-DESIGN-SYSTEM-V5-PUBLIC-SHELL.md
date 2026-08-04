# GARDA 01 Design System V5 — Public Shell (V5.0B)

## Scope

Phase V5.0B standardizes the public shell without changing application logic:

- sticky public navbar;
- responsive mobile navigation;
- public preference trigger integration;
- reading progress and chapter utilities;
- Jelajah launcher and command deck;
- official public footer.

## Design contract

The public shell uses the V5 foundation tokens from `garda-design-system-v5.css`.
All V5.0B selectors are scoped to:

```css
.public-body.garda-design-v5
```

This prevents the public shell from affecting login or management-portal pages.

## Jelajah behavior

- Normal desktop view is designed to fit within the viewport without internal scrolling.
- Very short viewports use a controlled scroll only in the navigation list.
- Opening Jelajah hides fixed chapter/launcher utilities so controls cannot overlap.
- Existing keyboard focus trap, Escape handling, and body lock remain controlled by `public-experience-v3.js`.

## Files

- `app/Views/layouts/public.php`
- `public/assets/css/garda-design-system-v5.css`
- `public/assets/css/garda-public-shell-v5.css`
- `app/Views/layouts/main.php` (included cumulatively from V5.0A)

## Runtime checks

Test both Ivory and Midnight on:

- `/home`
- `/profil`
- `/program`
- `/kegiatan`
- `/pengurus`
- `/kontak`
- English equivalents

Viewport matrix:

- 1366×768
- 1280×720
- 1024×768
- 768×1024
- 390×844
- browser zoom 80%, 100%, 125%
