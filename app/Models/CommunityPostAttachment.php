<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityPostAttachment extends Model
{
    protected $fillable = [
        'community_post_id', 'path', 'original_name', 'mime_type', 'size', 'kind',
    ];

    public const KIND_IMAGE = 'image';
    public const KIND_VIDEO = 'video';
    public const KIND_AUDIO = 'audio';
    public const KIND_PDF = 'pdf';
    public const KIND_FILE = 'file';

    public function post(): BelongsTo
    {
        return $this->belongsTo(CommunityPost::class, 'community_post_id');
    }

    public function getUrlAttribute(): ?string
    {
        return $this->path ? asset('storage/' . $this->path) : null;
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = (int) $this->size;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1024 * 1024) return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1024 * 1024 * 1024) return round($bytes / (1024 * 1024), 1) . ' MB';
        return round($bytes / (1024 * 1024 * 1024), 1) . ' GB';
    }

    public static function kindFromMime(string $mime): string
    {
        if (str_starts_with($mime, 'image/')) return self::KIND_IMAGE;
        if (str_starts_with($mime, 'video/')) return self::KIND_VIDEO;
        if (str_starts_with($mime, 'audio/')) return self::KIND_AUDIO;
        if ($mime === 'application/pdf') return self::KIND_PDF;
        return self::KIND_FILE;
    }
}
