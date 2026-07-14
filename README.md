# CivicYouth Management System

CivicYouth Management System adalah sistem informasi manajemen organisasi pemuda berbasis web yang dikembangkan menggunakan CodeIgniter 4 dan MySQL.

Project ini dibuat sebagai studi kasus nyata untuk membantu administrasi Karang Taruna RW 01 Kelurahan Randugarut dalam mengelola data anggota, struktur pengurus, agenda rapat, absensi rapat, kas organisasi, kegiatan, dan laporan.

Pada sisi portofolio, nama project ini adalah **CivicYouth Management System**.  
Pada sisi penggunaan aplikasi, sistem ini diterapkan untuk **Karang Taruna RW 01 Kelurahan Randugarut**.

---

## Studi Kasus

Aplikasi ini dikembangkan berdasarkan kebutuhan administrasi organisasi kepemudaan tingkat RW, khususnya:

**Karang Taruna RW 01 Kelurahan Randugarut**

Tujuan utama sistem ini adalah membantu pengurus organisasi agar proses administrasi menjadi lebih rapi, terdokumentasi, mudah dicari, dan lebih profesional.

---

## Tujuan Project

Project ini dibuat untuk:

- Membangun sistem administrasi organisasi pemuda berbasis web.
- Mengelola data anggota secara lebih tertib.
- Mencatat struktur kepengurusan organisasi.
- Mengelola agenda rapat dan hasil keputusan rapat.
- Mencatat absensi rapat.
- Mengelola pemasukan dan pengeluaran kas organisasi.
- Mendokumentasikan kegiatan organisasi.
- Menyediakan laporan yang dapat dicetak atau disimpan sebagai PDF.
- Menjadi portofolio pengembangan web berbasis studi kasus nyata.

---

## Fitur Utama

- Login admin
- Dashboard ringkasan organisasi
- Manajemen data anggota
- Manajemen struktur pengurus
- Manajemen agenda rapat
- Manajemen absensi rapat
- Manajemen kas organisasi
- Manajemen kegiatan
- Laporan data anggota
- Laporan kas organisasi
- Laporan agenda rapat
- Halaman laporan printable / save as PDF
- Branding visual Karang Taruna RW 01

---

## Teknologi yang Digunakan

- PHP
- CodeIgniter 4
- MySQL
- HTML
- CSS
- Git
- GitHub

---

## Modul Sistem

### 1. Dashboard

Dashboard digunakan untuk menampilkan ringkasan data utama organisasi, seperti total anggota, jumlah anggota aktif, dan saldo kas organisasi.

### 2. Data Anggota

Modul ini digunakan untuk mencatat dan mengelola data anggota Karang Taruna, meliputi:

- Nama lengkap
- RT
- Jenis kelamin
- Tanggal lahir
- Nomor HP
- Alamat
- Jabatan atau posisi
- Status keanggotaan

### 3. Struktur Pengurus

Modul ini digunakan untuk mengelola susunan kepengurusan organisasi, meliputi:

- Jabatan
- Nama pengurus
- Bidang atau seksi
- Lingkup RT
- Periode
- Deskripsi tugas
- Urutan tampilan
- Status jabatan

### 4. Agenda Rapat

Modul ini digunakan untuk mencatat agenda rapat organisasi, meliputi:

- Judul rapat
- Tanggal rapat
- Waktu mulai
- Waktu selesai
- Tempat rapat
- Agenda pembahasan
- Hasil keputusan
- Catatan tambahan
- Status rapat

### 5. Absensi Rapat

Modul ini digunakan untuk mencatat kehadiran anggota dalam agenda rapat, meliputi:

- Agenda rapat
- Nama anggota
- Status kehadiran
- Catatan kehadiran

Status kehadiran yang tersedia:

- Hadir
- Izin
- Tidak hadir

### 6. Kas Organisasi

Modul ini digunakan untuk mencatat pemasukan dan pengeluaran kas organisasi.

Fitur pada modul kas:

- Catat pemasukan
- Catat pengeluaran
- Kategori transaksi
- Nominal transaksi
- Keterangan transaksi
- Pencatat transaksi
- Perhitungan total pemasukan
- Perhitungan total pengeluaran
- Perhitungan saldo kas otomatis

### 7. Kegiatan

Modul ini digunakan untuk mencatat kegiatan organisasi, meliputi:

- Nama kegiatan
- Tanggal kegiatan
- Lokasi kegiatan
- Deskripsi kegiatan
- Hasil kegiatan
- Link dokumentasi
- Status kegiatan

Status kegiatan yang tersedia:

- Direncanakan
- Selesai
- Dibatalkan

### 8. Laporan

Modul laporan digunakan untuk menampilkan laporan administrasi organisasi dalam bentuk halaman yang dapat dicetak atau disimpan sebagai PDF.

Laporan yang tersedia:

- Laporan data anggota
- Laporan kas organisasi
- Laporan agenda rapat

---

### AI Content Studio

The application includes an experimental AI-based social media content feature.

Main capabilities:

- Upload multiple activity photos
- Generate Instagram caption, hashtag, title, alt text, and summary
- Support demo fallback mode when API quota is unavailable
- Support multiple AI provider configuration
- Generate branded Instagram feed image using a fixed 4:5 portrait template
- Export generated feed as PNG

The visual template is designed for Karang Taruna RW 01 branding with fixed logo, organization name, brand colors, and Instagram footer.

Further visual refinements are documented in:

[docs/ai-content-studio-roadmap.md](docs/ai-content-studio-roadmap.md)

## Struktur Database

Tabel utama yang digunakan dalam project ini:

- `roles`
- `users`
- `members`
- `organizational_structures`
- `meetings`
- `attendances`
- `cash_transactions`
- `activities`

---

## Cara Menjalankan Project

### 1. Clone Repository

Clone repository dari GitHub:

```bash
git clone https://github.com/zakyilhamf647586-spec/civicyouth-management-system.git
```

Masuk ke folder project:

```bash
cd civicyouth-management-system
```

---

### 2. Install Dependency

Jalankan perintah berikut:

```bash
composer install
```

---

### 3. Buat File Environment

Copy file `env` menjadi `.env`.

Untuk Windows:

```bash
copy env .env
```

Untuk Linux / Mac:

```bash
cp env .env
```

---

### 4. Konfigurasi File `.env`

Buka file `.env`, lalu sesuaikan konfigurasi berikut:

```env
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost:8080/'

database.default.hostname = localhost
database.default.database = db_civicyouth
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306
```

Pastikan tanda `#` di depan baris konfigurasi database sudah dihapus jika masih ada.

---

### 5. Buat Database

Buka phpMyAdmin, lalu buat database baru dengan nama:

```sql
CREATE DATABASE db_civicyouth;
```

Atau buat secara manual melalui phpMyAdmin dengan nama:

```text
db_civicyouth
```

---

### 6. Jalankan Migration

Jalankan perintah berikut untuk membuat tabel database:

```bash
php spark migrate
```

---

### 7. Jalankan Seeder

Jalankan perintah berikut untuk membuat data awal, termasuk role dan akun admin:

```bash
php spark db:seed InitialSeeder
```

---

### 8. Jalankan Server Lokal

Jalankan server CodeIgniter:

```bash
php spark serve
```

Buka aplikasi di browser:

```text
http://localhost:8080
```

---

## Akun Admin Default

Gunakan akun berikut untuk login:

```text
Email    : admin@civicyouth.local
Password : admin123
```

Catatan: akun ini digunakan untuk kebutuhan development lokal. Pada penggunaan nyata, password sebaiknya segera diganti.

---

## Struktur Folder Penting

```text
civicyouth-management-system/
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Views/
│   ├── Database/
│   │   ├── Migrations/
│   │   └── Seeds/
│   └── Filters/
├── public/
│   └── assets/
│       ├── css/
│       └── img/
├── writable/
├── tests/
├── env
├── .gitignore
├── composer.json
├── spark
└── README.md
```

---

## Branding

Aplikasi ini menggunakan identitas visual Karang Taruna RW 01 dengan warna utama:

- Navy
- Gold
- Cream
- Putih

Pendekatan visual ini dipilih agar aplikasi terlihat formal, rapi, profesional, dan tetap sesuai dengan karakter organisasi kepemudaan.

Nama aplikasi yang tampil di sistem:

```text
Karang Taruna RW 01
Sistem Manajemen Organisasi Pemuda
```

Nama project untuk portofolio:

```text
CivicYouth Management System
```

---

## Status Project

Project ini berada pada tahap **MVP** atau versi awal.

Fitur inti sudah tersedia dan dapat digunakan untuk kebutuhan administrasi dasar organisasi, seperti pengelolaan anggota, struktur, rapat, absensi, kas, kegiatan, dan laporan.

---

## Deployment Notes

For production deployment, this project should be configured carefully before being used online.

For detailed cPanel/shared hosting deployment instructions, see:

[docs/cpanel-deployment-guide.md](docs/cpanel-deployment-guide.md)

### Production Setup

1. Upload the project to a hosting/server that supports PHP and MySQL.
2. Set the web document root to the `public` folder.
3. Create a production `.env` file based on `.env.example`.
4. Set the production environment configuration:

```env
CI_ENVIRONMENT = production
app.baseURL = 'https://your-domain.com/'
```

5. Configure the production database credentials:

```env
database.default.hostname = localhost
database.default.database = your_database_name
database.default.username = your_database_username
database.default.password = your_database_password
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306
```

6. Run database migration and seeder if supported by the server:

```bash
php spark migrate
php spark db:seed InitialSeeder
```

7. Make sure the `writable` directory has write permission.
8. Make sure the `public/uploads` directory has write permission.
9. Change the default admin email and password before real use.
10. Enable HTTPS/SSL on the domain.

### Important Security Notes

- Do not expose the project root directly.
- The public web root should point to the `public` directory.
- Do not upload the real `.env` file to GitHub.
- Do not keep the default admin password in production.
- Do not show demo credentials in production.
- Backup the database regularly.

### Final Production Test

Before using the application in a real environment, make sure:

- Login works properly.
- Dashboard loads correctly.
- Member management works.
- Organization structure management works.
- Meeting management works.
- Attendance and recap features work.
- Bulk attendance input works.
- Cash transaction management works.
- Activity documentation upload works.
- Excel export works.
- Excel import works.
- Print/Save PDF works.
- Logout works properly.

---

## Roadmap Pengembangan

Pengembangan berikutnya dapat mencakup:

- Export PDF otomatis
- Export Excel
- Upload foto kegiatan
- Upload dokumen organisasi
- Manajemen surat masuk dan surat keluar
- Manajemen notulen rapat
- Rekap absensi per rapat
- Rekap kas per bulan
- Role-based access control
- Hak akses berdasarkan jabatan
- API backend
- Pencarian dan filter data
- Dashboard statistik yang lebih lengkap
- Versi modern menggunakan Next.js, TypeScript, Supabase, Tailwind CSS, dan Vercel

---

---

## AI Content Studio Provider

AI Content Studio supports multiple AI provider modes.

### Available Modes

| Provider | Description | Best For |
|---|---|---|
| `demo` | Generates fallback content without external API | Portfolio demo, offline demo, GitHub showcase |
| `openai` | Uses official OpenAI API | Real production usage |
| `github_models` | Uses GitHub Models API | Development and prototyping |
| `openrouter` | Uses OpenRouter API | Testing alternative/free model routes |

### Example Configuration

```env
AI_PROVIDER=demo
```

For official OpenAI API:

```env
AI_PROVIDER=openai
OPENAI_API_KEY=your_openai_api_key
OPENAI_MODEL=gpt-4o-mini
```

For GitHub Models:

```env
AI_PROVIDER=github_models
GITHUB_MODELS_TOKEN=your_github_personal_access_token
GITHUB_MODELS_MODEL=openai/gpt-4o-mini
```

For OpenRouter:

```env
AI_PROVIDER=openrouter
OPENROUTER_API_KEY=your_openrouter_api_key
OPENROUTER_MODEL=openrouter/free
```

### Important Notes

- Do not commit real API keys to GitHub.
- Keep real API keys inside `.env`.
- Use `demo` mode for public portfolio demonstration.
- Free providers may have rate limits, quota limits, or model availability changes.
- For real organizational use, use a production-ready paid API provider or the organization's own API key.

## Rencana Versi Modern

Project ini dapat dikembangkan menjadi versi modern dengan stack:

- Next.js
- TypeScript
- Supabase
- Tailwind CSS
- Vercel

Versi modern tersebut dapat menjadi pengembangan lanjutan dari sistem ini dengan tampilan yang lebih modern, deployment online, dan arsitektur yang lebih fleksibel.

---

## Nilai Portofolio

Project ini menunjukkan kemampuan dalam:

- Membuat aplikasi web berbasis studi kasus nyata
- Membangun CRUD dengan CodeIgniter 4
- Merancang database relasional
- Menggunakan migration dan seeder
- Mengelola autentikasi login
- Membuat dashboard sistem
- Membuat laporan printable
- Mengelola project dengan Git dan GitHub
- Menyusun aplikasi dengan identitas visual organisasi
- Mengubah kebutuhan organisasi menjadi sistem digital

---

## Author

**Zaky Ilham Ferdiansyah**

Mahasiswa Teknik Informatika  
Ketua Karang Taruna RW 01 Kelurahan Randugarut

---

## Catatan

Project ini dibuat sebagai bagian dari proses pembelajaran, pengembangan portofolio, dan penerapan teknologi informasi untuk kebutuhan administrasi organisasi pemuda di lingkungan masyarakat.