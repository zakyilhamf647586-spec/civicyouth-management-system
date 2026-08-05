GARDA 01 — UNIFIED PORTAL SHELL V5.0D
======================================

PACKAGE TYPE
-------------
Cumulative root-ready package:
- V5.0A Unified Design Foundation
- V5.0B Unified Public Shell
- V5.0C Unified Public Pages
- V5.0D Unified Portal Shell

INSTALLATION
------------
1. Stop the local development server if it is running.
2. Extract this ZIP directly into:
   C:\xampp\htdocs\civicyouth-management-system
3. Choose "Replace the files in the destination".
4. Restart:
   php spark serve --host 127.0.0.1 --port 8080
5. Hard refresh the browser with Ctrl + F5.

NO DATABASE ACTION
------------------
- No migration is included.
- Do not run php spark migrate for this visual phase.
- No route, controller, model, permission, or database logic is changed.

LOCAL SYNTAX CHECK
------------------
php -l app\Views\layouts\main.php
php -l app\Views\layouts\public.php
php -l app\Views\public\introducing.php

RUNTIME CHECK
-------------
Review representative Portal screens:
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

Test desktop, compact desktop, tablet, and mobile.

SELECTIVE GIT STAGING
---------------------
git add app/Views/layouts/main.php
git add app/Views/layouts/public.php
git add app/Views/public/introducing.php

git add public/assets/css/garda-design-system-v5.css
git add public/assets/css/garda-public-shell-v5.css
git add public/assets/css/garda-public-pages-v5.css
git add public/assets/css/garda-introducing-v5.css
git add public/assets/css/garda-portal-shell-v5.css

git add docs/GARDA-DESIGN-SYSTEM-V5-AUDIT.md
git add docs/GARDA-DESIGN-SYSTEM-V5-SPEC.md
git add docs/GARDA-DESIGN-SYSTEM-V5-ROADMAP.md
git add docs/GARDA-DESIGN-SYSTEM-V5-PUBLIC-SHELL.md
git add docs/GARDA-DESIGN-SYSTEM-V5-PUBLIC-PAGES.md
git add docs/GARDA-DESIGN-SYSTEM-V5-PORTAL-SHELL.md

git add INSTALL-README-V5.0A.txt
git add INSTALL-README-V5.0B.txt
git add INSTALL-README-V5.0C.txt
git add INSTALL-README-V5.0D.txt

Do not use git add .

SUGGESTED COMMIT
----------------
git commit -m "Unify GARDA 01 Portal shell and primitives"
git push origin feature/social-publication-canva

SUGGESTED CHECKPOINT
--------------------
git tag -a checkpoint-unified-portal-shell-v5.0d-2026-08-05 -m "Unified GARDA 01 Portal shell and operational primitives"
git push origin checkpoint-unified-portal-shell-v5.0d-2026-08-05
