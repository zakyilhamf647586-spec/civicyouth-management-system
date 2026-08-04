GARDA 01 DESIGN SYSTEM V5.0A
FOUNDATION + HOMEPAGE REGRESSION HOTFIX
=======================================

BASELINE
--------
Prepared from civicyouth-management-system-v5-baseline.zip.
The package deliberately does not replace the uncommitted V4.11.1 activity
view or PublicExperience configuration.

FILES ADDED
-----------
public/assets/css/garda-design-system-v5.css
docs/GARDA-DESIGN-SYSTEM-V5-AUDIT.md
docs/GARDA-DESIGN-SYSTEM-V5-SPEC.md
docs/GARDA-DESIGN-SYSTEM-V5-ROADMAP.md
INSTALL-README-V5.0A.txt

FILES UPDATED
-------------
app/Views/layouts/public.php
app/Views/layouts/main.php

INSTALLATION
------------
1. Back up the project or keep the current Git checkpoint available.
2. Extract this ZIP directly into the project root:
   C:\xampp\htdocs\civicyouth-management-system
3. Choose Replace for existing files.
4. Do NOT run migrations. There is no database change.
5. Do NOT run cache:clear if writable/cache currently has permission issues.
6. Restart the development server only if needed.
7. Use Ctrl + F5 in the browser. The stylesheet uses filemtime versioning.

VALIDATION
----------
php -l app\Views\layouts\public.php
php -l app\Views\layouts\main.php

Open:
- /home and /en/home in Ivory
- /home and /en/home in Midnight
- one Portal page such as /dashboard

Expected homepage result:
- the four statistics are independent premium cards;
- no connected black rectangle appears;
- desktop uses four columns;
- tablet/mobile use two columns;
- Midnight uses four dark cards with readable text.

GIT (ONLY AFTER VISUAL VALIDATION)
----------------------------------
git add app/Views/layouts/public.php
git add app/Views/layouts/main.php
git add public/assets/css/garda-design-system-v5.css
git add docs/GARDA-DESIGN-SYSTEM-V5-AUDIT.md
git add docs/GARDA-DESIGN-SYSTEM-V5-SPEC.md
git add docs/GARDA-DESIGN-SYSTEM-V5-ROADMAP.md
git add INSTALL-README-V5.0A.txt

git diff --cached --name-status

git commit -m "Establish GARDA 01 unified design foundation"
git push origin feature/social-publication-canva

Suggested checkpoint:
checkpoint-unified-design-foundation-v5.0a-2026-08-05
