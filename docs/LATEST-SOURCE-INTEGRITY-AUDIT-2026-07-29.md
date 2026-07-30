# Latest Source Integrity Audit — 29 July 2026

## Baseline

- source utama: `125d3cda-8516-4bf9-a368-f6aca8c6764d.zip`;
- Public Experience Maximal v3.1;
- Introducing Thunder Fix v3.2;
- Fase 4B rebased.

## Hasil statis

- 263 file PHP pada `app/` lulus `php -l`;
- 8 file JavaScript publik/admin lulus `node --check`;
- 155 referensi route memiliki controller dan method;
- 173 referensi view ditemukan;
- 48 referensi aset statis ditemukan;
- tidak ada conflict marker Git;
- tidak ditemukan hardcoded secret pada app, scripts, deploy, dan docs.

## Integrasi paket publik

- empat file v3.1 identik dengan source utama;
- `public-introducing.js` v3.1 telah digantikan secara benar oleh v3.2;
- ketiga file Thunder Fix v3.2 identik dengan source utama.

## Temuan yang diperbaiki Fase 4C

Source telah memiliki `IntroducingController`, view, CSS, JavaScript,
dan migration, tetapi route masih menunjuk `/` ke `PublicController` dan
belum menyediakan `/home`. Fase 4C memperbaikinya menjadi:

```text
/      → IntroducingController::index
/home  → PublicController::index
```

## Hygiene repository

Terdapat satu file artefak terminal bernama awal
`hell -ExecutionPolicy Bypass`. File tersebut bukan source aplikasi dan
harus dihapus.

Banyak file terlihat berubah karena konversi LF/CRLF. Fase 4C menambahkan
`.gitattributes` untuk mencegah noise serupa pada perubahan berikutnya.

## Batas audit

Audit ini bersifat statis. Browser nyata, MySQL runtime, upload, backup,
scheduler, dan migration tetap harus diuji pada XAMPP pengguna.
