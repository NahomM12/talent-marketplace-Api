<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class SuperadminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'superadmin@gm-bridge.test'],
            [
                'name' => 'Super Admin',
                'password' => 'placeholder-superadmin-123',
                'role' => Admin::ROLE_SUPERADMIN,
            ]
        );

        Admin::updateOrCreate(
            ['email' => 'admin@gm-bridge.test'],
            [
                'name' => 'Admin User',
                'password' => 'placeholder-admin-123',
                'role' => Admin::ROLE_ADMIN,
            ]
        );
    }
}
