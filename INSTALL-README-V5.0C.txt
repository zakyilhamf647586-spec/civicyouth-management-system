GARDA 01 — V5.0C UNIFIED PUBLIC PAGES
======================================

PACKAGE TYPE
-------------
Cumulative root-ready package:
V5.0A Foundation + V5.0B Public Shell + V5.0C Public Pages.

INSTALL LOCATION
----------------
Extract directly into:
C:\xampp\htdocs\civicyouth-management-system

Choose:
Replace the files in the destination

MIGRATION
---------
No database migration is required.

CACHE
-----
Do not run php spark cache:clear when the local cache directory has
permission issues. CSS and PHP assets use filemtime versioning.
Use Ctrl + F5 after installation, or restart php spark serve.

SYNTAX CHECK
------------
php -l app\Views\layouts\public.php
php -l app\Views\layouts\main.php
php -l app\Views\public\introducing.php

RUNTIME TEST
------------
Public routes:
/
/home
/profil
/program
/kegiatan
/pengurus
/kontak

English routes:
/en
/en/home
/en/about
/en/programs
/en/activities
/en/team
/en/contact

Test Ivory, Midnight, desktop, tablet, mobile, and browser zoom.

SELECTIVE GIT STAGING
---------------------
git add app/Views/layouts/public.php
git add app/Views/layouts/main.php
git add app/Views/public/introducing.php

git add public/assets/css/garda-design-system-v5.css
git add public/assets/css/garda-public-shell-v5.css
git add public/assets/css/garda-public-pages-v5.css
git add public/assets/css/garda-introducing-v5.css

git add docs/GARDA-DESIGN-SYSTEM-V5-AUDIT.md
git add docs/GARDA-DESIGN-SYSTEM-V5-SPEC.md
git add docs/GARDA-DESIGN-SYSTEM-V5-ROADMAP.md
git add docs/GARDA-DESIGN-SYSTEM-V5-PUBLIC-SHELL.md
git add docs/GARDA-DESIGN-SYSTEM-V5-PUBLIC-PAGES.md

git add INSTALL-README-V5.0A.txt
git add INSTALL-README-V5.0B.txt
git add INSTALL-README-V5.0C.txt

Review:
git diff --cached --name-status

Suggested commit:
git commit -m "Unify GARDA 01 public page experience"

git push origin feature/social-publication-canva

Suggested checkpoint:
git tag -a checkpoint-unified-public-pages-v5.0c-2026-08-05 `
    -m "Unified GARDA 01 public pages and responsive experience"

git push origin checkpoint-unified-public-pages-v5.0c-2026-08-05
