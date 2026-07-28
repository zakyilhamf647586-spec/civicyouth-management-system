# Production Deployment Readiness V1

Fase 4A menyediakan gerbang pra-deploy GARDA 01:

- dashboard `/system/readiness`;
- command `php spark production:check`;
- ekspor laporan JSON;
- template `.env` production;
- template Apache, Nginx, dan cPanel;
- script preflight;
- pembuat package yang mengecualikan secret dan data runtime;
- runbook deployment dan rollback.

Pemeriksaan blocking harus diselesaikan sebelum go-live.

Dashboard tidak menampilkan password database, encryption key,
API key, token, cookie, atau secret `.env`.

Hak akses: Admin dan Ketua.
