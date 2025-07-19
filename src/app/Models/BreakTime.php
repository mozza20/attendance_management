<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    protected $fillable=[
        'id',
        "attendance_id",
        "start_time",
        "end_time",
        "break_total",
    ];

    public function attendance(){
        return $this->belongsTo(Attendance::class);
    }
}
