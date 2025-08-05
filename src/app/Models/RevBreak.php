<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevBreak extends Model
{
    use HasFactory;

    protected $fillable=[
        'id',
        'attendance_id',
        'break_time_id',
        'rev_start_time',
        'rev_end_time',
        'rev_break_total'
    ];

    public function revData(){
        return $this->belongsTo(Attendance::class);
    }

    public function attendance() {
        return $this->belongsTo(Attendance::class);
    }

}
