GARDA 01 — AUTOMATED BACKUP & DISASTER RECOVERY V1
====================================================

PRASYARAT
---------
Fase 1 sampai Fase 4A sudah terpasang.

DATABASE
--------
Tidak ada migration baru.

SETELAH EKSTRAK
---------------
php spark cache:clear

COMMAND
-------
php spark backup:create --tag manual
php spark backup:list
php spark backup:verify --file ARCHIVE.zip
php spark backup:prune --dry-run
php spark backup:prune

RESTORE
-------
php spark backup:restore --file ARCHIVE.zip --yes --maintenance-confirmed YES

PORTAL
------
/system/backups

SOURCE-SPECIFIC SAFETY AUDIT
----------------------------
This package was revalidated against the latest uploaded source on 2026-07-28.
It has zero path overlap with the current Introducing and public-premium files.
Checkpoint the current public work before extraction.
