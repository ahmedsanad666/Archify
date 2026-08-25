<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the single CMS admin account.
     *
     * Override via .env: ADMIN_NAME, ADMIN_EMAIL, ADMIN_PASSWORD
     */
    public function run(): void
    {
        User::query()->firstOrCreate(
            [
                'email' => env('ADMIN_EMAIL', 'admin@archify.com'),
            ],
            [
                'name' => env('ADMIN_NAME', 'Archify Admin'),
                'password' => env('ADMIN_PASSWORD', 'password'),
                'email_verified_at' => now(),
            ],
        );
    }
}
