# Audit Teknis CivicYouth / GARDA 01 Portal

Tanggal audit: 16 Juli 2026

## Ringkasan

Penyebab utama kerusakan navbar dan footer bukan database maupun helper pengaturan dinamis. Masalahnya adalah pergantian struktur HTML partial ke sistem class baru, sementara `app.css` masih menyimpan beberapa generasi CSS navbar/footer lama. Dua blok CSS tambahan kemudian menumpuk di bagian akhir file dan saling menimpa.

Versi perbaikan ini mengembalikan navbar dan footer ke sistem class premium yang sebelumnya sudah tersedia dan stabil (`public-brand`, `public-nav-links`, `garda-public-footer`, dan `garda-footer-*`), lalu memasukkan data dinamis tanpa mengganti fondasi visualnya.

## Perbaikan utama

1. Mengembalikan struktur navbar ke class premium asli dan mempertahankan data dinamis.
2. Mengembalikan footer ke grid premium empat kolom asli dan mempertahankan data dinamis.
3. Menghapus blok CSS tambalan `Dynamic Public Navbar and Footer Refinement` dan `FINAL LOCK` yang bertabrakan.
4. Menghapus satu generasi CSS navigasi lama yang sudah tidak digunakan.
5. Mengubah cache busting CSS dari `time()` menjadi `filemtime()` agar cache otomatis berubah hanya saat file CSS berubah.
6. Menyatukan halaman Pengurus ke `layouts/public`; sebelumnya halaman tersebut membuat dokumen HTML, navbar, dan footer sendiri.
7. Menambahkan status menu aktif yang benar pada halaman detail program, detail kegiatan, dan Pengurus.
8. Menghubungkan logo dinamis ke halaman publik, portal, login, dan laporan cetak.
9. Menghapus route `/` ganda yang mengarah ke Dashboard dan Beranda publik secara bersamaan.
10. Mengaktifkan proteksi CSRF global.
11. Mengubah operasi hapus dari GET menjadi POST dan menambahkan token CSRF pada form hapus.
12. Memperbaiki upload galeri agar database dan file di-rollback bersama jika salah satu upload gagal.
13. Menghapus seluruh file galeri fisik saat kegiatan dihapus agar tidak meninggalkan file yatim.
14. Menyamakan batas panjang judul dan lokasi kegiatan dengan skema database.
15. Memperkuat login dengan validasi, pesan error umum, regenerasi session ID, dan nama role.
16. Membuat pergantian logo/favicon/OG image lebih aman: file lama baru dihapus setelah data berhasil tersimpan.
17. Memperbaiki `.gitignore` yang sebelumnya memiliki baris `content_studio` rusak dan aturan upload yang berulang.
18. Menambahkan CSS spesifik kartu kegiatan untuk mengatasi rule generik `.public-activity-card div` yang memberi padding pada semua `div` turunan.

## Hasil validasi statis

- 165 file PHP: lulus `php -l` tanpa syntax error.
- CSS: 0 parse error, jumlah kurung kurawal seimbang.
- JavaScript portal: lulus `node --check`.
- Route yang menunjuk controller/method tidak tersedia: 0.
- Route method/path duplikat: 0.
- Form POST tanpa `csrf_field()`: 0.

## Batas pemeriksaan

ZIP review sengaja tidak menyertakan `.env`, `vendor`, dan database MySQL. Karena itu audit dapat memvalidasi struktur, sintaks, route, view, CSS, keamanan form, dan konsistensi controller secara statis, tetapi tidak dapat menjalankan integrasi database penuh di lingkungan audit. Pengujian runtime final harus dilakukan pada XAMPP pengguna dengan `.env`, `vendor`, dan database asli.

## Langkah pemasangan versi perbaikan

1. Backup folder proyek aktif.
2. Ekstrak ZIP versi perbaikan.
3. Jangan menimpa `.env` milik proyek aktif.
4. Folder `vendor` dari proyek aktif tetap dipertahankan atau jalankan `composer install`.
5. Jalankan:

```bash
php spark cache:clear
php spark serve
```

6. Tekan `Ctrl + F5` pada browser.

## Daftar pengujian runtime

Halaman publik:

- `/`
- `/profil`
- `/program`
- `/program/{slug}`
- `/kegiatan`
- `/kegiatan/{id}`
- `/pengurus`
- `/kontak`

Portal:

- `/login`
- `/dashboard`
- `/activities`
- `/activities/gallery/{id}`
- `/programs`
- `/messages`
- `/settings/website`

Pengujian penting:

- Navbar desktop horizontal dan menu aktif berwarna emas.
- Navbar mobile membuka drawer dan dapat ditutup dengan Escape.
- Footer desktop empat kolom, tablet dua kolom, mobile satu kolom.
- Mengubah nama, slogan, logo, favicon, email, WhatsApp, dan media sosial dari Pengaturan Website memperbarui tampilan publik.
- Operasi hapus hanya bekerja melalui tombol/form POST.
- Upload beberapa foto galeri tidak meninggalkan record/file setengah jadi ketika terjadi kegagalan.

## Roadmap setelah baseline stabil

1. Status publikasi draft–review–published untuk kegiatan.
2. Hak akses berbasis role.
3. Audit log perubahan.
4. Backup dan restore database.
5. Media Library terpadu.
6. Pengaturan Beranda Dinamis.
7. Optimasi deployment dan performance.
