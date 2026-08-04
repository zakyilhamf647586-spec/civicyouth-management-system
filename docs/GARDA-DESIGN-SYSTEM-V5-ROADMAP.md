# GARDA 01 Design System V5 — Execution Roadmap

## V5.0A — Foundation and regression hotfix

Status after installing this package: **ready for runtime validation**.

Changes:

- add canonical design tokens and compatibility aliases;
- load one final design-system layer in public and Portal layouts;
- add `.garda-design-v5` scope;
- remove the connected black homepage statistics strip;
- provide consistent Ivory and Midnight statistics cards;
- standardize focus, typography, button motion, and control geometry safely.

Exit criteria:

- homepage statistics no longer render as a connected black field;
- Ivory and Midnight both remain legible;
- Portal pages retain their layout;
- no route or functionality changes;
- PHP/CSS validation passes;
- checkpoint created.

## V5.0B — Public shell unification

Scope:

- navbar;
- mobile navigation;
- Jelajah deck;
- footer;
- page canvas;
- section headings;
- common public buttons and metadata.

Target files:

- public layout and partials;
- new V5 public-shell CSS;
- minimal JavaScript refinements where needed.

Exit criteria:

- one navbar/footer language across all public pages;
- no floating-control collision;
- consistent theme behavior;
- no cross-page selector leakage.

## V5.0C — Public content components

Order:

1. Beranda.
2. Profil.
3. Program and program detail.
4. Kegiatan and activity detail.
5. Pengurus.
6. Kontak.
7. Introducing.

Each module receives:

- component inventory;
- Ivory/Midnight rules;
- responsive behavior;
- empty/fallback state;
- bilingual verification;
- visual regression checklist.

## V5.0D — Portal shell unification

Scope:

- sidebar;
- topbar;
- breadcrumb/context header;
- page container;
- notifications/profile menu;
- button, form, table, modal, toast, pagination, and empty-state primitives.

No business logic is changed.

## V5.0E — Portal module migration

Order:

1. Dashboard.
2. Members.
3. Structure.
4. Meetings and attendance.
5. Activities and programs.
6. Cash and reports.
7. Publications and Content Studio.
8. Public CMS and navigation.
9. Users/account security.
10. Backup, readiness, and operations.

Every module is migrated to shared primitives instead of adding another independent stylesheet dialect.

## V5.0F — Quality gate

- full responsive audit;
- keyboard and contrast audit;
- reduced-motion audit;
- cross-browser test;
- full regression;
- UAT per role;
- performance review;
- CSS retirement plan;
- release candidate checkpoint.

## Rules throughout V5

- do not alter database schema for a visual-only phase;
- do not replace whole project archives with older phase packages;
- use selective staging;
- preserve current V4.11.1 working-tree changes;
- one phase, one runtime test, one checkpoint;
- old CSS is retired only after its replacement passes.
