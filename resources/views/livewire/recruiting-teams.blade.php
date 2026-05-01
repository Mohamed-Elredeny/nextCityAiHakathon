<div>
    <section class="relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-10 lg:py-14">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                <div class="max-w-2xl">
                    <a href="{{ route('community') }}" class="inline-flex items-center gap-1.5 text-sm text-aiu-ink-600 hover:text-aiu-red transition mb-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Community
                    </a>
                    <p class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full chip-3d
                              text-aiu-red uppercase tracking-[0.22em] text-[11px] font-bold">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-aiu-red animate-pulse"></span>
                        Recruiting Teams
                    </p>
                    <h1 class="mt-4 font-heading font-bold text-3xl lg:text-5xl leading-[1.05] tracking-tight text-aiu-ink-900">
                        Find a team to join.
                    </h1>
                    <p class="mt-4 text-base text-aiu-ink-600 leading-relaxed max-w-xl">
                        Browse teams that are looking for new members. Apply with a quick intro — the team leader will review and decide.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-6 lg:px-8 pb-16">
        @if ($applySuccess)
            <div class="mb-5 p-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 text-sm">
                {{ $applySuccess }}
            </div>
        @endif

        @auth
            @if ($alreadyOnTeam)
                <div class="mb-5 p-4 rounded-xl border border-amber-200 bg-amber-50 text-amber-800 text-sm">
                    You are already a member of a team. You can't apply to another while you're on one.
                </div>
            @endif
        @else
            <div class="mb-5 p-4 rounded-xl card-3d text-sm text-aiu-ink-700">
                <a href="{{ route('login') }}" class="text-aiu-red font-semibold hover:underline">Sign in</a> to apply to a team.
            </div>
        @endauth

        @if ($teams->isEmpty())
            <div class="card-3d rounded-2xl p-10 text-center">
                <p class="text-aiu-ink-600 text-sm">No teams are recruiting right now. Check back soon!</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($teams as $team)
                    @php
                        $myApp = $myApplications[$team->id] ?? null;
                        $applying = $applyToTeamId === $team->id;
                    @endphp
                    <article class="card-3d rounded-2xl p-5 lg:p-6 flex flex-col">
                        <div class="flex items-start gap-4 mb-3">
                            @if ($team->logo_url)
                                <img src="{{ $team->logo_url }}" alt="{{ $team->name }}"
                                     class="w-14 h-14 rounded-xl object-cover border border-aiu-line shrink-0">
                            @else
                                <div class="w-14 h-14 rounded-xl chip-3d flex items-center justify-center shrink-0">
                                    <span class="font-heading font-bold text-aiu-ink-700">
                                        {{ strtoupper(mb_substr($team->name, 0, 2)) }}
                                    </span>
                                </div>
                            @endif

                            <div class="flex-1 min-w-0">
                                <a href="{{ route('teams.show', $team->slug) }}"
                                   class="font-heading font-bold text-lg text-aiu-ink-900 hover:text-aiu-red transition leading-tight block">
                                    {{ $team->name }}
                                </a>
                                @if ($team->tagline)
                                    <p class="text-xs text-aiu-ink-600 mt-0.5 line-clamp-2">{{ $team->tagline }}</p>
                                @endif
                                <p class="mt-1 text-[11px] text-aiu-ink-500">
                                    @if ($team->theme)
                                        <span>{{ $team->theme->name }}</span>
                                        <span class="text-aiu-ink-400">·</span>
                                    @endif
                                    <span>{{ $team->members_count }} {{ $team->members_count === 1 ? 'member' : 'members' }}</span>
                                </p>
                            </div>
                        </div>

                        @if ($team->recruitment_message)
                            <div class="surface-soft rounded-xl px-4 py-3 mb-3">
                                <p class="text-xs uppercase tracking-wider font-bold text-aiu-ink-500 mb-1">What we're looking for</p>
                                <p class="text-sm text-aiu-ink-700 whitespace-pre-line leading-relaxed">{{ $team->recruitment_message }}</p>
                                @if ($team->looking_for_skills)
                                    <p class="mt-2 text-xs">
                                        <span class="font-semibold text-aiu-ink-700">Skills:</span>
                                        <span class="text-aiu-ink-600">{{ $team->looking_for_skills }}</span>
                                    </p>
                                @endif
                            </div>
                        @endif

                        <p class="text-[11px] text-aiu-ink-500 mb-3">
                            Led by
                            <a href="{{ route('users.show', $team->leader_id) }}" class="font-semibold text-aiu-ink-700 hover:text-aiu-red">
                                {{ $team->leader?->name ?? '—' }}
                            </a>
                        </p>

                        <div class="mt-auto pt-3 border-t border-aiu-line">
                            @auth
                                @if ($myApp && $myApp->status === 'pending')
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full chip-3d text-[11px] font-semibold text-amber-700">
                                            ● Application pending
                                        </span>
                                        <button wire:click="withdrawApplication({{ $myApp->id }})"
                                                wire:confirm="Withdraw your application?"
                                                class="text-xs text-aiu-ink-600 hover:text-aiu-red font-semibold">
                                            Withdraw
                                        </button>
                                    </div>
                                @elseif ($myApp && $myApp->status === 'approved')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        ✓ Approved · You're on the team
                                    </span>
                                @elseif ($myApp && $myApp->status === 'rejected')
                                    <div>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-aiu-red-50 text-aiu-red border border-aiu-red-200">
                                            Application not accepted
                                        </span>
                                        @if ($myApp->response_message)
                                            <p class="mt-2 text-xs text-aiu-ink-600 italic">"{{ $myApp->response_message }}"</p>
                                        @endif
                                    </div>
                                @elseif (auth()->id() === $team->leader_id)
                                    <span class="text-xs text-aiu-ink-500">You lead this team.</span>
                                @elseif ($alreadyOnTeam)
                                    <span class="text-xs text-aiu-ink-500">Already on a team — can't apply.</span>
                                @elseif ($applying)
                                    <form wire:submit.prevent="submitApplication" class="space-y-2">
                                        <textarea wire:model="applicationMessage" rows="3" maxlength="1000"
                                                  placeholder="Why do you want to join? What can you contribute? (min 20 chars)"
                                                  class="input-3d w-full px-3 py-2 rounded-lg text-sm leading-relaxed"></textarea>
                                        @error('applicationMessage') <p class="text-xs text-aiu-red">{{ $message }}</p> @enderror
                                        <input type="text" wire:model="applicationSkills" maxlength="200"
                                               placeholder="Your skills (e.g. ML, React, UX)"
                                               class="input-3d w-full px-3 py-2 rounded-lg text-sm">
                                        @if ($applyError)
                                            <p class="text-xs text-aiu-red">{{ $applyError }}</p>
                                        @endif
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button" wire:click="cancelApply" class="btn-soft px-3 py-1.5 rounded-lg text-xs font-semibold">Cancel</button>
                                            <button type="submit" class="btn-aiu px-4 py-1.5 rounded-lg text-xs font-semibold">Send application</button>
                                        </div>
                                    </form>
                                @else
                                    <button wire:click="openApplyForm({{ $team->id }})" type="button"
                                            class="btn-aiu w-full px-4 py-2 rounded-lg text-sm font-semibold">
                                        Apply to join
                                    </button>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="btn-aiu inline-block w-full text-center px-4 py-2 rounded-lg text-sm font-semibold">
                                    Sign in to apply
                                </a>
                            @endauth
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</div>
