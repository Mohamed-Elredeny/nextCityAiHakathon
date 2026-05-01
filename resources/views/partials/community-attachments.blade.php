@php
    $compact = $compact ?? false;
@endphp
@if (isset($attachments) && count($attachments))
    @php
        $images = collect($attachments)->where('kind', 'image');
        $videos = collect($attachments)->where('kind', 'video');
        $audios = collect($attachments)->where('kind', 'audio');
        $files = collect($attachments)->whereNotIn('kind', ['image', 'video', 'audio']);
    @endphp

    @if ($images->count())
        <div class="mt-3 grid gap-2 {{ $images->count() === 1 ? 'grid-cols-1' : ($images->count() === 2 ? 'grid-cols-2' : 'grid-cols-2 sm:grid-cols-3') }}">
            @foreach ($images as $img)
                <a href="{{ $img->url }}" target="_blank" rel="noopener" class="block rounded-xl overflow-hidden border border-aiu-line bg-white group">
                    <img src="{{ $img->url }}" alt="{{ $img->original_name }}"
                         class="w-full {{ $compact ? 'max-h-64' : 'max-h-96' }} object-cover group-hover:opacity-95 transition" loading="lazy">
                </a>
            @endforeach
        </div>
    @endif

    @foreach ($videos as $video)
        <div class="mt-3 rounded-xl overflow-hidden border border-aiu-line bg-black">
            <video controls preload="metadata" class="w-full {{ $compact ? 'max-h-64' : 'max-h-[60vh]' }}">
                <source src="{{ $video->url }}" type="{{ $video->mime_type }}">
                Your browser doesn't support video playback.
            </video>
        </div>
    @endforeach

    @foreach ($audios as $audio)
        <div class="mt-3 surface-soft rounded-xl px-4 py-3">
            <p class="text-xs font-semibold text-aiu-ink-700 mb-1.5 truncate">{{ $audio->original_name }}</p>
            <audio controls preload="metadata" class="w-full">
                <source src="{{ $audio->url }}" type="{{ $audio->mime_type }}">
            </audio>
        </div>
    @endforeach

    @if ($files->count())
        <div class="mt-3 space-y-2">
            @foreach ($files as $file)
                <a href="{{ $file->url }}" target="_blank" rel="noopener"
                   class="flex items-center gap-3 surface-soft rounded-xl px-3 py-2.5 hover:ring-1 hover:ring-aiu-red/30 transition">
                    <span class="shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-lg
                                 {{ $file->kind === 'pdf' ? 'bg-aiu-red-50 text-aiu-red' : 'bg-aiu-ink-100 text-aiu-ink-700' }}">
                        @if ($file->kind === 'pdf')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                        @endif
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-aiu-ink-900 truncate">{{ $file->original_name }}</p>
                        <p class="text-[11px] text-aiu-ink-500">{{ strtoupper($file->kind) }} · {{ $file->human_size }}</p>
                    </div>
                    <svg class="w-4 h-4 text-aiu-ink-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                </a>
            @endforeach
        </div>
    @endif
@endif
