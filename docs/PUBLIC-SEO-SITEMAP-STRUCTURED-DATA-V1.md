# Public SEO, Sitemap & Structured Data V1

## Scope

Fase 2F menambahkan fondasi SEO teknis untuk seluruh website publik
GARDA 01 tanpa mengubah route halaman yang sudah berjalan.

## Fitur

- canonical URL bersih;
- Open Graph lengkap;
- Twitter/X Card;
- alt text gambar berbagi sosial;
- kode verifikasi Google dan Bing;
- JSON-LD Organization;
- JSON-LD WebSite;
- JSON-LD WebPage;
- BreadcrumbList;
- Article untuk detail kegiatan;
- CreativeWork untuk detail program;
- ItemList untuk daftar program dan kegiatan;
- sitemap XML dinamis;
- robots.txt dinamis;
- dashboard audit SEO internal.

## Endpoint publik

```text
/sitemap.xml
/robots.txt
```

## Portal

```text
/website/seo
```

## Sumber sitemap

- enam halaman publik utama;
- seluruh Program GARDA 01 berstatus published;
- seluruh kegiatan yang lolos public visibility scope.

## Pengaturan baru

- Alt Gambar Berbagi Sosial;
- Akun X / Twitter;
- Kode Verifikasi Google;
- Kode Verifikasi Bing.

## Catatan deployment

Base URL pada `.env` production harus menggunakan domain HTTPS
sebenarnya. Sitemap, canonical, dan structured data menggunakan
Base URL tersebut.

Pada Apache, `.htaccess` meneruskan robots.txt dan sitemap.xml ke
CodeIgniter. File `public/robots.txt` tetap disediakan sebagai
fallback untuk server yang melayani file statis secara langsung.
