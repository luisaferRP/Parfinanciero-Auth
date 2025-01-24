<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insertOrIgnore([
            [
                'name' => 'Parfinanciero',
                'last_name' => 'Riwi',
                'email' => 'autha539@gmail.com',
                'password' => Hash::make('password'),
                'auth_provider' =>'Local' ,
                'role_id' => 1, 
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
