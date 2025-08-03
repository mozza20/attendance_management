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
        'work_total',
        'status_id',
        'remarks'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function breakTimes(){
        return $this->hasMany(BreakTime::class);
    }

    public function status(){
        return $this->belongsTo(Status::class);
    }
}