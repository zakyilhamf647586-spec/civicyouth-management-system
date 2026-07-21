# Audit Trail Publikasi GARDA 01

## Tujuan

Menyediakan catatan aktivitas yang dapat ditelusuri untuk setiap
record Publikasi Sosial.

## Aktivitas yang direkam

- brief dibuat;
- data publikasi diperbarui;
- status workflow berubah;
- aset ditambahkan;
- aset dihapus;
- snapshot Instagram Insights ditambahkan;
- snapshot performa dihapus.

## Informasi audit

Setiap log menyimpan:

- publikasi terkait;
- jenis aktivitas;
- ringkasan;
- status sebelum dan sesudah;
- field yang berubah;
- metadata teknis;
- ID pengguna;
- nama pengguna;
- peran;
- alamat IP;
- user agent;
- waktu kejadian.

## URL

```text
/publications/audit
```

Riwayat per publikasi juga tampil di:

```text
/publications/{id}
```

## Hak akses

- Admin dan Ketua: melalui wildcard `publications.*`;
- Sekretaris: `publications.audit.view`;
- Bendahara dan Pengurus: tidak melihat audit trail.

## Catatan

Audit trail mulai merekam aktivitas setelah migration dijalankan.
Riwayat sebelum pemasangan tidak dibuat mundur secara otomatis.

Log tidak memiliki fitur hapus dari antarmuka Portal.
