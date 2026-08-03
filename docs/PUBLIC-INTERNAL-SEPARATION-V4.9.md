# GARDA 01 — Public/Internal Separation V4.9

## Tujuan

Memisahkan pengalaman website publik dari akses Portal internal tanpa membuat aplikasi kedua dan tanpa mengubah route autentikasi yang sudah aman.

## Keputusan arsitektur

- Website publik tidak menampilkan tombol atau tautan `Portal Pengurus`, `Portal Internal`, atau `Login`.
- Akses lokal pengurus tetap tersedia langsung melalui `/login`.
- Route `/login` dan `/en/login` tetap aktif, tetapi tidak dipromosikan pada permukaan publik dan tetap ditutup dari pengindeksan.
- Pada production, arsitektur yang direkomendasikan adalah:
  - `garda01.org` untuk website publik;
  - `portal.garda01.org` untuk Portal internal;
  - satu project CodeIgniter dan satu database yang sama.
- Penegakan host/subdomain dilakukan pada tahap deployment setelah domain dan hosting tersedia. V4.9 tidak memaksa host tertentu agar localhost tetap aman.

## Perlindungan baru

1. Default Navigation Manager tidak lagi memuat CTA Portal.
2. Navigation Manager menolak item yang mengarah ke autentikasi, dashboard, CMS, backup, monitoring, atau route internal lain.
3. Helper publik menyaring item internal dari draft maupun published menu sebagai pertahanan tambahan.
4. Migration membersihkan link internal yang mungkin sudah tersimpan dalam database.
5. Introducing dan footer tidak lagi menampilkan akses internal.
6. Microcopy form kontak tidak menyebut Portal Pengurus.
7. Audit otomatis tersedia melalui:

```powershell
php spark public:boundary:audit --strict
```

## Kontras mode Midnight

V4.9 juga memperkuat kontras pada section Kirim Pesan:

- label form;
- teks penjelas;
- submit note;
- placeholder;
- border dan focus state input.

## File yang tidak diubah

- `app/Config/Routes.php`
- `app/Config/Permissions.php`
- autentikasi dan session security;
- Fase 4B Backup & Recovery;
- Fase 4C Operational Monitoring;
- desain utama Introducing, Beranda, dan Portal.

## Batasan

Penyembunyian tautan bukan mekanisme keamanan utama. Pengamanan tetap berasal dari autentikasi, session security, permission route, RBAC, rate limiting, audit, dan server configuration.
