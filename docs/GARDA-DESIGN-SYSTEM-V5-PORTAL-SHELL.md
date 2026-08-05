# GARDA 01 Design System V5 — Portal Shell

## Phase

V5.0D — Unified Portal Shell.

## Objective

Unify the internal management experience without changing permissions, routes, controllers, models, database structure, or business workflows.

The Portal remains operationally denser than the public website, but now shares the same GARDA 01 brand system:

- navy and ivory foundation;
- restrained matte-gold accents;
- consistent geometry, elevation, typography, and interaction states;
- predictable desktop, compact-window, tablet, and mobile behavior.

## Source changes

### Updated

- `app/Views/layouts/main.php`

### Added

- `public/assets/css/garda-portal-shell-v5.css`

## Layout contract

The layout now loads the Portal shell after all legacy Portal styles. The final layer is scoped by:

```text
.garda-admin-body.garda-portal-v5
```

It also exposes safe page-level hooks:

```text
garda-admin-page--{segment}
data-admin-section="{section}"
```

These hooks will be used in V5.0E to migrate modules without global selector leakage.

## Accessibility additions

- skip link to the main content;
- focusable main content target;
- consistent `:focus-visible` behavior inherited from the V5 foundation;
- reduced-motion fallback;
- print-safe Portal shell.

## Portal shell coverage

- full and collapsed sidebar;
- automatic compact sidebar on medium desktop windows;
- mobile drawer and overlay;
- brand lockup;
- navigation active/hover states;
- sticky topbar;
- date, notification, public-site shortcut, and profile menu;
- content canvas and page heading;
- shared button, form, table, pagination, card, and empty-state geometry.

## Non-goals

This phase does not redesign every module-specific screen. Modules retain their existing markup and workflow until V5.0E.

No changes are made to:

- authorization or RBAC;
- navigation permissions;
- database and migrations;
- session or login security;
- backup, readiness, or monitoring logic;
- public-site routes or content.

## Runtime checklist

Test these representative pages:

```text
/dashboard
/members
/meetings
/activities
/cash
/publications
/website/pages
/users
/system/backups
/system/readiness
/system/operations
```

Test at:

- 1366 × 768;
- desktop window below 1280 px;
- tablet 768 px;
- mobile 360–390 px;
- browser zoom 80%, 100%, and 125%.

Confirm:

- sidebar and topbar do not overlap content;
- compact sidebar appears cleanly on medium windows;
- mobile drawer opens, closes, and blocks background interaction;
- profile menu remains inside the viewport;
- no horizontal page overflow;
- table wrappers continue to scroll independently;
- existing role-aware menu visibility remains unchanged;
- public pages are visually unchanged by the Portal-only stylesheet.
