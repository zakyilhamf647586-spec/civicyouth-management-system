GARDA 01 — PUBLIC/INTERNAL SEPARATION V4.9
===========================================

BASELINE
- Source terbaru setelah V4.8.1 RBAC Permission Hardening.
- Jangan pasang pada source yang lebih lama tanpa audit/rebase.

ISI
- Menghapus akses Portal/Login dari permukaan publik.
- Mempertahankan /login dan /en/login untuk akses langsung.
- Memblokir route internal dari Navigation Manager.
- Membersihkan item internal yang tersimpan melalui migration.
- Memperbaiki microcopy dan kontras form Kontak mode Midnight.
- Menambahkan command public:boundary:audit.

PEMASANGAN
1. Buat dan verifikasi backup.
2. Ekstrak ZIP ke root project.
3. Pilih Replace files in destination.
4. Jalankan:

   php spark migrate:status
   php spark migrate
   php spark cache:clear
   php spark public:boundary:audit --strict
   php spark auth:permissions:audit --strict

5. Restart php spark serve dan lakukan Ctrl+F5.
6. Uji website publik, /login, /en/login, Navigation Manager,
   Backup, Operations, Readiness, dan health endpoints.

MIGRATION BARU
2026-08-03-070000_EnforcePublicInternalSeparation.php

CATATAN
- Tidak mengubah app/Config/Routes.php.
- Tidak mengubah app/Config/Permissions.php.
- Tidak menghapus route login.
- Tidak membuat aplikasi atau database kedua.
- Subdomain portal.garda01.org disiapkan pada tahap deployment.

GIT
Gunakan selective staging dari panduan jawaban. Jangan gunakan git add .
