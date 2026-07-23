# GARDA 01 — Fase 2B: Kelola Beranda Lengkap

## Tujuan

Memperluas fondasi CMS publik agar seluruh teks utama Beranda dapat
 dikelola sebagai draft, dipreview, lalu dipublikasikan tanpa mengubah
struktur desain publik.

## Section yang dapat dikelola

- Hero dan tombol;
- label kartu kegiatan unggulan;
- statistik organisasi;
- nilai Guyub, Bergerak, Berdampak;
- pengantar dan jumlah kartu Program;
- teks Cerita Dampak;
- pengantar dan jumlah Kegiatan Terbaru;
- ajakan dan daftar bidang kolaborasi.

## Data dinamis

Angka statistik, daftar program, kegiatan unggulan, cerita dampak,
dan kegiatan terbaru tetap bersumber dari database Portal. CMS hanya
mengelola teks pendamping, visibilitas section, tautan, dan batas item.

## Workflow

```text
Simpan Draft → Preview Draft → Publikasikan
```

Konten publik tidak berubah sebelum tombol Publikasikan ditekan.

## Migration

```text
2026-07-22-130000_ExpandHomePageCms.php
```

Migration menambah section yang belum tersedia dan menambahkan field
baru pada JSON section lama tanpa menimpa nilai hasil edit sebelumnya.
