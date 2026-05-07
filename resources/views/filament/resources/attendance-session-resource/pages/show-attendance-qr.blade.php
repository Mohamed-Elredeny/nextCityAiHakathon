<x-filament-panels::page>
    @php
        $checkInUrl = $this->getCheckInUrl();
        $roster = $this->getRoster();
        $isOpen = $this->record->isOpenForCheckIn();
    @endphp

    <div class="grid lg:grid-cols-2 gap-6">
        {{-- QR card --}}
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Check-in QR</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Display this QR on a screen. Participants scan it with their phone.
                    </p>
                </div>
                <span @class([
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' => $isOpen,
                    'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200' => ! $isOpen,
                ])>
                    {{ $isOpen ? 'Open for check-in' : 'Closed' }}
                </span>
            </div>

            <div class="flex flex-col items-center gap-4">
                <div id="attendance-qr"
                     class="p-4 bg-white rounded-lg shadow-inner"
                     data-url="{{ $checkInUrl }}"></div>

                <div class="w-full">
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Direct link</label>
                    <div class="flex items-center gap-2 mt-1">
                        <input
                            type="text"
                            readonly
                            value="{{ $checkInUrl }}"
                            class="flex-1 text-xs font-mono px-2 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200"
                            onclick="this.select()"
                        />
                        <button
                            type="button"
                            class="text-xs px-2 py-1.5 rounded bg-primary-600 text-white hover:bg-primary-700"
                            onclick="navigator.clipboard.writeText('{{ $checkInUrl }}')"
                        >Copy</button>
                    </div>
                </div>

                <a
                    href="{{ $checkInUrl }}"
                    target="_blank"
                    class="text-xs text-primary-600 dark:text-primary-400 hover:underline"
                >Open check-in page in new tab →</a>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700 grid grid-cols-2 gap-3 text-sm">
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Type</div>
                    <div class="font-medium">{{ \App\Models\AttendanceSession::TYPES[$this->record->type] ?? $this->record->type }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Total checked-in</div>
                    <div class="font-semibold text-primary-600">{{ count($roster) }}</div>
                </div>
                @if ($this->record->starts_at)
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Opens</div>
                        <div>{{ $this->record->starts_at->format('M d, H:i') }}</div>
                    </div>
                @endif
                @if ($this->record->ends_at)
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Closes</div>
                        <div>{{ $this->record->ends_at->format('M d, H:i') }}</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Roster --}}
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Live Roster
                </h3>
                <button
                    type="button"
                    onclick="window.location.reload()"
                    class="text-xs px-2 py-1 rounded border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700"
                >Refresh</button>
            </div>

            @if (count($roster) === 0)
                <div class="text-sm italic text-gray-500 dark:text-gray-400 py-8 text-center">
                    No check-ins yet.
                </div>
            @else
                <div class="max-h-[480px] overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($roster as $att)
                        <div class="flex items-center gap-3 py-2">
                            <div class="w-9 h-9 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                @if ($att->user?->avatar_path)
                                    <img src="{{ $att->user->avatar_url }}" class="w-full h-full object-cover" alt="">
                                @else
                                    <span class="text-xs font-semibold text-gray-500">
                                        {{ $att->user?->initials ?? '?' }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                    {{ $att->user?->name ?? '—' }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                    {{ $att->user?->institution ?? '—' }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-700 dark:text-gray-300">
                                    {{ $att->checked_in_at?->format('H:i') }}
                                </div>
                                <div class="text-[10px] uppercase tracking-wide text-gray-400">
                                    {{ $att->source }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        (function () {
            var el = document.getElementById('attendance-qr');
            if (!el) return;
            var url = el.dataset.url;
            new QRCode(el, {
                text: url,
                width: 256,
                height: 256,
                correctLevel: QRCode.CorrectLevel.M
            });
        })();
    </script>
</x-filament-panels::page>
