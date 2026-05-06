<div class="max-w-md mx-auto px-6 py-16">
    <div class="card-3d rounded-2xl p-8">
        <div class="text-center mb-7">
            <div class="logo-plate inline-flex items-center justify-center h-20 rounded-2xl mb-5 px-5 py-3">
                <img src="{{ asset('img/aec-logo.png') }}" alt="AIU · ACIE" class="h-full w-auto object-contain">
            </div>
            <p class="text-aiu-red uppercase tracking-[0.28em] text-[11px] font-bold mb-2">Reset Password</p>
            <h1 class="font-heading font-bold text-3xl text-aiu-ink-900">Forgot your password?</h1>
            <p class="mt-2 text-sm text-aiu-ink-600">
                Confirm your email and phone to set a new one.
            </p>
        </div>

        @if ($resetDone)
            <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-5 text-center">
                <h2 class="font-heading font-bold text-aiu-ink-900 mb-1">Password updated</h2>
                <p class="text-sm text-aiu-ink-700">
                    You can now sign in with your new password.
                </p>
                <a href="{{ route('login') }}" class="inline-block mt-5 px-4 py-2 rounded-lg btn-aiu text-sm font-semibold uppercase tracking-wider">
                    Go to sign in
                </a>
            </div>

        @elseif ($stage === 'unverified')
            <div class="rounded-xl border border-aiu-gold-300 bg-aiu-gold-50 p-5 text-center">
                <h2 class="font-heading font-bold text-aiu-ink-900 mb-1">We couldn't verify those details</h2>
                <p class="text-sm text-aiu-ink-700">
                    The email and phone you entered don't match an active account.
                    Please contact us on WhatsApp and we'll help you recover access.
                </p>

                <a href="https://wa.me/{{ \App\Livewire\ForgotPassword::SUPPORT_PHONE_WHATSAPP }}"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center justify-center gap-2 mt-5 px-5 py-3 rounded-lg btn-aiu text-sm font-semibold uppercase tracking-wider">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4">
                        <path d="M19.05 4.91A10 10 0 0 0 4.1 18.36L3 22l3.74-1.08A10 10 0 1 0 19.05 4.91Zm-7.05 16a8.13 8.13 0 0 1-4.16-1.14l-.3-.18-2.22.65.66-2.16-.2-.32a8.18 8.18 0 1 1 6.22 3.15Zm4.6-6.13c-.25-.13-1.49-.74-1.72-.82-.23-.08-.4-.13-.57.13-.17.25-.65.82-.8.99-.15.17-.3.19-.55.06a6.7 6.7 0 0 1-1.97-1.22 7.4 7.4 0 0 1-1.36-1.7c-.14-.25 0-.39.11-.51.11-.11.25-.3.37-.45.12-.15.16-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.57-1.37-.77-1.87-.2-.49-.41-.42-.57-.43H8.5a1.1 1.1 0 0 0-.79.37 3.34 3.34 0 0 0-1.04 2.48 5.79 5.79 0 0 0 1.21 3.07 13.21 13.21 0 0 0 5.05 4.46c.7.3 1.25.49 1.68.62a4 4 0 0 0 1.85.12 3.07 3.07 0 0 0 2.02-1.42 2.5 2.5 0 0 0 .17-1.42c-.07-.13-.24-.2-.49-.32Z"/>
                    </svg>
                    Contact on WhatsApp · {{ \App\Livewire\ForgotPassword::SUPPORT_PHONE_DISPLAY }}
                </a>

                <button wire:click="startOver" type="button"
                        class="block w-full mt-3 text-xs text-aiu-ink-600 hover:underline">
                    Try again
                </button>
            </div>

        @elseif ($stage === 'reset')
            <form wire:submit="updatePassword" class="space-y-5">
                <p class="text-center text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-2.5">
                    Identity verified. Choose a new password.
                </p>

                <div>
                    <label class="block text-xs uppercase tracking-wider text-aiu-ink-400 font-bold mb-2">New password</label>
                    <input type="password" wire:model="password" required class="input-3d w-full px-4 py-3 rounded-lg">
                    @error('password') <p class="mt-2 text-xs text-aiu-red">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs uppercase tracking-wider text-aiu-ink-400 font-bold mb-2">Confirm password</label>
                    <input type="password" wire:model="password_confirmation" required class="input-3d w-full px-4 py-3 rounded-lg">
                </div>

                <button type="submit" class="btn-aiu w-full py-3 rounded-lg font-bold uppercase tracking-wider text-sm">
                    Update password
                </button>

                <button wire:click="startOver" type="button"
                        class="block w-full text-center text-xs text-aiu-ink-600 hover:underline">
                    Cancel
                </button>
            </form>

        @else
            <form wire:submit="verify" class="space-y-5">
                <div>
                    <label class="block text-xs uppercase tracking-wider text-aiu-ink-400 font-bold mb-2">Email</label>
                    <input type="email" wire:model="email" autofocus required class="input-3d w-full px-4 py-3 rounded-lg">
                    @error('email') <p class="mt-2 text-xs text-aiu-red">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs uppercase tracking-wider text-aiu-ink-400 font-bold mb-2">Phone</label>
                    <input type="tel" wire:model="phone" required class="input-3d w-full px-4 py-3 rounded-lg"
                           placeholder="The phone number on your account">
                    @error('phone') <p class="mt-2 text-xs text-aiu-red">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="btn-aiu w-full py-3 rounded-lg font-bold uppercase tracking-wider text-sm">
                    Verify &amp; continue
                </button>

                <p class="text-center text-xs text-aiu-ink-600">
                    Remembered it?
                    <a href="{{ route('login') }}" class="text-aiu-red font-semibold hover:underline">Back to sign in</a>
                </p>
            </form>
        @endif
    </div>
</div>
