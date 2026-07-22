# Manifest Rollback Fase 1

## Sebelum instalasi

Jalankan:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\phase1-backup.ps1
```

Backup berisi:

- dump database;
- source sebelum Fase 1;
- arsip `public/uploads`;
- commit dan status Git;
- working-tree diff.

## Rollback source

```powershell
powershell -ExecutionPolicy Bypass `
    -File .\scripts\phase1-rollback.ps1 `
    -BackupDirectory .\backups\phase1-YYYYMMDD-HHmmss `
    -ConfirmRollback
```

Script rollback source tidak otomatis mengimpor database dan tidak otomatis
mengganti uploads, agar data baru setelah instalasi tidak hilang tanpa sengaja.

## Rollback migration

Gunakan rollback satu batch hanya jika migration Fase 1 berada pada batch terbaru:

```powershell
php spark migrate:rollback
```

Periksa dahulu:

```powershell
php spark migrate:status
```

## Catatan penting

`git restore` bukan langkah instalasi. Perintah tersebut hanya relevan untuk
rollback manual dan dapat membuang perubahan yang belum di-commit.
