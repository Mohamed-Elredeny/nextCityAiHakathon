<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class VoterIpBlock extends Model
{
    /** Max vote attempts (success OR rejection) per IP within the window. */
    public const ATTEMPT_LIMIT = 3;

    /** Sliding window over which attempts are counted. */
    public const ATTEMPT_WINDOW_HOURS = 1;

    /** Once the limit is hit, the IP is blocked for this many hours. */
    public const BLOCK_DURATION_HOURS = 24;

    protected $fillable = [
        'ip_address', 'attempt_count',
        'first_attempt_at', 'last_attempt_at',
        'blocked_until', 'reason', 'last_user_agent',
    ];

    protected $casts = [
        'first_attempt_at' => 'datetime',
        'last_attempt_at' => 'datetime',
        'blocked_until' => 'datetime',
    ];

    public function isBlocked(): bool
    {
        return $this->blocked_until !== null && $this->blocked_until->isFuture();
    }

    /**
     * Atomically record a vote attempt from this IP. Resets the counter
     * when the rolling one-hour window has elapsed; auto-blocks the IP
     * when the threshold is reached.
     */
    public static function recordAttempt(string $ip, ?string $userAgent = null): self
    {
        $row = static::firstOrNew(['ip_address' => $ip]);
        $now = Carbon::now();

        $windowStart = $now->copy()->subHours(self::ATTEMPT_WINDOW_HOURS);
        $insideWindow = $row->last_attempt_at && $row->last_attempt_at->gte($windowStart);

        if (! $insideWindow) {
            $row->attempt_count = 1;
            $row->first_attempt_at = $now;
        } else {
            $row->attempt_count = ($row->attempt_count ?? 0) + 1;
        }

        $row->last_attempt_at = $now;
        $row->last_user_agent = $userAgent ? mb_substr($userAgent, 0, 500) : $row->last_user_agent;

        if ($row->attempt_count >= self::ATTEMPT_LIMIT && ! $row->isBlocked()) {
            $row->blocked_until = $now->copy()->addHours(self::BLOCK_DURATION_HOURS);
            $row->reason = 'Auto-blocked: ' . $row->attempt_count . ' attempts in '
                . self::ATTEMPT_WINDOW_HOURS . 'h';
        }

        $row->save();
        return $row;
    }

    /**
     * Quick lookup — returns the row when the IP is currently blocked,
     * null otherwise. Lazy-prunes expired blocks.
     */
    public static function activeBlockFor(string $ip): ?self
    {
        $row = static::where('ip_address', $ip)->first();
        if (! $row) {
            return null;
        }
        return $row->isBlocked() ? $row : null;
    }
}
