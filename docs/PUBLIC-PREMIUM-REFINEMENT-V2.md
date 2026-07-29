# GARDA 01 — Public Premium Refinement V2

## Tujuan

Peningkatan ini tidak menambah modul publik baru dan tidak mengubah alur CMS,
route, controller, model, maupun database. Implementasi hanya memperhalus
pengalaman website publik dengan tetap mengikuti struktur project yang sudah ada.

## Temuan utama sebelum perubahan

1. Pola visual antarlaman sudah konsisten, tetapi berulang sebagai kartu putih
   besar di atas latar ivory sehingga beberapa area terasa terlalu datar.
2. Halaman Profil memiliki ruang kosong berlebih karena grid menyamakan tinggi
   kartu yang jumlah kontennya berbeda.
3. Judul kartu `Identitas Resmi` menggunakan warna navy di atas kartu navy,
   sehingga kontrasnya sangat rendah.
4. Hierarki tombol publik belum sepenuhnya membedakan aksi pada latar gelap dan
   latar terang.
5. Navbar tidak merespons kedalaman scroll, sedangkan halaman publik cukup
   panjang.
6. Card, section heading, dan CTA sudah baik tetapi belum memiliki satu lapisan
   detail akhir yang menyatukan keseluruhan pengalaman.

## Perubahan

### Layout publik

- Menambahkan class `public-experience-v2` dan class halaman berdasarkan
  `activePage` pada body.
- Menambahkan skip link menuju konten utama.
- Memberi id `main-content` pada elemen main.
- Memuat stylesheet dan JavaScript refinement setelah stylesheet halaman agar
  berfungsi sebagai lapisan visual terakhir.

### Visual refinement

- Navbar menjadi lebih ringkas setelah halaman digulir.
- Hierarki tombol disesuaikan dengan konteks latar gelap/terang.
- Hero, card, statistik, pilar program, kegiatan, CTA, dan footer mendapat
  border, shadow, spacing, dan state interaksi yang lebih konsisten.
- Kicker diberi garis editorial kecil tanpa mengubah teks CMS.
- Halaman Profil tidak lagi memaksakan tinggi kartu yang sama.
- Kontras heading pada kartu Identitas Resmi diperbaiki.
- Daftar misi menggunakan penomoran visual yang lebih rapi.
- Kartu kegiatan memakai tinggi konten yang lebih konsisten.

### Interaksi

- Reveal animation ringan berbasis `IntersectionObserver`.
- Navbar scrolled state.
- Tombol kembali ke atas untuk halaman panjang.
- Seluruh motion menghormati `prefers-reduced-motion`.

## File yang berubah

- `app/Views/layouts/public.php`
- `public/assets/css/public-premium-v2.css`
- `public/assets/js/public-premium-v2.js`
- `docs/PUBLIC-PREMIUM-REFINEMENT-V2.md`

## Tidak berubah

- Route
- Controller
- Model
- Migration
- Seeder
- Struktur database
- Data CMS
- Portal admin
- Workflow publikasi

## Pengujian minimum

1. Buka `/`, `/profil`, `/program`, `/kegiatan`, `/pengurus`, dan `/kontak`.
2. Pastikan navbar mengecil setelah scroll dan kembali normal di bagian atas.
3. Pastikan skip link muncul ketika menerima fokus keyboard.
4. Pastikan halaman Profil tidak memiliki dead space besar pada kartu intro,
   visi, dan misi.
5. Pastikan seluruh tombol masih menuju URL yang sama.
6. Uji pada 360 px, 768 px, 1024 px, dan 1366 px.
7. Aktifkan reduced motion pada OS/browser dan pastikan seluruh konten langsung
   terlihat tanpa animasi.

## Rollback

Hapus dua asset berikut dan kembalikan perubahan pada layout publik:

- `public/assets/css/public-premium-v2.css`
- `public/assets/js/public-premium-v2.js`

Tidak ada rollback migration karena tidak ada perubahan database.
