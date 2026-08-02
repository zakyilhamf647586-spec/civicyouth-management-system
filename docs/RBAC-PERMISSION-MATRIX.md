# GARDA 01 Portal — Matriks Hak Akses V4.8

Dokumen ini menggambarkan kontrak akses yang berlaku pada source terbaru.
Perlindungan utama tetap berada pada route dan `PermissionFilter`; guard di
view menyelaraskan tampilan tombol dengan kewenangan server-side.

| Modul | Admin | Ketua | Sekretaris | Bendahara | Pengurus |
|---|---|---|---|---|---|
| Dashboard | Penuh | Lihat | Lihat | Lihat | Lihat |
| Manajemen akun | Penuh | Tidak | Tidak | Tidak | Tidak |
| Anggota | Penuh | Penuh | Lihat/Tambah/Ubah/Import/Export | Lihat | Lihat |
| Struktur | Penuh | Penuh | Lihat/Tambah/Ubah | Lihat | Lihat |
| Rapat | Penuh | Penuh | Penuh | Lihat | Lihat |
| Absensi | Penuh | Penuh | Penuh | Lihat/Rekap | Lihat |
| Kas | Penuh | Lihat/Export | Tidak | Penuh | Tidak |
| Kegiatan | Penuh | Penuh | Buat/Ubah/Hapus/Review/Galeri, tanpa publish/archive | Lihat | Lihat |
| Program | Penuh | Penuh | Lihat/Tambah/Ubah, tanpa publish/archive | Lihat | Lihat |
| Publikasi sosial | Penuh | Penuh | Produksi/workflow/analitik, tanpa approve/publish/archive | Lihat/analitik | Lihat/analitik |
| AI Content Studio | Penuh | Penuh | Penuh | Tidak | Tidak |
| Pesan masuk | Penuh | Penuh | Penuh | Tidak | Tidak |
| Laporan | Penuh | Semua | Anggota/Rapat | Kas | Tidak |
| CMS halaman publik | Penuh | Penuh | Draft/preview/review submission/revisions view | Tidak | Tidak |
| Navigation Manager | Penuh | Penuh | Draft/preview | Tidak | Tidak |
| SEO | Penuh | Penuh | Lihat | Tidak | Tidak |
| Audit CMS | Penuh | Penuh | Tidak | Tidak | Tidak |
| Production readiness | Penuh | Penuh | Tidak | Tidak | Tidak |
| Backup | Penuh | Lihat/Buat/Verifikasi | Tidak | Tidak | Tidak |
| Operasional sistem | Penuh | Penuh | Tidak | Tidak | Tidak |
| Pengaturan website | Penuh | Penuh | Tidak | Tidak | Tidak |

## Kontrak keamanan

- Role yang tidak dikenal ditolak secara default.
- Admin memakai wildcard global `*`.
- Role non-Admin tidak boleh memakai wildcard global.
- Manajemen akun dan retensi/penghapusan backup tetap Admin-only.
- Route internal memakai kombinasi filter `auth` dan `permission:*`.
- Tombol mutasi disembunyikan bila permission tidak tersedia.
- Data ringkasan laporan hanya dihitung dan ditampilkan bila akun memiliki
  permission laporan terkait.
- Transisi publikasi memakai pemeriksaan permission khusus untuk review,
  approval, publish, dan archive di dalam controller.

## Pemeriksaan otomatis

Jalankan:

```powershell
php spark auth:permissions:audit
```

Mode ketat:

```powershell
php spark auth:permissions:audit --strict
```

Output JSON:

```powershell
php spark auth:permissions:audit --json
```

Perintah tersebut memeriksa:

- daftar role standar;
- wildcard Admin dan least privilege role lain;
- permission pada route;
- permission yang dipakai view;
- label permission;
- kontrak guard aksi pada view utama;
- permission berisiko tinggi yang harus tetap Admin-only.
