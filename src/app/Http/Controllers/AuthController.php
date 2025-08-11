<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Admin; 
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;

class AuthController extends Controller
{
    // 会員登録画面表示
    public function user(){
        return view('auth.register');
    }

    // 会員登録ボタン→メール認証→勤怠登録画面
    public function store(RegisterRequest $request){
        $data = $request->only(['name', 'email', 'password']);
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        // メール認証用のメール送信
        $user->sendEmailVerificationNotification();

        // ログイン
        Auth::login($user);

        if (!Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }
        return redirect('/attendance');
    }

    // ログイン画面の表示(一般ユーザー)
    public function showLoginForm(){
        return view('auth.login');
    }


    //ログインボタン→勤怠登録画面へ
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

    
    // ログイン画面の表示(管理者)
    public function showAdminLoginForm(){
        return view('auth.adminLogin');
    }

    public function adminLogin(LoginRequest $request){
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials) && Auth::user()->isAdmin) {
            return redirect()->intended('/admin/attendance/list');
        } else {
            return back()->withErrors(['login' => 'ログイン情報が登録されていません'])->withInput();
        }
    }


    //ログアウト
    public function logout(Request $request){
        Auth::logout();
       
        $request->session()->invalidate(); // セッションの全データ削除
        $request->session()->regenerateToken(); // CSRFトークンの再発行

        //ログアウト前が管理者だった場合
        if($request->is('admin/*')){
            return redirect()->route('auth.adminLogin');
        }

        //ログアウト前が一般ユーザーだった場合
        return redirect()->route('auth.login');
    }
}
