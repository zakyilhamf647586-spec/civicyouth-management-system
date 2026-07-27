# Public Page Revision History & Safe Rollback V1

## Scope

Fase 3B menambahkan version history pada CMS Beranda, Profil, dan
Kontak. Setiap versi menyimpan metadata SEO, status section, dan
seluruh isi section.

## Snapshot otomatis

Snapshot dibuat ketika:

- migration pertama kali dijalankan;
- draft dikirim untuk review;
- halaman dipublikasikan;
- versi lama dipulihkan sebagai draft.

## Jenis versi

```text
Snapshot Awal Publik
Snapshot Awal Draft
Dikirim untuk Review
Dipublikasikan
Pemulihan Versi
```

## Pemulihan aman

Rollback tidak langsung menimpa website publik.

```text
Pilih Versi Lama
→ Pulihkan sebagai Draft
→ Preview
→ Kirim untuk Review
→ Setujui
→ Publikasikan
```

## Perbandingan

Detail versi membandingkan snapshot dengan draft saat ini:

- judul SEO;
- meta description;
- section yang berubah;
- field yang berubah;
- status tampil atau sembunyi.

## Hak akses

- Admin dan Ketua: melihat serta memulihkan versi;
- Sekretaris: melihat riwayat versi;
- Bendahara dan Pengurus: tidak memperoleh akses.

## URL

```text
/website/pages/revisions/{page}
/website/pages/revisions/{page}/{revision}
/website/pages/revisions/{page}/{revision}/restore
```
