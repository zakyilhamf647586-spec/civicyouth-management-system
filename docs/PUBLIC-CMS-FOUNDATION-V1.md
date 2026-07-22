# GARDA 01 — Public CMS Foundation V1

## Tujuan

Fondasi CMS halaman publik memungkinkan pengurus mengubah konten
Beranda, Profil, dan Kontak tanpa mengedit source code.

Alur perubahan:

```text
Edit Draft
→ Preview Draft
→ Publikasikan
→ Website Publik Diperbarui
```

Menyimpan draft tidak langsung mengubah website publik.

## Tabel baru

### public_pages

Menyimpan:

- identitas halaman;
- route publik;
- judul SEO draft dan published;
- meta description draft dan published;
- status perubahan draft;
- catatan revisi;
- editor dan publisher;
- waktu publish.

### public_page_sections

Menyimpan:

- section halaman;
- urutan section;
- konten JSON draft;
- konten JSON published;
- status tampil draft dan published.

## Halaman awal

- Beranda (`/`)
- Profil (`/profil`)
- Kontak (`/kontak`)

Migration menyalin konten awal sesuai tampilan source saat ini ke
versi draft dan published. Karena itu, website tidak berubah secara
visual setelah migration.

## Route admin

```text
GET  /website/pages
GET  /website/pages/edit/{pageKey}
POST /website/pages/update/{pageKey}
POST /website/pages/publish/{pageKey}
POST /website/pages/restore/{pageKey}
GET  /website/pages/preview/{pageKey}
```

## Hak akses

### Admin
Seluruh akses melalui wildcard.

### Ketua
Seluruh akses CMS halaman publik.

### Sekretaris
- melihat;
- mengubah draft;
- melihat preview.

Sekretaris tidak dapat mempublikasikan.

## Preview

Preview memakai query:

```text
?cms_preview=1
```

Draft hanya ditampilkan ketika:

- pengguna login;
- mempunyai permission `website.pages.preview`.

Pengunjung umum selalu menerima versi published.

Preview juga memakai `noindex`.

## Fallback

View publik tetap mempunyai teks fallback. Bila:

- migration belum dijalankan;
- tabel CMS tidak tersedia;
- record CMS tidak tersedia;
- field kosong;

website tetap menampilkan konten lama yang aman.

## Scope fase berikutnya

Fondasi ini belum mencakup:

- Navigation Manager;
- Footer Manager lengkap;
- Media Library;
- metadata Program dan Kegiatan;
- revision history lengkap;
- rollback ke banyak versi;
- approval multi-reviewer.

Fitur tersebut dikembangkan bertahap setelah fondasi ini stabil.
