@php
    $data = $notification->data ?? [];
    $type = $data['type'] ?? 'unknown';
    $isUnread = !$notification->read_at;
    $compact = $compact ?? false;

    $actorName = $data['actor_name'] ?? 'Someone';
    $postTitle = $data['post_title'] ?? null;
    $teamName = $data['team_name'] ?? null;

    $iconColor = 'bg-aiu-ink-100 text-aiu-ink-700';
    $icon = 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9';

    $title = '';
    $subtitle = '';
    $href = null;

    switch ($type) {
        case 'post_mention':
            $iconColor = 'bg-aiu-red-50 text-aiu-red';
            $icon = 'M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z';
            $title = $actorName . ' mentioned you in a post';
            $subtitle = $postTitle;
            $href = isset($data['post_id']) ? route('community.show', $data['post_id']) : null;
            break;
        case 'post_team_mention':
            $iconColor = 'bg-aiu-gold-50 text-aiu-gold-600';
            $icon = 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z';
            $title = $actorName . ' mentioned your team' . ($teamName ? ' (' . $teamName . ')' : '');
            $subtitle = $postTitle;
            $href = isset($data['post_id']) ? route('community.show', $data['post_id']) : null;
            break;
        case 'comment_mention':
            $iconColor = 'bg-aiu-red-50 text-aiu-red';
            $icon = 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z';
            $title = $actorName . ' mentioned you in a comment';
            $subtitle = $postTitle;
            $href = isset($data['post_id']) ? route('community.show', $data['post_id']) : null;
            break;
        case 'comment_team_mention':
            $iconColor = 'bg-aiu-gold-50 text-aiu-gold-600';
            $icon = 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z';
            $title = $actorName . ' mentioned your team in a comment';
            $subtitle = $postTitle;
            $href = isset($data['post_id']) ? route('community.show', $data['post_id']) : null;
            break;
        case 'post_comment':
            $iconColor = 'bg-emerald-50 text-emerald-600';
            $icon = 'M3 5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-5l-5 5v-5H5a2 2 0 01-2-2V5z';
            $title = $actorName . ' commented on your post';
            $subtitle = $postTitle;
            $href = isset($data['post_id']) ? route('community.show', $data['post_id']) : null;
            break;
        case 'thread_reply':
            $iconColor = 'bg-aiu-navy-50 text-aiu-navy';
            $icon = 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z';
            $title = $actorName . ' replied to a thread you joined';
            $subtitle = $postTitle;
            $href = isset($data['post_id']) ? route('community.show', $data['post_id']) : null;
            break;
        case 'post_like':
            $iconColor = 'bg-aiu-red-50 text-aiu-red';
            $icon = 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z';
            $title = $actorName . ' liked your post';
            $subtitle = $postTitle;
            $href = isset($data['post_id']) ? route('community.show', $data['post_id']) : null;
            break;
        case 'application_received':
            $iconColor = 'bg-aiu-red-50 text-aiu-red';
            $icon = 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z';
            $title = $actorName . ' applied to join your team';
            $subtitle = $teamName;
            $href = route('workspace');
            break;
        case 'application_approved':
            $iconColor = 'bg-emerald-50 text-emerald-600';
            $icon = 'M5 13l4 4L19 7';
            $title = 'Your application was approved!';
            $subtitle = $teamName ? 'Welcome to ' . $teamName : null;
            $href = route('workspace');
            break;
        case 'application_rejected':
            $iconColor = 'bg-aiu-ink-100 text-aiu-ink-700';
            $icon = 'M6 18L18 6M6 6l12 12';
            $title = 'Application not accepted';
            $subtitle = $teamName ? 'For ' . $teamName : null;
            if (!empty($data['response_message'])) {
                $subtitle = '"' . $data['response_message'] . '"';
            }
            $href = route('community.teams');
            break;
        default:
            $title = 'Notification';
            $subtitle = null;
    }
@endphp

<a @if ($href) href="{{ $href }}" wire:click="markAsRead('{{ $notification->id }}')" @endif
   class="block px-4 py-3 transition relative {{ $isUnread ? 'bg-aiu-red-50/40 hover:bg-aiu-red-50' : 'hover:bg-aiu-ink-50' }} {{ $compact ? '' : 'border-b border-aiu-line last:border-b-0' }}">
    <div class="flex items-start gap-3">
        <span class="shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-xl {{ $iconColor }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
            </svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-xs sm:text-sm font-semibold text-aiu-ink-900 leading-snug">
                {{ $title }}
                @if ($isUnread)
                    <span class="ml-1 inline-block w-1.5 h-1.5 rounded-full bg-aiu-red align-middle"></span>
                @endif
            </p>
            @if ($subtitle)
                <p class="mt-0.5 text-[11px] sm:text-xs text-aiu-ink-600 truncate">{{ $subtitle }}</p>
            @endif
            <p class="mt-0.5 text-[10px] text-aiu-ink-400">{{ $notification->created_at->diffForHumans() }}</p>
        </div>
    </div>
</a>
