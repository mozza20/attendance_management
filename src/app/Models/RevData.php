<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevData extends Model
{
    use HasFactory;

    protected $fillable=[
        'id',
        'attendance_id',
        'rev_start_time',
        'rev_finish_time',
        'rev_work_total',
        'remarks'
    ];

    public function revAttendance(){
        return $this->belongsTo(Attendance::class);
    }
}
