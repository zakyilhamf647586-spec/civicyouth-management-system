# Deadline Produksi Publikasi GARDA 01

## Tujuan

Mendeteksi pekerjaan media yang terlambat, segera jatuh tempo,
belum mempunyai target tayang, atau terhambat oleh data produksi
yang belum lengkap.

## Dasar perhitungan

Semua deadline dihitung mundur dari `scheduled_at` atau
Rencana Tayang.

| Status saat ini | Target berikutnya | Deadline |
|---|---|---:|
| Brief | Selesaikan draft konten | T-6 hari |
| Draft Konten | Mulai dan lengkapi desain | T-4 hari |
| Desain | Kirim untuk review | T-2 hari |
| Menunggu Review | Selesaikan review | T-1 hari |
| Perlu Revisi | Selesaikan revisi | T-18 jam |
| Disetujui | Finalisasi jadwal dan aset | T-12 jam |
| Dijadwalkan | Publikasikan konten | Waktu tayang |

Jendela peringatan default adalah 48 jam dan dapat diubah pada:

```text
app/Config/SocialMedia.php
```

## Kondisi

- **Terlambat:** deadline tahap telah lewat.
- **Segera Jatuh Tempo:** deadline berada dalam jendela peringatan.
- **Belum Dijadwalkan:** Rencana Tayang kosong.
- **Sesuai Jalur:** deadline masih di luar jendela peringatan.

## Deteksi hambatan

Sistem memeriksa:

- PIC;
- tautan desain kerja Canva;
- reviewer;
- aset dokumentasi;
- caption;
- rencana tayang.

Pemeriksaan menyesuaikan posisi workflow.

## URL

```text
/publications/deadlines
```

Ringkasan kritis juga ditampilkan pada:

```text
/publications
```

## Hak akses

- Admin dan Ketua: melalui wildcard publikasi.
- Sekretaris: `publications.deadlines.view`.
- Bendahara dan Pengurus: tidak melihat pusat deadline.

## Database

Tidak membutuhkan migration.
