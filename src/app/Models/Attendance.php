<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable=[
        'id',
        'user_id',
        'date',
        'start_time',
        'finish_time',
        'attendance_total',
        'status_id',
        'remarks'
    ];

    public function breakTimes(){
        return $this->hasMany(BreakTime::class);
    }
}
