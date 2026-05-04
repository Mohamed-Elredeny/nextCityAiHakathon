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

    {{-- Avatar upload — standalone section, OUTSIDE the Livewire form
         (HTML doesn't allow nested forms, so this can't live inside the
         wire:submit="save" form below). --}}
    <section class="card-3d rounded-3xl p-6 lg:p-7 mb-6" wire:ignore>
        <div class="flex flex-col sm:flex-row items-center gap-6">
            <div class="shrink-0">
                @if ($user?->avatar_path)
                    <img src="{{ asset('storage/' . $user->avatar_path) }}" alt="{{ $user->name }}" class="w-28 h-28 rounded-full object-cover ring-4 ring-aiu-red/20">
                @else
                    <x-avatar :user="$user" size="2xl" class="ring-4 ring-aiu-red/20"/>
                @endif
            </div>
            <div class="flex-1 w-full">
                <h2 class="font-heading text-lg font-bold text-aiu-ink-900 mb-1">Profile photo</h2>
                <p class="text-xs text-aiu-ink-600 mb-3">JPG/PNG, ≤ 2 MB.</p>
                <form action="{{ route('profile.avatar.upload') }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-center gap-3">
                    @csrf
                    <input type="file" name="avatar" accept="image/*" required
                           class="text-xs file:mr-2 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-aiu-ink-100 file:text-aiu-ink-700 file:font-semibold file:cursor-pointer hover:file:bg-aiu-red hover:file:text-white">
                    <button type="submit" class="btn-aiu px-4 py-1.5 rounded-lg text-xs font-semibold">
                        {{ $user?->avatar_path ? 'Replace photo' : 'Upload photo' }}
                    </button>
                </form>
                @error('avatar') <p class="mt-2 text-xs text-aiu-red">{{ $message }}</p> @enderror
                @if ($user?->avatar_path)
                    <form action="{{ route('profile.avatar.delete') }}" method="POST" class="mt-2"
                          onsubmit="return confirm('Remove your photo?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-[11px] text-aiu-ink-500 hover:text-aiu-red transition">Remove photo</button>
                    </form>
                @endif
                @if (session('asset_status'))
                    <p class="mt-2 text-xs text-emerald-600 font-semibold">{{ session('asset_status') }}</p>
                @endif
            </div>
        </div>
    </section>

    <form wire:submit="save" class="space-y-6">
        {{-- Identity (rest of the profile) --}}
        <section class="card-3d rounded-3xl p-6 lg:p-7">
            <h2 class="font-heading text-lg font-bold text-aiu-ink-900 mb-5">Identity</h2>
            <div class="flex flex-col sm:flex-row items-start gap-6">
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
                    <div class="sm:col-span-2">
                        <label class="block text-[10px] uppercase tracking-wider text-aiu-ink-400 font-bold mb-1.5">Primary role on a team</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach (\App\Models\User::ROLE_CATEGORIES as $key => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" wire:model="primaryRole" value="{{ $key }}" class="peer sr-only">
                                    <div class="px-3 py-2.5 rounded-lg text-center text-xs font-semibold ring-1 ring-aiu-line bg-white text-aiu-ink-700 hover:ring-aiu-red/40 peer-checked:bg-aiu-red peer-checked:text-white peer-checked:ring-aiu-red transition">
                                        {{ $label }}
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-1.5 text-[11px] text-aiu-ink-500">Pick what you bring to a team. Used by leaders looking for missing roles.</p>
                        @error('primaryRole') <p class="mt-1 text-xs text-aiu-red">{{ $message }}</p> @enderror
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
