<div class="max-w-xl mx-auto px-6 py-12">
    <div class="card-3d rounded-2xl p-8">
        <div class="text-center mb-7">
            <div class="logo-plate inline-flex items-center justify-center h-20 rounded-2xl mb-5 px-5 py-3">
                <img src="{{ asset('img/aec-logo.png') }}" alt="AIU · ACIE" class="h-full w-auto object-contain">
            </div>
            <p class="text-aiu-red uppercase tracking-[0.28em] text-[11px] font-bold mb-2">Create Account</p>
            <h1 class="font-heading font-bold text-3xl text-aiu-ink-900">Join Next City AI Hack</h1>
            <p class="mt-2 text-sm text-aiu-ink-600">Register as a student, mentor, or judge.</p>
        </div>

        @if ($submittedStatus === 'pending')
            <div class="rounded-xl border border-aiu-gold-300 bg-aiu-gold-50 p-5 text-center">
                <h2 class="font-heading font-bold text-aiu-ink-900 mb-1">Application received</h2>
                <p class="text-sm text-aiu-ink-700">
                    Thanks! Your {{ ucfirst($role) }} application is awaiting admin review. You'll be able to sign in once it has been approved.
                </p>
                <a href="{{ route('login') }}" class="inline-block mt-5 px-4 py-2 rounded-lg btn-soft text-sm font-semibold">
                    Back to sign in
                </a>
            </div>
        @else
            <form wire:submit="register" class="space-y-5">
                <div>
                    <label class="block text-xs uppercase tracking-wider text-aiu-ink-400 font-bold mb-2">I am registering as</label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach (\App\Livewire\ParticipantRegister::ROLES as $r)
                            <label class="cursor-pointer block">
                                <input type="radio" wire:model.live="role" value="{{ $r }}" class="peer sr-only">
                                <div @class([
                                    'text-center px-3 py-2.5 rounded-lg text-sm font-semibold capitalize transition border',
                                    'chip-3d text-aiu-ink-700 hover:border-aiu-red/40' => $role !== $r,
                                    'bg-aiu-red text-white border-aiu-red shadow-red' => $role === $r,
                                ])>
                                    {{ $r }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @if (in_array($role, \App\Livewire\ParticipantRegister::PENDING_ROLES, true))
                        <p class="mt-2 text-[11px] text-aiu-ink-600">
                            <span class="font-semibold text-aiu-red">Note:</span> Mentor &amp; Judge accounts require admin approval before sign-in is enabled.
                        </p>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-aiu-ink-400 font-bold mb-2">Full name</label>
                        <input type="text" wire:model="name" required class="input-3d w-full px-4 py-3 rounded-lg">
                        @error('name') <p class="mt-1 text-xs text-aiu-red">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs uppercase tracking-wider text-aiu-ink-400 font-bold mb-2">Email</label>
                        <input type="email" wire:model="email" required class="input-3d w-full px-4 py-3 rounded-lg">
                        @error('email') <p class="mt-1 text-xs text-aiu-red">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs uppercase tracking-wider text-aiu-ink-400 font-bold mb-2">Phone (optional)</label>
                        <input type="tel" wire:model="phone" class="input-3d w-full px-4 py-3 rounded-lg">
                        @error('phone') <p class="mt-1 text-xs text-aiu-red">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs uppercase tracking-wider text-aiu-ink-400 font-bold mb-2">
                            {{ $role === 'student' ? 'Institution' : 'Institution / Company' }}
                        </label>
                        <input type="text" wire:model="institution" class="input-3d w-full px-4 py-3 rounded-lg">
                        @error('institution') <p class="mt-1 text-xs text-aiu-red">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs uppercase tracking-wider text-aiu-ink-400 font-bold mb-2">
                        Headline (optional)
                    </label>
                    <input type="text" wire:model="headline" maxlength="120"
                           placeholder="{{ $role === 'judge' ? 'e.g. Senior Product Manager · 12 years' : ($role === 'mentor' ? 'e.g. AI Lead · Vodafone' : 'e.g. CS student · year 3') }}"
                           class="input-3d w-full px-4 py-3 rounded-lg">
                    @error('headline') <p class="mt-1 text-xs text-aiu-red">{{ $message }}</p> @enderror
                </div>

                @if (in_array($role, \App\Livewire\ParticipantRegister::PENDING_ROLES, true))
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-aiu-ink-400 font-bold mb-2">
                            Brief background (helps admin review)
                        </label>
                        <textarea wire:model="bio" rows="4" maxlength="2000"
                                  class="input-3d w-full px-4 py-3 rounded-lg"
                                  placeholder="Areas of expertise, relevant experience, why you'd like to {{ $role }}…"></textarea>
                        @error('bio') <p class="mt-1 text-xs text-aiu-red">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-aiu-ink-400 font-bold mb-2">Password</label>
                        <input type="password" wire:model="password" required class="input-3d w-full px-4 py-3 rounded-lg">
                        @error('password') <p class="mt-1 text-xs text-aiu-red">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-aiu-ink-400 font-bold mb-2">Confirm password</label>
                        <input type="password" wire:model="password_confirmation" required class="input-3d w-full px-4 py-3 rounded-lg">
                    </div>
                </div>

                <button type="submit" class="btn-aiu w-full py-3 rounded-lg font-bold uppercase tracking-wider text-sm">
                    @if (in_array($role, \App\Livewire\ParticipantRegister::PENDING_ROLES, true))
                        Submit application
                    @else
                        Create account
                    @endif
                </button>

                <p class="text-center text-xs text-aiu-ink-600">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-aiu-red font-semibold hover:underline">Sign in</a>
                </p>
            </form>
        @endif
    </div>
</div>
