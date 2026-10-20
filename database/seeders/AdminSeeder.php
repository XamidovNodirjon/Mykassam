<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Default administrator: login "admin", password "admin123".
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['login' => 'admin'],
            [
                'first_name' => 'Admin',
                'last_name' => 'Tizim',
                'phone' => null,
                'password' => 'admin123',
                'role' => UserRole::Admin,
                'is_active' => true,
            ],
        );
    }
}
