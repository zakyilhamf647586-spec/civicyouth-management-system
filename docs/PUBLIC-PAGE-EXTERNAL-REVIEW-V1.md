# Secure External Page Preview & Review V1

## Scope

Fase 3C memungkinkan Ketua atau Admin membagikan snapshot draft
Beranda, Profil, atau Kontak kepada reviewer di luar Portal tanpa
memberikan akun admin.

## Prinsip keamanan

- token acak 256-bit;
- database hanya menyimpan SHA-256 token;
- token dibatasi untuk satu halaman dan satu revision snapshot;
- masa berlaku 1–30 hari;
- batas akses 5–500 tampilan;
- token dapat dicabut;
- preview selalu noindex;
- validasi token gagal menggunakan respons generik;
- request dibatasi dengan Throttler;
- IP disimpan sebagai hash, bukan alamat mentah;
- feedback eksternal tidak mengubah approval internal;
- website publik tidak berubah.

## Alur

```text
Simpan Draft
→ Buat Tautan Review Eksternal
→ Sistem Membuat Snapshot Terkunci
→ Reviewer Membuka Tanpa Login
→ Reviewer Memberi Komentar/Keputusan
→ Pengurus Meninjau Masukan
→ Workflow Internal Tetap Berjalan
```

## Status tautan

```text
Aktif
Tanggapan Diterima
Kedaluwarsa
Batas Akses Tercapai
Dicabut
```

## Catatan identitas

Nama dan email reviewer pada form merupakan identitas yang
dinyatakan sendiri. Tautan token adalah bukti akses, bukan
autentikasi identitas formal.

## URL admin

```text
/website/pages/external-review/{page}
```

## URL publik

```text
/review/page/{token}
```

## Deployment

Gunakan HTTPS. URL token dapat tercatat pada access log web server;
karena itu jangan membagikan tautan melalui kanal publik atau
menyimpannya di screenshot terbuka.

## Batas snapshot

Snapshot mengunci metadata dan section yang dikelola CMS halaman.
Data operasional dinamis seperti statistik anggota, program terbit,
atau kegiatan terbaru tetap dibaca dari data Portal saat halaman
dibuka agar komponen publik tidak diduplikasi.
