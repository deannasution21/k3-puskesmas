<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DinasAdminSeeder extends Seeder
{
    /**
     * Akun awal Dinas Kesehatan (superadmin). Password wajib diganti setelah login pertama.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admindinas'],
            [
                'password' => Hash::make('dinas123'),
                'role' => 'dinas',
                'puskesmas_id' => null,
            ],
        );
    }
}
