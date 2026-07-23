# Website Navigation Manager V1

## Scope

Fase 2E memindahkan navigasi navbar dan footer dari source code ke
pengelolaan terstruktur di Portal.

## Menu yang dikelola

- Navigasi Utama atau header;
- Navigasi Footer.

## Kemampuan

- mengubah label;
- mengubah URL;
- mengatur urutan;
- menambah atau menghapus item;
- mengaktifkan atau menonaktifkan item;
- membuka tautan pada tab baru;
- memilih gaya tombol Portal pada header;
- mengatur penanda halaman aktif;
- menyimpan draft;
- preview;
- publish;
- restore versi publik.

## Alur aman

```text
Edit Menu
→ Simpan Draft
→ Preview Navigasi
→ Publikasikan
```

## Fallback

Apabila tabel belum tersedia atau database gagal dibaca, navbar dan
footer memakai susunan default yang identik dengan versi sebelumnya.

## URL admin

```text
/website/navigation
/website/navigation/edit/header
/website/navigation/edit/footer
```

## Preview

Preview memakai parameter:

```text
?nav_preview=1
```

Parameter dipertahankan pada tautan internal selama preview agar
pengurus dapat memeriksa seluruh halaman.

## Hak akses

- Admin dan Ketua: lihat, edit, preview, publish.
- Sekretaris: lihat, edit, preview.
- Bendahara dan Pengurus: tidak memperoleh akses.
