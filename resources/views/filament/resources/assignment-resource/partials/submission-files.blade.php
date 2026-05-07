<div class="space-y-3">
    @if ($submission->notes)
        <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-3">
            <div class="text-xs uppercase tracking-wide text-amber-800 dark:text-amber-300 font-semibold mb-1">
                Team notes
            </div>
            <p class="text-sm text-amber-900 dark:text-amber-100 whitespace-pre-wrap">{{ $submission->notes }}</p>
        </div>
    @endif

    @if ($submission->files->isEmpty())
        <p class="text-sm italic text-gray-500">No files uploaded yet.</p>
    @else
        <div class="divide-y divide-gray-100 dark:divide-gray-700 border rounded-lg overflow-hidden">
            @foreach ($submission->files as $file)
                <div class="p-3 flex items-start gap-3 bg-white dark:bg-gray-800">
                    <div class="w-10 h-10 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <a href="{{ $file->url }}" target="_blank"
                           class="text-sm font-medium text-primary-600 hover:underline truncate block">
                            {{ $file->original_name }}
                        </a>
                        <div class="text-xs text-gray-500 mt-0.5">
                            {{ $file->human_size }}
                            · uploaded by <strong>{{ $file->uploader?->name ?? '—' }}</strong>
                            · {{ $file->created_at?->format('M d, H:i') }}
                        </div>
                        @if ($file->comment)
                            <div class="mt-2 text-xs text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/50 rounded p-2 whitespace-pre-wrap">
                                {{ $file->comment }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
