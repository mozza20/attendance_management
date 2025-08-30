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
        //固定ユーザーの作成(メール認証が必要)
        User::create([
            'name' => '山田 太郎',
            'email' => 'testuser@example.com',
            'isAdmin' => 0,
            'password' => Hash::make('password1234'), 
        ]);

         //一般ユーザーの作成
        User::factory()->count(9)->create();

        // 管理者の作成
        User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'isAdmin' => 1,
            'email_verified_at' => now(),
            'password' => Hash::make('admin2345'), 
        ]);
    }
}
