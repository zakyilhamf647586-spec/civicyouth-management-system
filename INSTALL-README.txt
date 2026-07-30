GARDA 01 — MONITORING, HEALTH CHECK & OPERATIONAL DASHBOARD V1
===============================================================

PRASYARAT
---------
Fase 4B sudah berada pada source terbaru.

MIGRATION
---------
php spark migrate

COMMAND
-------
php spark system:health
php spark system:health --json
php spark system:health --snapshot
php spark system:health:snapshot
php spark system:health:prune --days 30

PORTAL
------
/system/operations

PUBLIC HEALTH
-------------
/health/live
/health/ready

CATATAN
-------
Patch juga memperbaiki route Introducing:
/ -> Introducing
/home -> Beranda utama

SOURCE HYGIENE
--------------
Periksa artefak terminal:
powershell -ExecutionPolicy Bypass -File .\scripts\source-hygiene-check.ps1

Hapus artefak yang namanya diawali "hell -ExecutionPolicy Bypass":
Get-ChildItem -LiteralPath . -File | Where-Object { $_.Name -like 'hell -ExecutionPolicy Bypass*' } | Remove-Item -Force
