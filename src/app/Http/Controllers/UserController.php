<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function attendance(){
        return view('attendance');
    }
}
