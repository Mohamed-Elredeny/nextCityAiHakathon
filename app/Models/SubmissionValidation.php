<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionValidation extends Model
{
    protected $fillable = ['submission_id', 'check_key', 'status', 'message', 'checked_at'];

    protected $casts = [
        'checked_at' => 'datetime',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }
}
