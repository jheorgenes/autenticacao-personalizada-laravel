<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // add 3 users to the database
        for ($i = 1; $i <= 3; $i++) {
            User::create([
                'username' => "user$i",
                'email' => "user$i@gmail.com",
                'password' => bcrypt('Aa123456'),
                'email_verified_at' => Carbon::now(),
                'active' => true
            ]);
        }
    }
}
