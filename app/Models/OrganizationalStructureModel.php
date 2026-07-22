<?php

namespace App\Models;

use CodeIgniter\Model;

class OrganizationalStructureModel extends Model
{
    protected $table         = 'organizational_structures';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'member_id',
        'position_name',
        'division',
        'rt_scope',
        'period',
        'description',
        'sort_order',
        'status',
        'photo',
        'short_bio',
    ];

    protected $useTimestamps = true;

    /**
     * Terapkan satu sumber filter yang sama untuk hitungan Beranda dan halaman
     * Pengurus publik. Field persetujuan tampil dan periode aktif belum tersedia
     * pada schema saat ini, sehingga keduanya akan ditambahkan pada fase CMS/data.
     */
    public function applyPublicVisibility(): self
    {
        $this
            ->where('organizational_structures.status', 'active')
            ->where('members.membership_status', 'active');

        return $this;
    }

    public function publicOfficials(): array
    {
        $this->select(
            'organizational_structures.*, '
            . 'members.full_name AS member_name, '
            . 'members.rt AS member_rt'
        );

        $this->join(
            'members',
            'members.id = organizational_structures.member_id',
            'inner'
        );

        $this->applyPublicVisibility();

        return $this
            ->orderBy('organizational_structures.sort_order', 'ASC')
            ->orderBy('organizational_structures.id', 'ASC')
            ->findAll();
    }

    public function countPublicOfficials(): int
    {
        $this->join(
            'members',
            'members.id = organizational_structures.member_id',
            'inner'
        );

        $this->applyPublicVisibility();

        return $this->countAllResults();
    }
}
