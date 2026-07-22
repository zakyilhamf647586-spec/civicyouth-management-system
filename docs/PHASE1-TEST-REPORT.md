# Laporan Pengujian Fase 1 — GARDA 01

## Pengujian yang telah dilakukan pada paket patch

Pengujian ini dilakukan secara statis terhadap source upload terbaru yang
diberikan pengguna, lalu patch dioverlay ke source tersebut.

### Hasil

- 199 file PHP pada hasil overlay lulus `php -l`;
- 24 file PHP yang dikirim dalam patch lulus `php -l`;
- tidak ditemukan lagi `getRandomName()` atau `->move()` langsung pada seluruh
  controller upload setelah overlay;
- route `/activities/quality` ditemukan pada konfigurasi route;
- alias dan global filter security ditemukan pada konfigurasi filter;
- kedua setting lokasi ditemukan pada controller dan migration;
- tidak ditemukan nama method controller yang terduplikasi pada file yang
  diubah;
- seluruh file patch berhasil dikemas ke ZIP dan checksum SHA-256 dibuat.

## Pengujian yang wajib dilakukan pada komputer project

Paket source yang diunggah tidak menyertakan `spark`, `vendor`, `.env`, database,
atau server web aktif. Karena itu pengujian berikut belum dapat dibuktikan dari
lingkungan audit dan harus dijalankan pada project lokal:

1. status dan eksekusi migration;
2. query database nyata pada Beranda dan Pengurus;
3. upload JPG, PNG, WEBP, ICO, XLSX, XLS, dan CSV;
4. penolakan file palsu, file terlalu besar, dan gambar beresolusi ekstrem;
5. response header HTTP;
6. penerapan `.htaccess` Apache pada `public/uploads`;
7. tampilan responsive `/activities/quality`;
8. penyimpanan URL Maps dan deskripsi lokasi;
9. rollback migration;
10. Chrome, Edge, Firefox, dan ukuran layar yang diwajibkan.

Gunakan:

```powershell
.\scripts\phase1-runtime-check.ps1
.\scripts\phase1-http-smoke.ps1
```

Kemudian lakukan skenario manual yang tercantum pada dokumentasi instalasi.
