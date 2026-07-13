# Deployment Checklist

## Server Requirements

- PHP 8.2 or newer
- MySQL / MariaDB
- Composer support
- PHP extensions:
  - intl
  - mbstring
  - mysqli
  - gd
  - zip
- SSL / HTTPS support

## Hosting Setup

- Upload project files
- Set document root to `/public`
- Create database
- Import database or run migrations
- Configure `.env`
- Set `CI_ENVIRONMENT = production`
- Set correct `app.baseURL`
- Ensure `/writable` is writable
- Ensure `/public/uploads` is writable
- Change default admin credentials

## Security Notes

- Do not upload real `.env` to GitHub
- Do not expose project root directly
- Do not keep default admin password in production
- Do not show demo credentials in production
- Backup database regularly

## Final Test

- Login works
- Dashboard loads
- CRUD modules work
- File upload works
- Excel export works
- Excel import works
- Print/Save PDF works
- Logout works