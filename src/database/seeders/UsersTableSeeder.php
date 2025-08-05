<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //一般ユーザーの作成
        User::factory()->count(3)->create();

        // 管理者の作成
        User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'isAdmin' => 1,
            'email_verified_at' => now(),
            'password' => Hash::make('admin'), 
        ]);
    }
}
