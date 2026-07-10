<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRequest extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'attendance_id', 'date', 'clock_in', 'clock_out', 'break_times', 'remarks', 'status'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
