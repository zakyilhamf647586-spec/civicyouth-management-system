# GARDA 01 — Uptime Monitoring Guide V1

## Endpoint

```text
/health/live
/health/ready
```

`/health/live` hanya membuktikan bahwa proses aplikasi menjawab.

`/health/ready` memeriksa database, migration, filesystem, disk,
backup, audit, log, dan lock operasi. Endpoint mengembalikan:

- HTTP `200` untuk `healthy` atau `degraded`;
- HTTP `503` untuk `critical`;
- HTTP `429` bila rate limit terlampaui.

Respons tidak memuat password, path server, query, stack trace,
API key, atau secret lain.

## Monitor eksternal

Atur layanan uptime untuk membuka:

```text
https://domain-anda/health/live
https://domain-anda/health/ready
```

Interval yang wajar:

- live: 1–5 menit;
- ready: 5 menit.

## Scheduler internal

Windows:

```powershell
powershell -ExecutionPolicy Bypass `
    -File .\scripts\install-health-monitor-schedule.ps1
```

Linux: gunakan `deploy/health-monitor-cron.example`.
