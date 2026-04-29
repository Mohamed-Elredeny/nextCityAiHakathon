<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamWorkspaceDraft extends Model
{
    protected $fillable = ['team_id', 'section_key', 'body', 'updated_by'];

    public const SECTIONS = [
        'cover' => 'Cover Page',
        'problem' => 'Problem Statement',
        'solution' => 'Proposed Solution',
        'architecture' => 'Technical Architecture',
        'impact' => 'Impact & Scalability',
        'roles' => 'Team Roles',
        'references' => 'References & Resources',
        'ai_disclosure' => 'AI Tools Disclosure',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
