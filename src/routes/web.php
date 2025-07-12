<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MailTestController; // メール認証用

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//登録画面の表示
Route::get('/register', [AuthController::class, 'user'])->name('auth.register');

// 登録ボタンで登録内容を保存
Route::post('/register',[AuthController::class,'store'])->name('register');

//ログイン画面の表示
Route::get('/login',[AuthController::class,'showLoginForm'])->middleware('guest')->name('auth.login');

//ログイン処理
Route::post('/login',[AuthController::class,'login'])->middleware(['guest'])->name('login');

// メール認証画面
Route::get('/email/verify',function(){
    return view('auth.verify');
})->middleware('auth')->name('verification.notice');

// メール認証リンクからのアクセス
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // メール確認済み状態にする
    return redirect('/attendance');
})->middleware(['auth', 'signed'])->name('verification.verify');

// メール再送信処理
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '確認リンクを再送信しました。');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// ログイン必須画面
Route::middleware('auth','verified')->group(function () {
    // 勤怠登録画面表示
    Route::get('/attendance',[UserController::class,'attendance'])->name('user.attendance');

    //勤怠一覧表示
    Route::get('/attendance/list',[UserController::class,'index'])->name('user.attendanceList');

});

//ログイン必須に入れる
// 勤怠詳細表示 パスにattendance_id入れる
Route::get('/attendance/detail/',[UserController::class,'show'])->name('attendanceDetail.show');

// ログアウト
Route::middleware('auth')->group(function () {
    Route::post('/logout',[AuthController::class,'logout'])->name('logout');
});