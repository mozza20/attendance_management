<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    //一般ユーザー_ログイン機能---------------------------
    public function test_login_user(){
        $user = User::factory()->create([
            'email' => "test@example.com",
            'password' =>bcrypt('password1234'),
        ]);

        $response = $this->post('/login', [
            'email' => "test@example.com",
            'password' => "password1234",
        ]);

        $response->assertRedirect('/attendance');
        $this->assertAuthenticatedAs($user);
    }

    //バリデーション_email
    public function test_login_user_validate_email(){
        $response = $this->post('/login', [
            'email' => "",
            'password' => "password1234",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    //バリデーション_password
    public function test_login_user_validate_password(){
        $response = $this->post('/login', [
            'email' => "test@example.com",
            'password' => "",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }

    //バリデーション_password不一致
    public function test_login_user_validate_user(){
        $response = $this->post('/login', [
            'email' => "test@example.com",
            'password' => "password2345",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('login');

        $errors = session('errors');
        $this->assertEquals('ログイン情報が登録されていません', $errors->first('login'));
    }

    //管理者_ログイン機能---------------------------
    public function test_login_admin(){
        $admin = User::factory()->admin()->create([
            'email' => "admintest@example.com",
            'password' =>bcrypt('admin2345'),
        ]);

        $this->withSession(['url.intended' => '/admin/attendance/list']);

        $response = $this->post('/admin/login', [
            'email' => "admintest@example.com",
            'password' => "admin2345",
        ]);

        $response->assertRedirect('/admin/attendance/list');
        $this->assertAuthenticatedAs($admin);
    }

    //バリデーション_email
    public function test_login_admin_validate_email(){
        $response = $this->post('/admin/login', [
            
            'email' => "",
            'password' => "admin2345",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    //バリデーション_password
    public function test_login_admin_validate_password(){
        $response = $this->post('/admin/login', [
            
            'email' => "admintest@example.com",
            'password' => "",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }

    //バリデーション_password不一致
    public function test_login_admin_validate_user(){
        $response = $this->post('/admin/login', [
            
            'email' => "admintest@example.com",
            'password' => "admin234",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('login');

        $errors = session('errors');
        $this->assertEquals('ログイン情報が登録されていません', $errors->first('login'));
    }
}
