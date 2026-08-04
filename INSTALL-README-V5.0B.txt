GARDA 01 — Unified Public Shell V5.0B
======================================

This is a cumulative root-ready package.
It includes the V5.0A design foundation plus the V5.0B public shell.
It is safe to apply whether V5.0A has already been installed or not,
provided the project baseline matches the latest V5 audit source.

INSTALL
-------
1. Make a verified backup.
2. Extract the ZIP directly into the project root.
3. Choose Replace files in destination.
4. No migration is required.
5. Do not run cache:clear if writable/cache has permission problems.
6. Restart the local server if needed and press Ctrl+F5.

VALIDATE
--------
php -l app\Views\layouts\public.php
php -l app\Views\layouts\main.php

Visual targets:
- unified navbar geometry and active state;
- premium mobile navigation drawer;
- Jelajah fits normal desktop viewports without internal scrolling;
- fixed utilities do not overlap while Jelajah is open;
- footer uses one consistent premium system;
- Ivory and Midnight remain readable.

NO DATABASE CHANGES
-------------------
No route, controller, model, permission, migration, backup, or monitoring
logic is changed by this phase.
