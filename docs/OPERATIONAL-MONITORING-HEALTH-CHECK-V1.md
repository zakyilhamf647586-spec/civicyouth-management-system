# Operational Monitoring & Health Check V1

Fase 4C menyediakan:

- dashboard `/system/operations`;
- liveness `/health/live`;
- readiness `/health/ready`;
- snapshot kesehatan berkala;
- incident center otomatis;
- tren 48 snapshot terbaru;
- integrasi database, migration, writable, disk, backup, audit, log;
- command `system:health`;
- scheduler Windows dan cron Linux;
- ekspor JSON tanpa secret.

## Command

```text
system:health
system:health:snapshot
system:health:prune
```

## Incident lifecycle

- warning/critical membuka atau memperbarui insiden;
- operator dapat menandai insiden telah diketahui;
- status kembali normal menyelesaikan insiden otomatis;
- pengakuan tidak memaksa status menjadi sehat.

## Hak akses

Admin dan Ketua dapat melihat, mengekspor, membuat snapshot,
dan mengakui insiden.
