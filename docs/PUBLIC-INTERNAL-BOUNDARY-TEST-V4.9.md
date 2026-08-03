# Uji Public/Internal Boundary V4.9

## Audit otomatis

```powershell
php spark public:boundary:audit --strict
php spark public:boundary:audit --json
```

Target:

```text
0 gagal, 0 peringatan
```

## Website publik

Periksa route berikut dalam mode Indonesia dan English:

- `/`
- `/home`
- `/profil`
- `/program`
- `/kegiatan`
- `/pengurus`
- `/kontak`
- `/en`
- `/en/home`
- `/en/about`
- `/en/programs`
- `/en/activities`
- `/en/team`
- `/en/contact`

Kriteria:

- navbar tidak menampilkan Portal/Login;
- Jelajah tidak menampilkan Portal/Login;
- Introducing tidak menampilkan akses internal;
- footer tidak menampilkan akses internal;
- tidak ada ruang kosong atau alignment rusak setelah CTA dihapus;
- form Kontak mode Midnight memiliki kontras yang jelas.

## Akses internal

Buka langsung:

- `/login`
- `/en/login`

Kriteria:

- login tetap dapat dibuka;
- login berhasil membawa pengguna ke Portal sesuai role;
- logout dan session security tetap berfungsi.

## Navigation Manager

1. Buka `/website/navigation` sebagai role berizin.
2. Pastikan CTA Portal lama tidak muncul setelah migration.
3. Coba masukkan `/login`, `/system/backups`, `/website/audit`, atau `https://portal.example.org`.
4. Simpan draft.

Target:

- draft ditolak dengan pesan bahwa route internal tidak boleh dipublikasikan;
- link publik dan eksternal biasa tetap dapat disimpan;
- preview dan publish tetap berfungsi.

## Regression

Periksa kembali:

- `/system/backups`
- `/system/operations`
- `/system/readiness`
- `/health/live`
- `/health/ready`
- `php spark auth:permissions:audit --strict`
- `php spark system:health`
