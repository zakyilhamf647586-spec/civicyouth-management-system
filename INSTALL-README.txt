GARDA 01 — PRODUCTION DEPLOYMENT READINESS V1
================================================

PRASYARAT
---------
Fase 1 sampai Fase 3D sudah terpasang.

DATABASE
--------
Tidak ada migration baru.

SETELAH EKSTRAK
---------------
php spark cache:clear

Restart server dan tekan Ctrl + F5.

TES
----
php spark production:check
php spark production:check --json
php spark production:check --strict

/system/readiness
/system/readiness/export
