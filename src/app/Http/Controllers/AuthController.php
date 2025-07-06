<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    // 会員登録画面表示
    public function user(){
        return view('auth.register');
    }

    // 会員登録ボタン→プロフィール設定画面へ
    public function store(RegisterRequest $request){
        $data = $request->only(['name', 'email', 'password']);
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        // メール認証用のメール送信
        $user->sendEmailVerificationNotification();

        // ログイン
        Auth::login($user);
        return redirect('/attendance');
    }


    // ログイン画面の表示
    public function showLoginForm(){
        return view('auth.login');
    }

    //ログインボタン→トップページへ
    public function login(LoginRequest $request){
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            //セキュリティ強化
            $request->session()->regenerate(); 

            if(!Auth::user()->hasVerifiedEmail()){
                $user = Auth::user();
                // メール認証用のメール送信
                $user->sendEmailVerificationNotification();
                return redirect()->route('verification.notice');
            }
            return redirect()->intended('/attendance');
        }

        return back()->withErrors([
            'login' => 'ログイン情報が登録されていません',
        ])->withInput();
    }
}
