<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => '田中 太郎',
            'email' => 'test1@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);

        User::create([
            'name' => '山田 花子',
            'email' => 'test2@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);
    }
}
