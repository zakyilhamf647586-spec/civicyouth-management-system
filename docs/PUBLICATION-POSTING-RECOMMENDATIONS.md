# Rekomendasi Waktu Tayang GARDA 01

## Prinsip

Rekomendasi dibuat dari data internal GARDA 01, bukan angka umum
atau klaim universal tentang algoritma Instagram.

Sistem memakai konten berstatus Dipublikasikan yang mempunyai
snapshot Instagram Insights dalam periode analisis.

## Formula skor

```text
50% Reach rata-rata
30% Engagement Rate
20% Saves + Shares rata-rata
× faktor jumlah sampel
```

Engagement Rate:

```text
(Likes + Comments + Shares + Saves) / Reach × 100
```

## Pengelompokan waktu

- 00.00–05.59
- 06.00–08.59
- 09.00–11.59
- 12.00–14.59
- 15.00–17.59
- 18.00–20.59
- 21.00–23.59

Setiap slot juga dikelompokkan berdasarkan hari Senin–Minggu.

## Kualitas data

- 1–2 konten: Data Awal
- 3–5 konten: Keyakinan Sedang
- 6+ konten: Keyakinan Tinggi

Minimum representatif default adalah tiga konten terukur.

## Baseline eksperimen

Ketika data belum cukup, Portal menampilkan baseline internal yang
dapat diubah pada:

```text
app/Config/SocialMedia.php
```

Baseline bukan klaim waktu terbaik secara umum. Tujuannya adalah
membangun eksperimen yang konsisten sampai data internal tersedia.

## URL

```text
/publications/recommendations
```

Saran tiga slot teratas juga tampil pada field Rencana Tayang.

## Hak akses

- Admin dan Ketua: melalui wildcard publikasi.
- Sekretaris: `publications.recommendations.view`.
- Bendahara dan Pengurus: tidak melihat rekomendasi.

## Database

Tidak membutuhkan migration.
