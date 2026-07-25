# Public Page Review Workflow V1

## Scope

Fase 3A menambahkan workflow editorial formal pada CMS Beranda,
Profil, dan Kontak.

## Status

```text
Draft
Menunggu Review
Perlu Revisi
Disetujui
Terpublikasi
```

## Alur

```text
Edit dan Simpan Draft
→ Preview Draft
→ Kirim untuk Review
→ Minta Revisi atau Setujui
→ Publikasikan
```

## Aturan

- halaman harus mempunyai perubahan draft;
- Catatan Revisi editor wajib tersedia sebelum submit review;
- halaman Menunggu Review dikunci dari penyuntingan;
- reviewer dapat meminta revisi dengan catatan wajib;
- halaman Disetujui dikunci dari penyuntingan;
- publish hanya dapat dilakukan ketika status Disetujui;
- restore versi publik membatalkan draft dan proses review berjalan.

## Hak akses

- Admin: seluruh aksi;
- Ketua: seluruh aksi;
- Sekretaris: edit, preview, dan submit review;
- Bendahara dan Pengurus: tidak memperoleh akses CMS halaman.

## URL

```text
/website/pages
/website/pages/review
/website/pages/edit/{page}
/website/pages/preview/{page}
```

## Database

Migration menambahkan metadata review pada `public_pages` tanpa
mengubah konten section atau versi publik yang sedang tayang.
