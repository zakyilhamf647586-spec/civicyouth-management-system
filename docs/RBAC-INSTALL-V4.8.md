# Instalasi GARDA 01 RBAC V4.8

## Baseline

Patch ini dibuat langsung di atas source:

- branch: `feature/social-publication-canva`
- commit: `bb17d84`
- checkpoint: `checkpoint-account-access-hardening-v4.7-2026-08-02`

## Dampak

- Tidak ada migration baru.
- Tidak mengubah route.
- Tidak mengubah grant permission.
- Tidak mengubah website publik.
- Menyelaraskan tombol Portal dengan permission route.
- Membatasi ringkasan Pusat Laporan berdasarkan izin spesifik.
- Menambahkan audit otomatis `auth:permissions:audit`.

## Pemasangan

1. Buat dan verifikasi backup terbaru.
2. Ekstrak paket ke root project:
   `C:\xampp\htdocs\civicyouth-management-system`
3. Pilih **Replace files in destination**.
4. Bersihkan cache:

```powershell
php spark cache:clear
```

5. Jalankan pemeriksaan:

```powershell
php spark auth:permissions:audit --strict
```

6. Tidak perlu menjalankan `php spark migrate` untuk patch ini.

## Smoke test role

- Admin: seluruh aksi tersedia.
- Ketua: tidak melihat Manajemen Akun atau prune backup.
- Sekretaris: tidak melihat kas, publish/archive program, atau Pengaturan Website.
- Bendahara: Kas penuh; modul lain read-only; Pusat Laporan hanya menampilkan laporan kas.
- Pengurus: modul yang tersedia hanya menampilkan aksi read-only.

Akses langsung ke route tanpa permission harus menghasilkan HTTP 403.

## Selective staging

```powershell
git add app/Commands/AuthorizationAudit.php
git add app/Controllers/ReportController.php

git add app/Views/members/index.php
git add app/Views/structures/index.php
git add app/Views/meetings/index.php
git add app/Views/attendances/index.php
git add app/Views/attendances/recap.php
git add app/Views/cash/index.php
git add app/Views/programs/index.php
git add app/Views/content_studio/index.php
git add app/Views/contact_messages/show.php
git add app/Views/reports/index.php
git add app/Views/seo/index.php

git add docs/RBAC-PERMISSION-MATRIX.md
git add docs/RBAC-AUDIT-V4.8.md
git add docs/RBAC-INSTALL-V4.8.md
```

Periksa:

```powershell
git diff --cached --name-status
```

Commit:

```powershell
git commit -m "Harden role-aware portal actions and reports"
git push origin feature/social-publication-canva
```

Checkpoint:

```powershell
git tag -a checkpoint-rbac-permission-hardening-v4.8-2026-08-02 `
    -m "Role-aware portal actions and authorization audit"

git push origin checkpoint-rbac-permission-hardening-v4.8-2026-08-02
```
