# Kalender Konten & Brief Otomatis GARDA 01

## Kalender konten bulanan

URL:

```text
/publications/calendar
```

Tanggal kalender dipilih berurutan dari waktu tayang, jadwal,
tanggal kegiatan, lalu tanggal pembuatan record.

Fitur:

- navigasi bulan;
- pemilih periode;
- ringkasan produksi;
- kalender desktop;
- agenda khusus mobile;
- daftar konten aktif tanpa jadwal.

## Brief otomatis dari Data Kegiatan

URL:

```text
/publications/create/activity/{activity_id}
```

Sistem mengisi pilar, kegiatan, kategori, format, master Canva,
judul, tanggal, lokasi, ringkasan, hook, tujuan, audiens, CTA,
PIC, reviewer, caption, hashtag, alt text, dan catatan.

Kegiatan selesai memakai `DOC-01A`.
Kegiatan direncanakan memakai `INFO-01F`.

Brief tetap harus diperiksa manusia sebelum disimpan.

## Database

Tidak membutuhkan migration.
