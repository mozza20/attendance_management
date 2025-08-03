<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class BreakTime extends Model
{
    use HasFactory;

    protected $fillable=[
        "attendance_id",
        "start_time",
        "end_time",
        "break_total",
    ];

    public function attendance(){
        return $this->belongsTo(Attendance::class);
    }

    protected static function booted(){
        static::saving(function ($break) {
            if ($break->start_time && $break->end_time && is_null($break->break_total)) {
                $start = Carbon::parse($break->start_time);
                $end = Carbon::parse($break->end_time);
                $break->break_total = $start->diffInSeconds($end);
            }
        });
    }
}
