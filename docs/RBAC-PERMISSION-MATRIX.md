# GARDA 01 Portal — Matriks Hak Akses V1

| Modul | Admin | Ketua | Sekretaris | Bendahara | Pengurus |
|---|---|---|---|---|---|
| Dashboard | Penuh | Lihat | Lihat | Lihat | Lihat |
| Anggota | Penuh | Penuh | Tambah/Ubah/Import/Export | Lihat | Lihat |
| Struktur | Penuh | Penuh | Lihat/Tambah/Ubah | Lihat | Lihat |
| Rapat | Penuh | Penuh | Penuh | Lihat | Lihat |
| Absensi | Penuh | Penuh | Penuh | Lihat/Rekap | Lihat |
| Kas | Penuh | Lihat/Export | Tidak | Penuh | Tidak |
| Kegiatan | Penuh | Penuh termasuk publish | Buat/Ubah/Review, tanpa publish | Lihat | Lihat |
| Program | Penuh | Penuh | Buat/Ubah, tanpa publish | Lihat | Lihat |
| AI Content Studio | Penuh | Penuh | Penuh | Tidak | Tidak |
| Pesan Masuk | Penuh | Penuh | Penuh | Tidak | Tidak |
| Laporan | Penuh | Penuh | Anggota/Rapat | Kas | Tidak |
| Pengaturan Website | Penuh | Penuh | Tidak | Tidak | Tidak |

Catatan:
- Role yang tidak dikenal ditolak secara default.
- Admin memakai wildcard `*`.
- Route dilindungi server-side; menyembunyikan menu bukan satu-satunya perlindungan.
- Aksi tombol pada setiap view akan dirapikan pada fase RBAC berikutnya.
