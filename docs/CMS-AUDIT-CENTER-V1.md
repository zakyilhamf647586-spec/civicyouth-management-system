# Central CMS Audit Activity V1

## Scope

Fase 3D menyatukan catatan aktivitas penting dari:

- CMS halaman publik;
- navigasi website;
- pengaturan website;
- Program GARDA 01;
- Kegiatan;
- Publikasi Sosial;
- review eksternal;
- penolakan izin Portal.

## Endpoint

```text
/website/audit
/website/audit/{id}
/website/audit/export
```

## Prinsip keamanan

- audit bersifat fail-soft: kegagalan penyimpanan audit tidak
  membatalkan perubahan utama;
- password, token mentah, token hash, cookie, CSRF, dan secret
  disensor sebelum metadata disimpan;
- alamat IP hanya disimpan sebagai hash;
- audit record tidak menyediakan aksi edit atau hapus;
- ekspor dibatasi maksimal 5.000 event per permintaan.

## Role

- Admin: lihat dan ekspor;
- Ketua: lihat dan ekspor melalui permission `website.*`;
- Sekretaris, Bendahara, Pengurus: tidak memperoleh akses audit
  terpusat.

## Event utama

```text
page.draft_saved
page.review_submitted
page.changes_requested
page.approved
page.published
page.revision_restored

navigation.draft_saved
navigation.published
navigation.draft_restored

settings.website_updated

program.created
program.updated
program.published
program.archived

activity.created
activity.updated
activity.review_submitted
activity.published
activity.returned_to_draft
activity.archived
activity.deleted

publication.*
external_review.*
access.permission_denied
```

## Retention

Versi V1 tidak menghapus audit otomatis. Retention policy dan
archival dapat ditambahkan setelah volume produksi diketahui.
