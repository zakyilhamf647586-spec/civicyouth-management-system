# GARDA 01 — Publication UI Polish V1

## Audit singkat

Tampilan sebelumnya sudah memiliki struktur informasi yang benar, tetapi beberapa
komponen masih terasa seperti CRUD generik:

- tombol utama terlalu padat dan terlalu dominan;
- card kegiatan belum mempunyai hirarki isi yang kuat;
- alur lima langkah terlalu datar;
- statistik belum terasa sebagai satu sistem komponen;
- header tabel terlalu gelap;
- isi tabel tersebar tanpa kelompok visual;
- aksi dan tautan Canva belum mempunyai hirarki yang konsisten.

## File yang diubah

- `app/Views/publications/_assets.php`
- `app/Views/publications/index.php`

## File baru

- `public/assets/css/admin-publications-polish.css`

Tidak ada controller, model, route, query, migration, atau JavaScript yang diubah.

## Implementasi

- Sistem tombol primary, secondary, dan tertiary.
- Card kegiatan dengan badge, tanggal, metadata, ringkasan dua baris, hover,
  dan tombol refined.
- Step cards dengan nomor yang lebih halus dan rhythm lebih konsisten.
- Statistik seragam dengan soft tint.
- Tabel menjadi worklist dengan header slate, cell grouping, link Canva ringan,
  tombol compact, dan row hover.
- Tabel berubah menjadi task cards di layar mobile.
- Focus-visible dan empty state tetap jelas.

## Batasan

Patch hanya memoles halaman `/publications`. Halaman kalender, analitik, audit,
deadline, form, sidebar, dan website publik tidak diubah.
