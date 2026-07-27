GARDA 01 — PUBLIC PAGE REVISION HISTORY V1
=============================================

PRASYARAT
---------
- Fase 1 Runtime Stabilization
- Seluruh Fase 2
- Fase 3A Public Page Review Workflow

FITUR
-----
- Snapshot versi halaman.
- Riwayat versi per halaman.
- Detail isi snapshot.
- Perbandingan dengan draft aktif.
- Pemulihan aman sebagai draft baru.
- Snapshot otomatis pada review dan publish.

MIGRATION
---------
app/Database/Migrations/2026-07-23-180000_CreatePublicPageRevisions.php

Jalankan:
php spark migrate

SETELAH EKSTRAK
---------------
php spark cache:clear

Restart server dan tekan Ctrl + F5.

TES
----
/website/pages
/website/pages/revisions/home
/website/pages/revisions/profile
/website/pages/revisions/contact
