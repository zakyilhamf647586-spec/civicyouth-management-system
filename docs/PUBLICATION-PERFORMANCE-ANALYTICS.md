# Analitik Performa Instagram GARDA 01

## Tujuan

Mencatat performa setiap konten Instagram secara manual dan
membandingkan efektivitas format serta pilar program.

## Data yang dicatat

- Reach
- Impressions
- Likes
- Comments
- Shares
- Saves
- Profile visits
- Follows
- Link clicks
- Video views
- Waktu snapshot
- Catatan
- Pencatat

Setiap input disimpan sebagai snapshot historis. Analitik bulanan
menggunakan snapshot terbaru dari setiap konten yang tayang pada
bulan terpilih.

## Rumus Engagement Rate

```text
(Likes + Comments + Shares + Saves) / Reach × 100
```

## URL

```text
/publications/analytics
/publications/analytics/export
```

Input snapshot dilakukan pada halaman detail publikasi.

## Hak akses

- Admin dan Ketua: seluruh fungsi melalui wildcard publikasi.
- Sekretaris: lihat, input, hapus, dan export metrik.
- Bendahara dan Pengurus: lihat analitik.
- Input metrik hanya tersedia untuk publikasi berstatus Published.

## Sumber data

Versi ini menggunakan input manual dari Instagram Insights dan
belum memakai Instagram Graph API. Pendekatan ini dapat langsung
digunakan tanpa kredensial Meta atau proses review aplikasi.

## Database

Migration membuat tabel `content_post_metrics`.
Satu publikasi dapat mempunyai banyak snapshot performa.
