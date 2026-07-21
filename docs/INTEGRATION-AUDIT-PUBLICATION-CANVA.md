# Analisis Integrasi Paket Publication Canva

## Temuan utama

Paket unggahan adalah **snapshot proyek penuh**, bukan patch terisolasi. Di
dalamnya terdapat versi lama `Routes.php`, `layouts/main.php`, autentikasi,
dashboard, activity workflow, dan model pengguna yang belum membawa fondasi
RBAC serta Manajemen Akun terbaru.

Mengekstrak paket tersebut langsung ke proyek aktif berisiko:

1. mengembalikan seluruh route internal menjadi `auth` tanpa permission;
2. menghilangkan menu Manajemen Akun dan halaman 403;
3. menimpa dashboard review queue dan responsive admin shell;
4. menghilangkan proteksi `workflow_action` kegiatan;
5. mengembalikan sidebar serta tabel ke versi lama.

## Keputusan eksekusi

Fitur Publikasi Sosial dipisahkan dan di-merge ke struktur terkini. File lama
yang tidak berhubungan tidak ikut dibawa. Modul baru tetap menggunakan tabel
`content_posts` dan `content_assets`, sehingga AI Content Studio dan Publikasi
Sosial mengolah record yang sama.

## Peningkatan terhadap paket sumber

- route dilindungi permission RBAC;
- transisi status divalidasi lagi pada controller;
- tombol menyesuaikan role;
- URL desain kerja harus berasal dari Canva `/design/` hasil duplikasi;
- URL tayang harus berasal dari Instagram;
- migration defensif dan tidak menambahkan kolom/index yang sudah tersedia;
- Dashboard quick actions menyesuaikan permission;
- tidak membutuhkan dependency Composer baru.
