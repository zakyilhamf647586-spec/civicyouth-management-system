# GARDA 01 — Disaster Recovery Runbook V1

## Yang dilindungi

- database aplikasi;
- `public/uploads`;
- `writable/uploads`;
- metadata release tanpa secret.

`.env`, source release, sertifikat SSL, dan salinan backup off-site
harus dikelola terpisah.

## Backup manual

```bash
php spark backup:create --tag manual
php spark backup:list
php spark backup:verify --file ARCHIVE.zip
```

## Backup terjadwal

Windows:

```powershell
powershell -ExecutionPolicy Bypass `
    -File .\scripts\install-backup-schedule.ps1
```

Linux: gunakan `deploy/backup-cron.example`.

## Retensi

```bash
php spark backup:prune --dry-run
php spark backup:prune
```

Default:

- 10 archive terbaru;
- satu per hari selama 14 hari;
- satu per minggu selama 8 minggu;
- satu per bulan selama 12 bulan;
- backup protected tidak dihapus otomatis.

## Restore

1. hentikan trafik pada web server atau hosting;
2. pastikan archive terverifikasi;
3. pastikan binary `mysql` tersedia;
4. jalankan:

```bash
php spark backup:restore \
  --file ARCHIVE.zip \
  --yes \
  --maintenance-confirmed YES
```

Command membuat safety backup sebelum restore kecuali operator
secara sadar memakai `--skip-safety-backup`.

## Setelah restore

```bash
php spark migrate:status
php spark cache:clear
php spark production:check
```

Lakukan smoke test website publik, login, data anggota, kas,
kegiatan, CMS, upload, dan audit sebelum membuka trafik.

## Catatan kritis

- jangan menyimpan backup di folder public;
- jangan mengirim archive melalui kanal publik;
- jangan memakai `migrate:refresh`;
- simpan sekurangnya satu salinan backup di perangkat atau storage kedua.
