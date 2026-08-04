# GARDA 01 Design System V5 — Baseline Audit

## Baseline

Source archive: `civicyouth-management-system-v5-baseline.zip`

Historical Git state supplied in `AUDIT-CONTEXT.txt`:

- Branch: `feature/social-publication-canva`
- HEAD: `74bedfb Refine public activity pillar filter`
- Checkpoint: `checkpoint-premium-activity-filter-v4.10-2026-08-03`
- V4.11/V4.11.1 changes are present in the working tree but are not yet committed.
- Database migrations are current through batch 35 (`EnforcePublicInternalSeparation`).

## Confirmed regression: connected black statistics strip

The dark element on the desktop homepage is the `.garda-home-statistics` component.

It is created by the desktop rule in:

`public/assets/css/public-experience-v3.css`

At desktop width (`min-width: 921px`) the rule intentionally converts the four statistics cards into one connected navy field by applying:

- zero gap;
- a single dark background on the parent;
- transparent, borderless child cards;
- white text and gold numbers.

A second rule repeats the same treatment below 921px. That means the component changes from the earlier white-card design into a dark connected strip at every viewport.

### Important conclusion

This regression was **not introduced by V4.11.1**. The current `AUDIT-CONTEXT.txt` lists only these modified source files:

- `app/Config/PublicExperience.php`
- `app/Views/public/activity_detail.php`
- `public/assets/css/public-preferences.css`
- `INSTALL-README.txt`

`public-experience-v3.css` is not modified. The dark strip is therefore an older V3 design decision that became visually inconsistent with the newer pages and was noticed during the latest polishing work.

## CSS architecture findings

### 1. One monolithic stylesheet serves both products

`public/assets/css/app.css` contains approximately 9,787 lines and includes:

- legacy application shell;
- older public landing page;
- public activity pages;
- officials pages;
- public navbar and footer;
- profile/program pages;
- public homepage;
- contact page;
- admin shell;
- dashboard;
- login;
- website settings;
- miscellaneous stability overrides.

Both the public website and Portal load this file. A broad selector added for one product can therefore affect the other product.

### 2. Public pages load eight global CSS layers

The public layout loads these layers for every regular public page:

1. `app.css`
2. `public-footer-refinement.css`
3. `public-home-impact.css`
4. `public-cms-preview.css`
5. `public-external-review.css`
6. `public-premium-v2.css`
7. `public-experience-v3.css`
8. `public-preferences.css`

Some pages add another page-specific stylesheet through the `head` section.

The practical result is a cascade based on chronology rather than component ownership: the last patch wins.

### 3. Public layers redefine the same components repeatedly

Static analysis of the global public stylesheets found approximately:

- 2,650 selector occurrences;
- 1,908 unique selector strings;
- 464 selector definitions repeated in more than one public layer or repeated inside a layer.

Frequently redefined components include:

- `.garda-home-statistics article`
- `.public-program-summary`
- `.activity-detail-main`
- `.activity-editorial-section`
- `.garda-home-section`
- `.program-pillar-card`
- `.public-activity-card`
- form controls such as `input`, `select`, and `textarea`.

### 4. File responsibilities no longer match file names

`public-preferences.css` has grown to approximately 2,060 lines. Besides language/theme controls it now contains:

- Midnight surface overrides for most public components;
- activity filter V4.10;
- activity-detail V4.11/V4.11.1;
- responsive activity-detail behavior.

This makes a change to a “preferences” file capable of altering unrelated pages.

### 5. Three visual generations coexist

The public site currently combines:

- base styles in `app.css`;
- Premium V2;
- Experience V3;
- page-specific refinement layers;
- theme-specific overrides.

Each generation uses slightly different:

- navy and gold values;
- border opacity;
- corner radius;
- shadow depth;
- typography scale;
- card philosophy.

This is why individual pages can feel polished but not feel like one product.

### 6. Portal modules also use multiple local design dialects

The Portal combines the shared `app.css` with:

- master shell responsive CSS;
- table responsive CSS;
- dashboard CSS;
- publications base/simplified/polish layers;
- independent CSS for backup, readiness, operations, users, CMS, SEO, and other modules.

The functional structure is strong, but component geometry and hierarchy vary by module.

## V5.0A response

V5.0A introduces a new last-loaded, strictly scoped compatibility layer:

`public/assets/css/garda-design-system-v5.css`

It provides:

- one canonical token set;
- aliases for the existing legacy variables;
- shared typography, focus, button, and form contracts;
- public Ivory and Midnight surface contracts;
- Portal surface contracts;
- the homepage statistics regression fix;
- a scope marker: `.garda-design-v5`.

The existing stylesheets remain intact during the migration. This avoids a risky all-at-once rewrite while ensuring that every subsequent V5 component is based on the same visual language.

## Security and source hygiene observation

The uploaded audit ZIP includes SQL files under `/backups/`. The directory is already ignored by `.gitignore`, but future review archives should omit it because database dumps can contain private organization and account data.
