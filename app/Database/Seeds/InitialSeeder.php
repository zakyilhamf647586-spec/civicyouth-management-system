<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitialSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $roles = [
            [
                'role_name'   => 'Admin',
                'description' => 'Administrator utama sistem',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'role_name'   => 'Ketua',
                'description' => 'Ketua organisasi',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'role_name'   => 'Sekretaris',
                'description' => 'Pengelola administrasi dan dokumen organisasi',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'role_name'   => 'Bendahara',
                'description' => 'Pengelola keuangan organisasi',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'role_name'   => 'Pengurus',
                'description' => 'Pengurus umum organisasi',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ];

        foreach ($roles as $role) {
            $existingRole = $this->db->table('roles')
                ->where('role_name', $role['role_name'])
                ->get()
                ->getRow();

            if (!$existingRole) {
                $this->db->table('roles')->insert($role);
            }
        }

        $adminRole = $this->db->table('roles')
            ->where('role_name', 'Admin')
            ->get()
            ->getRow();

        $existingAdmin = $this->db->table('users')
            ->where('email', 'admin@civicyouth.local')
            ->get()
            ->getRow();

        if (!$existingAdmin && $adminRole) {
            $this->db->table('users')->insert([
                'role_id'    => $adminRole->id,
                'name'       => 'CivicYouth Admin',
                'email'      => 'admin@civicyouth.local',
                'password'   => password_hash('admin123', PASSWORD_DEFAULT),
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}