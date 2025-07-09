<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Hash;
use App\Models\User;

use DateTime;

class UserController extends Controller
{
    public function attendance(){
        $now=new DateTime();
        $date=$now->format('Y年n月j日');
        $time=$now->format('H:i');
        return view('user.attendance',compact('date','time'));
    }

    public function index(){
        return view('user.attendanceList');
    }
}
