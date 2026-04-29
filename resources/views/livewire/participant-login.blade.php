<div class="max-w-md mx-auto px-6 py-16">
    <div class="card-3d rounded-2xl p-8">
        <div class="text-center mb-7">
            <div class="logo-plate inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4 p-2.5">
                <img src="{{ asset('img/aec-logo.png') }}" alt="AIU" class="w-full h-full object-contain">
            </div>
            <p class="text-aiu-red uppercase tracking-[0.28em] text-[11px] font-bold mb-2">Participant Sign In</p>
            <h1 class="font-heading font-bold text-3xl text-aiu-ink-900">Welcome back</h1>
            <p class="mt-2 text-sm text-aiu-ink-600">Use the credentials provided by ACIE.</p>
        </div>

        <form wire:submit="login" class="space-y-5">
            <div>
                <label class="block text-xs uppercase tracking-wider text-aiu-ink-400 font-bold mb-2">Email</label>
                <input type="email" wire:model="email" autofocus required class="input-3d w-full px-4 py-3 rounded-lg">
                @error('email') <p class="mt-2 text-xs text-aiu-red">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs uppercase tracking-wider text-aiu-ink-400 font-bold mb-2">Password</label>
                <input type="password" wire:model="password" required class="input-3d w-full px-4 py-3 rounded-lg">
                @error('password') <p class="mt-2 text-xs text-aiu-red">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-aiu-ink-700 cursor-pointer">
                <input type="checkbox" wire:model="remember" class="w-4 h-4 rounded border-aiu-line text-aiu-red focus:ring-aiu-red">
                Remember me on this device
            </label>

            <button type="submit" class="btn-aiu w-full py-3 rounded-lg font-bold uppercase tracking-wider text-sm">
                Sign in
            </button>
        </form>

    </div>
</div>
