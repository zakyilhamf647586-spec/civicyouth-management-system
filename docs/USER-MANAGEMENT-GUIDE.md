# GARDA 01 — Manajemen Akun Pengguna V1

## Cakupan

- Daftar dan filter akun
- Membuat akun pengurus
- Mengubah nama, email, peran, status, dan kata sandi
- Reset kata sandi
- Aktivasi/nonaktivasi akun
- Perlindungan akun yang sedang digunakan
- Perlindungan minimal satu Admin aktif
- Tanpa penghapusan akun permanen

## Hak akses

Hanya role **Admin** yang menerima permission `users.*` melalui wildcard `*`.

## Catatan keamanan

- Kata sandi disimpan menggunakan `password_hash()`.
- Kata sandi lama tidak pernah ditampilkan.
- Akun sendiri tidak dapat dinonaktifkan atau diturunkan perannya.
- Admin aktif terakhir tidak dapat dinonaktifkan atau diganti perannya.
- Perubahan role/status pengguna lain berlaku pada login berikutnya atau
  setelah pemeriksaan ulang session oleh AuthFilter.

## Tidak membutuhkan migration

Tabel `users` dan `roles` yang sudah tersedia digunakan apa adanya.
