<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Default administrator: login "developer", password "password". Change the password after
     * the first sign-in on a live server.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['login' => 'developer'],
            [
                'first_name' => 'Developer',
                'last_name' => null,
                'phone' => null,
                'password' => 'password',
                'role' => UserRole::Admin,
                'is_active' => true,
            ],
        );
    }
}
