<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Permissions extends BaseConfig
{
    /**
     * Permission map GARDA 01 Portal.
     *
     * Security principle:
     * - unknown roles receive no permission;
     * - Admin receives all permissions;
     * - every other role receives only explicit permissions.
     *
     * Wildcards are supported, for example "meetings.*".
     *
     * @var array<string, list<string>>
     */
    public array $rolePermissions = [
        'admin' => [
            '*',
        ],

        'ketua' => [
            'dashboard.view',

            'members.*',
            'structures.*',
            'meetings.*',
            'attendances.*',

            'cash.view',
            'cash.export',

            'activities.*',
            'programs.*',
            'publications.*',
            'content_studio.*',
            'messages.*',

            'reports.*',
            'settings.website.manage',
        ],

        'sekretaris' => [
            'dashboard.view',

            'members.view',
            'members.create',
            'members.update',
            'members.import',
            'members.export',

            'structures.view',
            'structures.create',
            'structures.update',

            'meetings.*',
            'attendances.*',

            'activities.view',
            'activities.create',
            'activities.update',
            'activities.delete',
            'activities.submit_review',
            'activities.return_to_draft',
            'activities.gallery.view',
            'activities.gallery.manage',

            'programs.view',
            'programs.create',
            'programs.update',

            'publications.view',
            'publications.create',
            'publications.update',
            'publications.assets',
            'publications.workflow',
            'publications.metrics.view',
            'publications.metrics.manage',
            'publications.metrics.export',
            'publications.audit.view',
            'publications.deadlines.view',
            'publications.recommendations.view',

            'content_studio.*',
            'messages.*',

            'reports.view',
            'reports.members',
            'reports.meetings',
        ],

        'bendahara' => [
            'dashboard.view',

            'members.view',
            'structures.view',
            'meetings.view',
            'attendances.view',
            'attendances.recap',

            'cash.*',

            'activities.view',
            'activities.gallery.view',
            'programs.view',
            'publications.view',
            'publications.metrics.view',

            'reports.view',
            'reports.cash',
        ],

        'pengurus' => [
            'dashboard.view',
            'members.view',
            'structures.view',
            'meetings.view',
            'attendances.view',
            'activities.view',
            'activities.gallery.view',
            'programs.view',
            'publications.view',
            'publications.metrics.view',
        ],
    ];

    /**
     * Friendly labels used on the access-denied page.
     *
     * @var array<string, string>
     */
    public array $permissionLabels = [
        'dashboard.view' => 'melihat dashboard',

        'users.view'           => 'melihat manajemen akun',
        'users.create'         => 'membuat akun pengguna',
        'users.update'         => 'mengubah akun pengguna',
        'users.status'         => 'mengaktifkan atau menonaktifkan akun',
        'users.reset_password' => 'mereset kata sandi pengguna',

        'members.view'   => 'melihat data anggota',
        'members.create' => 'menambah anggota',
        'members.update' => 'mengubah anggota',
        'members.delete' => 'menghapus anggota',
        'members.import' => 'mengimpor data anggota',
        'members.export' => 'mengekspor data anggota',

        'structures.view'   => 'melihat struktur pengurus',
        'structures.create' => 'menambah struktur pengurus',
        'structures.update' => 'mengubah struktur pengurus',
        'structures.delete' => 'menghapus struktur pengurus',

        'meetings.view'   => 'melihat agenda rapat',
        'meetings.create' => 'menambah agenda rapat',
        'meetings.update' => 'mengubah agenda rapat',
        'meetings.delete' => 'menghapus agenda rapat',

        'attendances.view'   => 'melihat absensi',
        'attendances.create' => 'menambah absensi',
        'attendances.update' => 'mengubah absensi',
        'attendances.delete' => 'menghapus absensi',
        'attendances.recap'  => 'melihat rekap absensi',
        'attendances.bulk'   => 'mengisi absensi massal',

        'cash.view'   => 'melihat kas organisasi',
        'cash.create' => 'mencatat transaksi kas',
        'cash.update' => 'mengubah transaksi kas',
        'cash.delete' => 'menghapus transaksi kas',
        'cash.export' => 'mengekspor laporan kas',

        'activities.view'            => 'melihat kegiatan',
        'activities.create'          => 'menambah kegiatan',
        'activities.update'          => 'mengubah kegiatan',
        'activities.delete'          => 'menghapus kegiatan',
        'activities.submit_review'   => 'mengirim kegiatan untuk ditinjau',
        'activities.publish'         => 'menerbitkan kegiatan',
        'activities.return_to_draft' => 'mengembalikan kegiatan menjadi draft',
        'activities.archive'         => 'mengarsipkan kegiatan',
        'activities.gallery.view'    => 'melihat galeri kegiatan',
        'activities.gallery.manage'  => 'mengelola galeri kegiatan',

        'programs.view'    => 'melihat program GARDA 01',
        'programs.create'  => 'menambah program',
        'programs.update'  => 'mengubah program',
        'programs.publish' => 'menerbitkan program',
        'programs.archive' => 'mengarsipkan program',

        'publications.view'     => 'melihat publikasi sosial',
        'publications.create'   => 'membuat brief publikasi',
        'publications.update'   => 'mengubah brief publikasi',
        'publications.assets'   => 'mengelola aset publikasi',
        'publications.workflow' => 'menjalankan alur produksi publikasi',
        'publications.review'   => 'meminta revisi publikasi',
        'publications.approve'  => 'menyetujui publikasi',
        'publications.publish'  => 'menjadwalkan atau menandai publikasi tayang',
        'publications.archive'  => 'mengarsipkan atau membuka kembali publikasi',
        'publications.metrics.view' => 'melihat analitik performa publikasi',
        'publications.metrics.manage' => 'mencatat atau menghapus metrik Instagram',
        'publications.metrics.export' => 'mengekspor analitik performa publikasi',
        'publications.audit.view' => 'melihat audit trail publikasi',
        'publications.deadlines.view' => 'melihat deadline produksi publikasi',
        'publications.recommendations.view' => 'melihat rekomendasi waktu tayang publikasi',

        'content_studio.view'     => 'melihat AI Content Studio',
        'content_studio.create'   => 'membuat konten',
        'content_studio.update'   => 'mengubah dan menghasilkan konten',
        'content_studio.delete'   => 'menghapus konten',

        'messages.view'   => 'melihat pesan masuk',
        'messages.manage' => 'mengelola status pesan',

        'reports.view'     => 'melihat pusat laporan',
        'reports.members'  => 'melihat laporan anggota',
        'reports.cash'     => 'melihat laporan kas',
        'reports.meetings' => 'melihat laporan rapat',

        'settings.website.manage' => 'mengelola pengaturan website',
    ];
}
