# GARDA 01 — Audit Peran dan Izin Akses V4.8

## Tujuan

Menyelaraskan apa yang terlihat pada antarmuka Portal dengan permission yang
sudah ditegakkan pada route, sekaligus mencegah data ringkasan lintas peran
muncul pada Pusat Laporan.

## Temuan baseline V4.7

- Seluruh route internal utama sudah memakai `auth` dan `permission:*`.
- Role tidak dikenal menerima nol permission.
- Manajemen akun sudah Admin-only.
- Beberapa view lama masih menampilkan tombol tambah, edit, hapus, publish,
  archive, import, atau export kepada role read-only. Route tetap menolak,
  tetapi pengalaman pengguna tidak konsisten.
- Pusat Laporan menghitung dan menampilkan ringkasan kas, anggota, dan rapat
  kepada setiap role yang memiliki `reports.view`, walaupun role tersebut
  belum tentu memiliki report permission spesifik.
- Halaman SEO menampilkan tautan ke Pengaturan Website kepada Sekretaris yang
  hanya mempunyai akses lihat SEO.

## Perubahan V4.8

- Menambahkan guard tombol pada view Anggota, Struktur, Rapat, Absensi, Kas,
  Program, AI Content Studio, Pesan Masuk, Laporan, dan SEO.
- Menyembunyikan kolom Aksi bila tidak ada tindakan yang diizinkan.
- Menghitung ringkasan laporan secara kondisional berdasarkan permission.
- Menambahkan `php spark auth:permissions:audit`.
- Memutakhirkan matriks RBAC agar sesuai source aktual.

## Batas perubahan

Tidak mengubah:

- route publik;
- Introducing dan `/home`;
- Fase 4B backup;
- Fase 4C monitoring;
- struktur tabel database;
- permission grant yang sudah berlaku;
- desain website publik.

## Uji runtime yang disarankan

Uji minimal dengan akun:

1. Admin — seluruh menu dan aksi tersedia.
2. Ketua — tidak melihat Manajemen Akun atau prune backup.
3. Sekretaris — tidak melihat kas, publish program, atau pengaturan website.
4. Bendahara — Kas penuh, laporan kas, tetapi tidak melihat aksi mutasi modul lain.
5. Pengurus — hanya melihat modul read-only dan tidak melihat tombol mutasi.

Setiap akses langsung ke route tanpa izin harus menghasilkan HTTP 403.
