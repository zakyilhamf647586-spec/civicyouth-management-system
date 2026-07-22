# Fase 1 — Audit Runtime dan Stabilisasi GARDA 01

## Tujuan

Fase ini menutup risiko teknis paling mendesak tanpa melakukan redesign website
publik atau mengubah fakta organisasi.

## Perubahan yang diterapkan

1. **Upload gambar terpusat dan lebih aman**
   - MIME diperiksa menggunakan `finfo`;
   - gambar dibaca dengan `getimagesize`;
   - batas dimensi dan jumlah piksel diterapkan;
   - JPG/PNG/WEBP didekode dan di-encode ulang;
   - nama file acak dengan ekstensi yang sesuai MIME;
   - metadata dan appended payload dibuang;
   - direktori upload dilindungi `.htaccess`.

2. **Upload import anggota diperketat**
   - validasi ekstensi, MIME, signature XLS/XLSX, dan struktur workbook XLSX.

3. **Noindex dan no-store portal internal**
   - meta robots pada admin dan login;
   - `X-Robots-Tag` pada route nonpublik;
   - respons internal memakai `Cache-Control: no-store`;
   - `robots.txt` membatasi crawler dari route internal.

4. **Security headers dasar**
   - `X-Content-Type-Options`;
   - `X-Frame-Options`;
   - `Referrer-Policy`;
   - `Permissions-Policy`;
   - HSTS hanya pada HTTPS production.

5. **Setting footer/lokasi yang hilang**
   - `contact_location_description`;
   - `contact_maps_url`;
   - tersedia melalui migration, seeder, controller, validasi, dan form dinamis.

6. **Sumber data Pengurus publik disamakan**
   - struktur harus aktif;
   - anggota yang terhubung harus aktif;
   - hitungan Beranda dan daftar Pengurus memakai query yang sama;
   - field `rt_scope` dan `description` diselaraskan dengan schema aktual;
   - CTA teknis menuju login diganti menjadi narasi publik menuju Kontak.

7. **Audit kualitas kegiatan non-destruktif**
   - URL: `/activities/quality`;
   - indikator Belum Lengkap, Perlu Diperiksa, dan Siap Dipublikasikan;
   - tidak mengubah isi data secara otomatis.

## Migration

`2026-07-22-070000_AddMissingLocationSiteSettings.php`

Migration hanya menambahkan dua record setting bila belum tersedia. `down()` hanya
menghapus record yang memiliki marker migration Fase 1, sehingga setting lama yang
sudah ada tidak ikut terhapus.

## Prasyarat runtime

- PHP 8.2 atau kompatibel dengan versi project;
- ekstensi `fileinfo`, `gd`, `intl`, `mysqli`, dan `mbstring`;
- Apache `AllowOverride` aktif agar perlindungan `.htaccess` pada uploads berlaku;
- production menggunakan HTTPS.

## Batas Fase 1

Belum mengaktifkan CSP penuh karena beberapa halaman lama masih memakai script
inline. CSP akan dikerjakan setelah inventaris script pada Fase SEO/performa agar
tidak merusak portal.

Belum menambahkan field persetujuan tampil publik atau periode aktif pada struktur.
Untuk saat ini query publik memakai status struktur dan status anggota yang sudah
tersedia di schema.


## Pengujian

Hasil pengujian statis dan daftar pengujian runtime yang masih wajib dilakukan
tersedia pada `docs/PHASE1-TEST-REPORT.md`.

Gunakan:

```powershell
.\scripts\phase1-runtime-check.ps1
.\scripts\phase1-http-smoke.ps1
```
