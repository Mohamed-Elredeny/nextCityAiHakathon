<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
    <div class="mb-6">
        <p class="inline-flex items-center gap-2 px-3 py-1 rounded-full chip-3d
                  text-aiu-red uppercase tracking-[0.22em] text-[10px] font-bold mb-3">
            My Profile
        </p>
        <h1 class="font-heading text-3xl lg:text-4xl font-bold text-aiu-ink-900">{{ $user?->name }}</h1>
        <p class="mt-1 text-sm text-aiu-ink-600">Update how you appear across the hackathon.</p>
    </div>

    @if ($savedMessage)
        <div class="mb-5 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 text-sm font-semibold flex items-center gap-2"
             x-data x-init="setTimeout(() => $el.style.display = 'none', 3000)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            {{ $savedMessage }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        {{-- Avatar + identity --}}
        <section class="card-3d rounded-3xl p-6 lg:p-7">
            <h2 class="font-heading text-lg font-bold text-aiu-ink-900 mb-5">Identity</h2>
            <div class="flex flex-col sm:flex-row items-start gap-6">
                <div class="text-center">
                    <div class="relative inline-block">
                        @if ($avatar)
                            <img src="{{ $avatar->temporaryUrl() }}" alt="preview" class="w-28 h-28 rounded-full object-cover ring-4 ring-aiu-red/20">
                        @elseif ($user?->avatar_path)
                            <img src="{{ asset('storage/' . $user->avatar_path) }}" alt="{{ $user->name }}" class="w-28 h-28 rounded-full object-cover ring-4 ring-aiu-red/20">
                        @else
                            <x-avatar :user="$user" size="2xl" class="ring-4 ring-aiu-red/20"/>
                        @endif
                    </div>
                    <div class="mt-3 flex flex-col gap-1.5">
                        <label class="cursor-pointer btn-soft px-3 py-1.5 rounded-lg text-xs font-semibold inline-flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V19a2 2 0 002 2h14a2 2 0 002-2v-2.5M16 8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Upload photo
                            <input type="file" wire:model="avatar" accept="image/*" class="hidden">
                        </label>
                        @if ($user?->avatar_path && !$avatar)
                            <button type="button" wire:click="$set('removeAvatar', true)" wire:click="save"
                                    class="text-[11px] text-aiu-ink-500 hover:text-aiu-red transition">Remove</button>
                        @endif
                    </div>
                    <div wire:loading wire:target="avatar" class="text-[11px] text-aiu-gold-600 mt-1">Uploading…</div>
                    @error('avatar') <p class="mt-1 text-xs text-aiu-red">{{ $message }}</p> @enderror
                </div>

                <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-4 w-full">
                    <div class="sm:col-span-2">
                        <label class="block text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold mb-1.5">Full name</label>
                        <input type="text" wire:model="name" class="input-3d w-full px-3 py-2.5 rounded-lg text-sm">
                        @error('name') <p class="mt-1 text-xs text-aiu-red">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold mb-1.5">Headline</label>
                        <input type="text" wire:model="headline" placeholder="e.g. ML engineer · Cairo University" class="input-3d w-full px-3 py-2.5 rounded-lg text-sm">
                        @error('headline') <p class="mt-1 text-xs text-aiu-red">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold mb-1.5">Email</label>
                        <input type="email" value="{{ $email }}" disabled class="input-3d w-full px-3 py-2.5 rounded-lg text-sm opacity-60 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold mb-1.5">Phone</label>
                        <input type="tel" wire:model="phone" placeholder="+201..." class="input-3d w-full px-3 py-2.5 rounded-lg text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold mb-1.5">Institution</label>
                        <input type="text" wire:model="institution" placeholder="Alamein International University" class="input-3d w-full px-3 py-2.5 rounded-lg text-sm">
                    </div>
                </div>
            </div>
        </section>

        {{-- Bio --}}
        <section class="card-3d rounded-3xl p-6 lg:p-7">
            <h2 class="font-heading text-lg font-bold text-aiu-ink-900 mb-3">About you</h2>
            <textarea wire:model="bio" rows="6" placeholder="Short bio — what you build, what excites you, what you bring to your team."
                      class="input-3d w-full px-4 py-3 rounded-xl text-sm leading-relaxed resize-y"></textarea>
            @error('bio') <p class="mt-1 text-xs text-aiu-red">{{ $message }}</p> @enderror
        </section>

        {{-- Social --}}
        <section class="card-3d rounded-3xl p-6 lg:p-7">
            <h2 class="font-heading text-lg font-bold text-aiu-ink-900 mb-3">Links</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach (['linkedin' => 'LinkedIn', 'github' => 'GitHub', 'twitter' => 'Twitter / X', 'website' => 'Personal site'] as $key => $label)
                    <div>
                        <label class="block text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold mb-1.5">{{ $label }}</label>
                        <input type="url" wire:model="socialLinks.{{ $key }}" placeholder="https://..." class="input-3d w-full px-3 py-2.5 rounded-lg text-sm">
                        @error('socialLinks.'.$key) <p class="mt-1 text-xs text-aiu-red">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </div>
        </section>

        <div class="flex justify-end">
            <button type="submit" class="btn-aiu px-6 py-2.5 rounded-xl text-sm font-bold inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Save profile
            </button>
        </div>
    </form>
</div>
