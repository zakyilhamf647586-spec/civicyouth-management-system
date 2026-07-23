# Public CMS Contact, Footer & Location Complete V1

## Scope

Fase 2D menyelesaikan pengelolaan halaman Kontak serta microcopy
Footer dan lokasi organisasi.

## Halaman Kontak

Section yang dapat dikelola:

- Hero dan kartu identitas;
- kanal kontak resmi;
- pengantar form;
- ruang kolaborasi;
- lokasi organisasi;
- catatan layanan;
- SEO title dan meta description.

Email, WhatsApp, alamat, jam respons, dan URL Google Maps tetap
bersumber dari Pengaturan Website agar tidak terjadi duplikasi data.

## Footer

Pengaturan baru:

- judul kolom navigasi;
- judul kolom lokasi;
- judul kolom kontak;
- pengantar kontak;
- label kartu maps;
- teks aksi maps.

## Alur CMS

```text
Simpan Draft
→ Preview Draft
→ Publikasikan
```

## URL

```text
/website/pages/edit/contact
/website/pages/preview/contact
/settings/website
/kontak
```

## Database

Migration menambahkan field CMS dan record site settings secara
defensif tanpa menimpa nilai yang sudah ada.
