# GARDA 01 — Production Deployment Runbook V1

## Prinsip

- jangan menimpa source aktif secara acak;
- `.env` production dibuat langsung di server;
- document root hanya menunjuk ke `public`;
- backup dibuat sebelum migration;
- go-live hanya setelah smoke test lulus.

## 1. Preflight lokal

```powershell
git status
php spark production:check
powershell -ExecutionPolicy Bypass `
    -File .\scripts\production-preflight.ps1
```

## 2. Package

```powershell
powershell -ExecutionPolicy Bypass `
    -File .\scripts\production-package.ps1
```

Tambahkan `-IncludeVendor` bila hosting tidak menyediakan Composer.

## 3. Backup server lama

Simpan database, source, `.env`, dan upload pengguna di lokasi privat.

## 4. Upload ke release baru

Contoh:

```text
/var/www/garda01/releases/2026.07.1/
```

## 5. Dependency

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
```

## 6. Environment

Salin `.env.production.example` menjadi `.env`, lalu isi domain,
database, encryption key, release, dan commit.

## 7. Permission

Pastikan `writable/` dan `public/uploads/` writable oleh web server.
Gunakan 755/775. Hindari 777.

## 8. Database

```bash
php spark migrate:status
php spark migrate
php spark production:check
```

Jangan memakai `migrate:refresh` pada production.

## 9. Cache

```bash
php spark cache:clear
```

## 10. Smoke test

Periksa website publik, login, dashboard, data organisasi, upload,
impor/ekspor, CMS, review, audit, sitemap, robots.txt, dan mobile.

## 11. Rollback

Kembalikan release sebelumnya. Pulihkan database hanya bila memang
diperlukan dan backup telah diverifikasi.
