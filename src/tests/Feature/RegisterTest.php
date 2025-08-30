<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    //会員情報登録
    public function test_register_user(){
        $response = $this->post('/register', [
            'name' => "テストユーザー",
            'email' => "test@example.com",
            'password' => "password",
            'password_confirmation' => "password",
        ]);

        $response->assertRedirect('/email/verify');
        $this->assertDatabaseHas(User::class, [
            'name' => "テストユーザー",
            'email' => "test@example.com",
        ]);
    }

    //バリデーション_name
    public function test_register_user_validate_name()
    {
        $response = $this->post('/register', [
            'name' => "",
            'email' => "test@example.com",
            'password' => "password1234",
            'password_confirmation' => "password1234",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');

        $errors = session('errors');
        $this->assertEquals('お名前を入力してください', $errors->first('name'));
    }

    //バリデーション_email
    public function test_register_user_validate_email(){
        $response = $this->post('/register', [
            'name' => "テストユーザー",
            'email' => "",
            'password' => "password1234",
            'password_confirmation' => "password1234",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    //バリデーション_password
    public function test_register_user_validate_password(){
        $response = $this->post('/register', [
            'name' => "テストユーザー",
            'email' => "test@example.com",
            'password' => "",
            'password_confirmation' => "password1234",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }

    //バリデーション_パスワード7文字以下
    public function test_register_user_validate_password_under7(){
        $response = $this->post('/register', [
            'name' => "テストユーザー",
            'email' => "test@example.com",
            'password' => "passwor",
            'password_confirmation' => "password1234",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードは8文字以上で入力してください', $errors->first('password'));
    }

    //バリデーション_password不一致
    public function test_register_user_validate_confirm_password(){
        $response = $this->post('/register', [
            'name' => "テストユーザ",
            'email' => "test@gmail.com",
            'password' => "password1234",
            'password_confirmation' => "password",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードと一致しません', $errors->first('password'));
    }
}