<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan cache permission agar data baru terbaca
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Buat 3 role
        foreach (['super_admin', 'admin', 'kasir'] as $namaRole) {
            Role::firstOrCreate(['name' => $namaRole, 'guard_name' => 'web']);
        }

        // 2. Buat 3 akun contoh
        $akun = [
            ['name' => 'Super Admin', 'email' => 'superadmin@toko.test', 'role' => 'super_admin'],
            ['name' => 'Admin Toko',  'email' => 'admin@toko.test',      'role' => 'admin'],
            ['name' => 'Kasir Toko',  'email' => 'kasir@toko.test',      'role' => 'kasir'],
        ];

        foreach ($akun as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            $user->assignRole($data['role']);
        }
    }
}