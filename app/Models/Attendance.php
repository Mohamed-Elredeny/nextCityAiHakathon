<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    public const SOURCE_SELF = 'self';
    public const SOURCE_ADMIN = 'admin';

    protected $fillable = [
        'attendance_session_id', 'user_id', 'checked_in_by',
        'checked_in_at', 'ip_address', 'user_agent',
        'device_fingerprint', 'source',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }
}
