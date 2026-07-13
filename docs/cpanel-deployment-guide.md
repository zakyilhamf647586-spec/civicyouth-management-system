# cPanel Deployment Guide

This document explains how to deploy the CivicYouth Management System to a shared hosting or cPanel environment.

The project is built with:

- CodeIgniter 4
- PHP
- MySQL
- Composer
- PhpSpreadsheet
- File upload support

---

## 1. Recommended Hosting Requirements

Before deploying, make sure the hosting supports:

- PHP 8.2 or newer
- MySQL / MariaDB
- Composer support, if available
- PHP extensions:
  - intl
  - mbstring
  - mysqli
  - gd
  - zip
- SSL / HTTPS
- File Manager or FTP access
- Ability to set file permissions

---

## 2. Recommended Folder Structure

For better security, do not expose the whole project directly through `public_html`.

Recommended structure:

```text
/home/username/
├── civicyouth-app/
│   ├── app/
│   ├── system/
│   ├── vendor/
│   ├── writable/
│   ├── .env
│   ├── composer.json
│   ├── spark
│   └── other project files
│
└── public_html/
    ├── index.php
    ├── favicon.ico
    ├── assets/
    └── uploads/
```

Explanation:

- `public_html` contains files from the CodeIgniter `public` folder.
- `civicyouth-app` contains the application source code.
- The real `.env` file should be placed inside `civicyouth-app`.
- Only public assets should be accessible from the browser.

---

## 3. Upload Project Files

Upload these folders and files to:

```text
/home/username/civicyouth-app/
```

Required files and folders:

```text
app/
system/
vendor/
writable/
composer.json
composer.lock
spark
.env
```

If the hosting supports Composer and SSH, you can upload the project without `vendor`, then run:

```bash
composer install --no-dev --optimize-autoloader
```

If the hosting does not support Composer, upload the existing local `vendor` folder.

---

## 4. Move Public Files

Open the local project folder:

```text
civicyouth-management-system/public
```

Upload the contents of the `public` folder into:

```text
/home/username/public_html/
```

So `public_html` should contain:

```text
index.php
favicon.ico
assets/
uploads/
```

---

## 5. Edit `public_html/index.php`

Open:

```text
public_html/index.php
```

Find this line:

```php
$pathsPath = realpath(FCPATH . '../app/Config/Paths.php') ?: FCPATH . '../app/Config/Paths.php';
```

Change it to:

```php
$pathsPath = realpath(FCPATH . '../civicyouth-app/app/Config/Paths.php') ?: FCPATH . '../civicyouth-app/app/Config/Paths.php';
```

This tells CodeIgniter where the real application folder is located.

---

## 6. Create Production `.env`

Inside:

```text
/home/username/civicyouth-app/
```

Create a file named:

```text
.env
```

Example production configuration:

```env
CI_ENVIRONMENT = production

app.baseURL = 'https://your-domain.com/'

database.default.hostname = localhost
database.default.database = your_database_name
database.default.username = your_database_username
database.default.password = your_database_password
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306
```

Important:

- Replace `https://your-domain.com/` with your real domain.
- Replace all database credentials with your hosting database credentials.
- Do not upload the real `.env` file to GitHub.

---

## 7. Create MySQL Database in cPanel

In cPanel:

1. Open **MySQL Databases**.
2. Create a new database.
3. Create a database user.
4. Set a strong password.
5. Add the user to the database.
6. Give the user all required privileges.
7. Copy the database name, username, and password into `.env`.

---

## 8. Import Database

There are two options.

### Option A — Import SQL via phpMyAdmin

1. Open phpMyAdmin from cPanel.
2. Select the production database.
3. Click **Import**.
4. Upload the exported `.sql` file from local database.
5. Click **Go**.

### Option B — Run Migration and Seeder

If SSH is available, go to:

```bash
cd /home/username/civicyouth-app
```

Then run:

```bash
php spark migrate
php spark db:seed InitialSeeder
```

---

## 9. Set Folder Permissions

Make sure these folders are writable:

```text
civicyouth-app/writable/
public_html/uploads/
```

Recommended permission:

```text
755
```

If upload or cache fails, try:

```text
775
```

Avoid using `777` unless there is no other option.

---

## 10. Production Security Checklist

Before real use:

- Change the default admin email.
- Change the default admin password.
- Make sure `CI_ENVIRONMENT = production`.
- Make sure demo credentials are not shown on the login page.
- Make sure `.env` is not accessible from the browser.
- Make sure project root is not exposed directly.
- Enable SSL / HTTPS.
- Backup database regularly.
- Test file upload.
- Test Excel import/export.
- Test print/save PDF.

---

## 11. Final Test After Deployment

Open the domain in browser and test:

- Login
- Dashboard
- Data Anggota
- Struktur Pengurus
- Agenda Rapat
- Rekap Absensi
- Input Absensi Massal
- Kas Organisasi
- Kegiatan
- Upload Dokumentasi Kegiatan
- Laporan
- Export Excel
- Import Excel
- Logout

---

## 12. Common Problems

### 404 Not Found

Possible causes:

- Wrong `app.baseURL`
- Wrong route
- Incorrect `index.php` path configuration
- Project root exposed incorrectly

### Blank Page / Error 500

Possible causes:

- PHP version not compatible
- Missing Composer vendor folder
- Missing PHP extension
- Wrong file permission
- Incorrect `.env` configuration

### File Upload Fails

Possible causes:

- `public_html/uploads` is not writable
- File size exceeds limit
- PHP upload limit is too small

### Excel Export or Import Fails

Possible causes:

- `zip` extension is disabled
- `gd` extension is disabled
- PhpSpreadsheet is not installed
- `vendor` folder is missing

### Database Error

Possible causes:

- Wrong database name
- Wrong username
- Wrong password
- Database user has no privileges
- Migration has not been run