<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsUser;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CsvDownloadController;
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


//一般ユーザー

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


// 一般ユーザー_ログイン必須
Route::middleware('auth','verified','user')->group(function () {

    // 勤怠登録画面表示
    Route::get('/attendance',[UserController::class,'attendance'])->name('attendance.index');

    //勤怠登録
    Route::post('/attendance',[UserController::class,'input'])->name('attendance.input');

    //勤怠一覧表示
    Route::get('/attendance/list',[UserController::class,'index'])->name('attendanceList');

    //勤怠修正申請
    Route::post('/attendance/detail/confirm/{attendance_id}',[UserController::class,'edit'])->name('attendanceDetail.confirm');
    
});


//管理者

//ログイン画面の表示
Route::get('/admin/login',[AuthController::class,'showAdminLoginForm'])->middleware('guest')->name('auth.adminLogin');

//ログイン処理
Route::post('/admin/login',[AuthController::class,'adminLogin'])->middleware(['guest'])->name('adminLogin');

//管理者_ログイン必須
Route::middleware(['auth', 'verified', 'admin'])->group(function () {

    //勤怠一覧表示(日次)
    Route::get('/admin/attendance/list', [AdminController::class, 'index'])->name('dailyAttendanceList');

    //スタッフ一覧表示
    Route::get('/admin/users',[AdminController::class,'staffIndex'])->name('staffList');

    //勤怠一覧表示(スタッフ別)
    Route::get('/admin/users/{user_id}/attendances',[AdminController::class,'staffShow'])->name('user.attendanceList');

    //CSVダウンロード
    Route::get('/csv_download/{user_id}',[CsvDownloadController::class,'downloadCsv'])->name('downloadCsv');
});


//一般・管理者共通_ログイン必須
Route::middleware(['auth', 'verified'])->group(function () {
    
    // 勤怠詳細表示
    Route::get('/attendance/detail/{attendance_id}',[AttendanceController::class,'show'])->name('attendanceDetail.show');
    
    //申請一覧表示
    Route::get('/stamp_correction_request/list',[AttendanceController::class,'showRequest'])->name('requestLists');

});


// ログアウト
Route::middleware('auth')->group(function () {
    Route::post('/logout',[AuthController::class,'logout'])->name('logout');
});