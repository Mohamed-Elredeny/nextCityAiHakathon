<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Submission extends Model
{
    protected $fillable = [
        'team_id', 'round', 'report_pdf_path', 'slides_url', 'repo_url',
        'video_url', 'ai_disclosure_text', 'status', 'submitted_at',
        'submitted_by', 'reject_reason',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_VALIDATED = 'validated';
    public const STATUS_FLAGGED = 'flagged';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    public const ROUND_ONE = 'round1';
    public const ROUND_FINALS = 'finals';

    public const ROUNDS = [
        self::ROUND_ONE => 'Round 1',
        self::ROUND_FINALS => 'Finals',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function validations(): HasMany
    {
        return $this->hasMany(SubmissionValidation::class);
    }

    public function isComplete(): bool
    {
        return filled($this->report_pdf_path)
            && filled($this->slides_url)
            && filled($this->repo_url)
            && filled($this->video_url);
    }
}
