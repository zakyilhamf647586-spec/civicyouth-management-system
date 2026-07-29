# Automated Backup & Disaster Recovery V1

Fase 4B melindungi database dan file upload GARDA 01.

## Fitur

- backup database memakai mysqldump;
- fallback exporter PHP;
- backup public/uploads dan writable/uploads;
- ZIP atomik;
- SHA-256 archive;
- checksum setiap file;
- verifikasi integritas;
- lock operasi;
- retensi latest, daily, weekly, monthly;
- Windows Scheduled Task dan Linux cron;
- restore CLI dengan safety backup;
- Audit CMS untuk aksi penting;
- dashboard `/system/backups`.

## Batas keamanan

- archive berada di `writable/backups`;
- tidak ada tombol download;
- tidak ada restore melalui browser;
- `.env` tidak dimasukkan;
- password database hanya ditulis ke file sementara berizin 0600;
- file credential sementara dihapus setelah operasi.

## Command

```text
backup:create
backup:list
backup:verify
backup:prune
backup:restore
```

## Hak akses Portal

Admin:
- lihat;
- buat;
- verifikasi;
- retensi.

Ketua:
- lihat;
- buat;
- verifikasi.

Restore hanya untuk operator yang memiliki akses terminal server.
