<?php

namespace App\Support;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class MentionRenderer
{
    /**
     * Convert plaintext body into HTML with @mentions linked.
     * Only resolves @tokens that match the saved mentioned IDs (no DB lookup per @token).
     *
     * @param  Collection<int, User>|null  $userMap  keyed by id
     * @param  Collection<int, Team>|null  $teamMap  keyed by id
     */
    public static function render(?string $body, ?Collection $userMap = null, ?Collection $teamMap = null): HtmlString
    {
        $body = (string) $body;
        if ($body === '') {
            return new HtmlString('');
        }

        $userMap ??= collect();
        $teamMap ??= collect();

        // Build name → entity lookups (case-insensitive, spaces collapsed)
        $userByKey = [];
        foreach ($userMap as $u) {
            foreach (self::keysForName($u->name) as $k) {
                $userByKey[$k] = $u;
            }
        }
        $teamByKey = [];
        foreach ($teamMap as $t) {
            foreach (self::keysForName($t->name) as $k) {
                $teamByKey[$k] = $t;
            }
            $teamByKey[mb_strtolower($t->slug)] = $t;
        }

        $escaped = e($body);

        // Match @token where token is letters/digits/_-./ followed-by-spaces? We'll match @ + (word|"phrase").
        // Simple approach: match @ followed by 1-3 words (allowing spaces)
        $pattern = '/@([A-Za-z0-9_][A-Za-z0-9_\-.]*(?:[ ]+[A-Za-z0-9_][A-Za-z0-9_\-.]*){0,3})/u';

        $escaped = preg_replace_callback($pattern, function ($m) use ($userByKey, $teamByKey) {
            $token = $m[1];
            // Try matching the longest prefix
            $words = preg_split('/\s+/', trim($token));
            for ($take = count($words); $take >= 1; $take--) {
                $candidate = implode(' ', array_slice($words, 0, $take));
                $key = self::normalizeKey($candidate);
                if (isset($userByKey[$key])) {
                    $u = $userByKey[$key];
                    $rest = trim(mb_substr($token, mb_strlen($candidate)));
                    return self::userLink($u, $candidate) . ($rest !== '' ? ' ' . e($rest) : '');
                }
                if (isset($teamByKey[$key])) {
                    $t = $teamByKey[$key];
                    $rest = trim(mb_substr($token, mb_strlen($candidate)));
                    return self::teamLink($t, $candidate) . ($rest !== '' ? ' ' . e($rest) : '');
                }
            }
            return '@' . e($token);
        }, $escaped);

        // Preserve newlines (since blade whitespace-pre-line works on plain text not html)
        $escaped = nl2br($escaped, false);

        return new HtmlString($escaped);
    }

    private static function userLink(User $u, string $display): string
    {
        $url = e(route('users.show', $u->id));
        return '<a href="' . $url . '" class="text-aiu-red font-semibold hover:underline">@' . e($display) . '</a>';
    }

    private static function teamLink(Team $t, string $display): string
    {
        $url = e(route('teams.show', $t->slug));
        return '<a href="' . $url . '" class="text-aiu-gold-600 font-semibold hover:underline">@' . e($display) . '</a>';
    }

    private static function keysForName(string $name): array
    {
        $keys = [];
        $full = self::normalizeKey($name);
        if ($full !== '') $keys[] = $full;

        // Also accept first name only (single word)
        $parts = preg_split('/\s+/', trim($name));
        if (is_array($parts) && count($parts) > 1) {
            $keys[] = self::normalizeKey($parts[0]);
        }
        return array_filter(array_unique($keys));
    }

    private static function normalizeKey(string $s): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $s)));
    }
}
