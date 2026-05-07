<div class="min-h-screen bg-gradient-to-br from-aiu-blue-50 to-white py-12 px-4">
    <div class="max-w-md mx-auto">
        <div class="text-center mb-6">
            <img src="{{ asset('img/aec-logo.png') }}" alt="ACIE" class="h-14 mx-auto mb-3">
            <h1 class="text-2xl font-heading font-bold text-gray-900">Attendance Check-in</h1>
        </div>

        @if (! $session)
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-red-100">
                <div class="text-red-600 font-semibold mb-2">Invalid link</div>
                <p class="text-sm text-gray-600">{{ $error ?? 'This check-in link is not valid.' }}</p>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="border-b border-gray-100 pb-4 mb-4">
                    <div class="text-xs uppercase tracking-wide text-gray-500">Session</div>
                    <div class="font-semibold text-lg text-gray-900">{{ $session->name }}</div>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ \App\Models\AttendanceSession::TYPES[$session->type] ?? $session->type }}
                        @if ($session->starts_at)
                            · {{ $session->starts_at->format('M d, H:i') }}
                        @endif
                    </div>
                </div>

                @auth
                    @php $user = auth()->user(); @endphp

                    <div class="flex items-center gap-3 mb-5 p-3 rounded-lg bg-gray-50">
                        <div class="w-12 h-12 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
                            @if ($user->avatar_path)
                                <img src="{{ $user->avatar_url }}" alt="" class="w-full h-full object-cover">
                            @else
                                <span class="text-sm font-semibold text-gray-500">{{ $user->initials }}</span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-gray-900 truncate">{{ $user->name }}</div>
                            <div class="text-xs text-gray-500 truncate">{{ $user->email }}</div>
                        </div>
                    </div>

                    @if ($existing)
                        <div class="rounded-lg bg-green-50 border border-green-200 p-4 text-center">
                            <div class="text-3xl mb-1">✓</div>
                            <div class="font-semibold text-green-800">You're checked in</div>
                            <div class="text-xs text-green-700 mt-1">
                                {{ $existing->checked_in_at?->format('M d, H:i') }}
                            </div>
                        </div>
                    @elseif (! empty($missingFields))
                        <div class="rounded-lg bg-amber-50 border border-amber-200 p-4">
                            <div class="font-semibold text-amber-900 mb-2">Profile incomplete</div>
                            <p class="text-sm text-amber-800 mb-3">
                                You must finish your profile and upload a photo before you can check in.
                            </p>
                            <ul class="text-sm text-amber-900 list-disc list-inside mb-4 space-y-0.5">
                                @foreach ($missingFields as $label)
                                    <li>{{ $label }}</li>
                                @endforeach
                            </ul>
                            <a
                                href="{{ route('profile') }}"
                                class="inline-flex items-center justify-center w-full px-4 py-2.5 rounded-lg bg-aiu-red text-white text-sm font-semibold hover:bg-red-700 transition"
                            >
                                Complete profile →
                            </a>
                        </div>
                    @elseif (! $session->isOpenForCheckIn())
                        <div class="rounded-lg bg-gray-100 border border-gray-200 p-4 text-center">
                            <div class="font-semibold text-gray-700">Check-in is closed</div>
                            @if ($session->starts_at && now()->lt($session->starts_at))
                                <div class="text-xs text-gray-500 mt-1">Opens {{ $session->starts_at->format('M d, H:i') }}</div>
                            @elseif ($session->ends_at && now()->gt($session->ends_at))
                                <div class="text-xs text-gray-500 mt-1">Closed at {{ $session->ends_at->format('M d, H:i') }}</div>
                            @endif
                        </div>
                    @else
                        @if ($error)
                            <div class="rounded-lg bg-red-50 border border-red-200 p-3 mb-3 text-sm text-red-800">
                                {{ $error }}
                            </div>
                        @endif

                        <button
                            wire:click="checkIn"
                            wire:loading.attr="disabled"
                            class="w-full px-4 py-3 rounded-lg bg-aiu-red text-white text-base font-semibold hover:bg-red-700 disabled:opacity-60 transition"
                        >
                            <span wire:loading.remove wire:target="checkIn">✓ Check me in</span>
                            <span wire:loading wire:target="checkIn">Checking in…</span>
                        </button>

                        <p class="text-xs text-gray-500 text-center mt-3">
                            One check-in per device per session.
                        </p>
                    @endif

                    <div class="mt-5 pt-4 border-t border-gray-100 text-center">
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-xs text-gray-500 hover:text-gray-700 underline">
                                Sign out
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
        @endif
    </div>
</div>
