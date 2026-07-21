# Publikasi Sosial & Canva — GARDA 01

Modul **Publikasi Sosial** menjadikan Portal Web sebagai pusat kendali pekerjaan media, sedangkan Canva tetap menjadi ruang desain yang dapat diedit oleh tim.

Portal menyimpan brief, sumber kegiatan, pilar program, PIC, reviewer, status, jadwal, aset, dan tautan hasil publikasi. Canva menyimpan desain visualnya. Master Canva tidak digunakan langsung: setiap publikasi harus memakai hasil duplikasi agar master tetap bersih.

## Aktivasi

Setelah kode diperbarui, jalankan:

```bash
php spark migrate
```

Masuk ke Portal, lalu buka **Website & Publikasi → Publikasi Sosial** atau akses `/publications`.

## Alur Operasional

1. Buat brief baru di **Publikasi Sosial**.
2. Pilih pilar GARDA 01, sumber kegiatan, kategori, dan format publikasi.
3. Tentukan hook cover, tujuan, audiens, CTA, PIC, reviewer, dan rencana tayang.
4. Buka master Canva dari detail publikasi, lalu buat salinan desain.
5. Ganti seluruh placeholder pada salinan, kemudian tempel tautan desain kerja ke Portal.
6. Lengkapi caption secara manual atau gunakan **AI Content Studio** pada record yang sama.
7. Jalankan alur status: **Brief → Draft → Desain → Review → Disetujui → Dijadwalkan → Dipublikasikan → Diarsipkan**.
8. Setelah tayang, simpan tautan Instagram sebelum mengubah status menjadi **Dipublikasikan**.

Status **Perlu Revisi** dapat dipilih dari tahap review, approved, atau scheduled. Sistem akan menolak status **Dijadwalkan** jika waktu tayang belum diisi dan menolak status **Dipublikasikan** jika tautan Instagram belum dicatat.

## Aturan Cover

Untuk carousel, halaman pertama berfungsi sebagai cover pemantik. Cover harus membuat orang berhenti dan ingin menggeser, tetapi tetap mewakili isi. Gunakan satu gagasan pendek, misalnya “Bukan hanya tentang menang.” Inti informasi dan bukti kegiatan diteruskan pada halaman berikutnya.

Cover tidak wajib untuk semua konten. Postingan ucapan tunggal, pengumuman mendesak, atau poster yang harus langsung terbaca boleh menampilkan informasi utama pada halaman pertama.

## Katalog Master Canva

| Kode | Kegunaan | Ukuran |
|---|---|---|
| `COVER-00` | Library cover dan hook carousel | 1080 × 1350 |
| `DOC-01A` | Carousel dokumentasi kegiatan | 1080 × 1350 |
| `DOC-01B` | Varian visual tujuh pilar | 1080 × 1350 |
| `INFO-01F` | Agenda dan pengumuman feed | 1080 × 1350 |
| `INFO-01S` | Agenda dan pengumuman story | 1080 × 1920 |
| `GREET-01F` | Ucapan feed | 1080 × 1350 |
| `GREET-01S` | Ucapan story | 1080 × 1920 |
| `REELS-01` | Reels dan konten vertikal | 1080 × 1920 |
| `STORY-01` | Story interaktif | 1080 × 1920 |
| `REPORT-01` | Laporan dan transparansi | 1080 × 1350 |
| `RECRUIT-01F` | Rekrutmen feed | 1080 × 1350 |
| `RECRUIT-01S` | Rekrutmen story | 1080 × 1920 |

Tautan edit dan Canva Design ID master dikelola terpusat dalam `app/Config/SocialMedia.php`. Seluruh 12 master telah diverifikasi dapat ditemukan pada akun Canva, dengan jumlah halaman sesuai katalog. Perubahan tautan cukup dilakukan di file tersebut agar seluruh halaman Portal ikut diperbarui.

## Pembagian Tanggung Jawab

- **PIC:** menyiapkan brief, naskah, aset, dan desain kerja.
- **Reviewer:** memeriksa fakta, nama, tanggal, logo, ejaan, dan kesesuaian dengan pilar.
- **Admin media:** menjadwalkan, memublikasikan, menyimpan tautan Instagram, dan mengarsipkan record.
- **Portal:** menjadi sumber data dan audit trail.
- **Canva:** menjadi editor visual dan sumber file ekspor.

## Checklist Sebelum Tayang

- Hook cover sesuai dengan isi dan tidak menipu.
- Nama kegiatan, lokasi, tanggal, dan pihak yang disebut sudah benar.
- Foto mendapat izin penggunaan dan tidak menampilkan data pribadi sensitif.
- Logo, warna, margin aman, dan urutan halaman konsisten.
- Caption, hashtag, mention, CTA, serta alt text telah diperiksa.
- Reviewer sudah menyetujui versi final.
- Tautan desain kerja Canva tersimpan di Portal.


## Hak Akses Portal

- **Admin:** seluruh alur publikasi dan manajemen akun.
- **Ketua:** seluruh alur publikasi, termasuk review, approval, penjadwalan, tayang, dan arsip.
- **Sekretaris:** membuat dan mengedit brief, mengelola aset, mengerjakan desain, serta mengirim konten untuk review. Tidak dapat menyetujui atau menandai konten tayang.
- **Bendahara dan Pengurus:** akses baca untuk koordinasi internal.

Perlindungan diterapkan pada route, controller, dan tombol antarmuka. Mengubah HTML atau mengirim request manual tidak dapat melewati izin status.
