<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Next City AI Hackathon — Leaderboard' }}</title>

    <link rel="icon" type="image/png" href="{{ asset('img/aec-logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        heading: ['Ubuntu', 'system-ui', 'sans-serif'],
                        sans: ['Roboto', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        aiu: {
                            red:        '#C8102E',
                            'red-50':   '#FEF1F3',
                            'red-100':  '#FCE0E5',
                            'red-200':  '#F7B8C2',
                            'red-500':  '#C8102E',
                            'red-600':  '#A50C26',
                            'red-700':  '#7E091D',
                            navy:       '#0F4778',
                            'navy-50':  '#EEF3F8',
                            gold:       '#cf9040',
                            'gold-50':  '#FAF1E1',
                            'gold-300': '#e2b878',
                            'gold-500': '#cf9040',
                            'gold-600': '#a87231',
                            ink: {
                                900: '#0F172A',
                                700: '#334155',
                                600: '#475569',
                                400: '#94A3B8',
                                200: '#E2E8F0',
                                100: '#EEF2F7',
                                50:  '#F4F7FB',
                            },
                            surface: '#FFFFFF',
                            base:    '#F6F8FB',
                            soft:    '#EEF2F7',
                            line:    '#E5E9F0',
                        },
                    },
                    boxShadow: {
                        'soft':    '0 1px 2px rgba(15,23,42,0.04), 0 4px 12px -4px rgba(15,23,42,0.06)',
                        'card':    '0 1px 2px rgba(15,23,42,0.05), 0 8px 24px -8px rgba(15,23,42,0.10)',
                        'lifted':  '0 2px 4px rgba(15,23,42,0.04), 0 16px 40px -12px rgba(15,23,42,0.16)',
                        'red':     '0 1px 2px rgba(200,16,46,0.20), 0 12px 28px -8px rgba(200,16,46,0.30)',
                        'gold':    '0 1px 2px rgba(207,144,64,0.20), 0 10px 24px -8px rgba(207,144,64,0.32)',
                        'inset-top': 'inset 0 1px 0 rgba(255,255,255,0.85), inset 0 -1px 0 rgba(15,23,42,0.04)',
                    },
                },
            },
        };
    </script>

    @livewireStyles

    <style>
        :root {
            color-scheme: light;
        }
        body {
            font-family: 'Roboto', system-ui, sans-serif;
            color: #0F172A;
            background-color: #F6F8FB;
        }
        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Ubuntu', system-ui, sans-serif;
            letter-spacing: -0.01em;
        }
        .tabular-nums { font-variant-numeric: tabular-nums; }

        /* Soft, layered ambient background — barely-visible warmth, no harsh contrast */
        .aiu-bg-soft {
            background:
                radial-gradient(1200px 700px at 8% -10%, rgba(200,16,46,0.05), transparent 55%),
                radial-gradient(1000px 600px at 100% 0%,  rgba(207,144,64,0.06), transparent 55%),
                radial-gradient(900px  600px at 50% 110%, rgba(15,71,120,0.04), transparent 60%),
                linear-gradient(180deg, #F8FAFC 0%, #F4F7FB 100%);
        }

        /* 3D card: realistic top-edge highlight + bottom soft shadow */
        .card-3d {
            background: linear-gradient(180deg, #FFFFFF 0%, #FCFDFE 100%);
            border: 1px solid rgba(229, 233, 240, 0.9);
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,0.9),
                0 1px 2px rgba(15,23,42,0.04),
                0 8px 24px -8px rgba(15,23,42,0.10);
            transition: transform .25s ease, box-shadow .25s ease;
        }
        .card-3d-hover:hover {
            transform: translateY(-2px);
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,0.95),
                0 2px 6px rgba(15,23,42,0.05),
                0 18px 40px -12px rgba(15,23,42,0.15);
        }

        /* Inset / sub-card variant */
        .surface-soft {
            background: linear-gradient(180deg, #F7F9FC 0%, #F2F5FA 100%);
            border: 1px solid rgba(229, 233, 240, 0.7);
        }

        /* Pill / chip with subtle 3D */
        .chip-3d {
            background: linear-gradient(180deg, #FFFFFF 0%, #F4F7FB 100%);
            border: 1px solid #E5E9F0;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.95), 0 1px 2px rgba(15,23,42,0.04);
        }

        /* Brand button (red) with depth */
        .btn-aiu {
            background: linear-gradient(180deg, #D1132F 0%, #B80E27 100%);
            color: white;
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,0.20),
                0 1px 2px rgba(200,16,46,0.30),
                0 8px 18px -6px rgba(200,16,46,0.45);
            transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
        }
        .btn-aiu:hover { transform: translateY(-1px); filter: brightness(1.04); }
        .btn-aiu:active { transform: translateY(0); filter: brightness(0.98); }
        .btn-aiu:disabled { background: #CBD2DC; box-shadow: none; cursor: not-allowed; color: #94A3B8; }

        /* Soft (secondary) button */
        .btn-soft {
            background: linear-gradient(180deg, #FFFFFF 0%, #F4F7FB 100%);
            color: #334155;
            border: 1px solid #E5E9F0;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.95), 0 1px 2px rgba(15,23,42,0.04);
            transition: transform .18s ease, box-shadow .18s ease;
        }
        .btn-soft:hover { transform: translateY(-1px); box-shadow: inset 0 1px 0 rgba(255,255,255,0.95), 0 4px 10px -2px rgba(15,23,42,0.08); }

        /* Inputs */
        .input-3d {
            background: #FFFFFF;
            border: 1px solid #E5E9F0;
            box-shadow: inset 0 1px 2px rgba(15,23,42,0.05);
            transition: border-color .18s ease, box-shadow .18s ease;
        }
        .input-3d:focus {
            outline: none;
            border-color: #C8102E;
            box-shadow: inset 0 1px 2px rgba(15,23,42,0.05), 0 0 0 4px rgba(200,16,46,0.10);
        }

        /* Range slider styling for light mode */
        input[type="range"].slider-aiu {
            -webkit-appearance: none;
            height: 8px;
            background: linear-gradient(180deg, #EEF2F7 0%, #E2E8F0 100%);
            border-radius: 999px;
            box-shadow: inset 0 1px 2px rgba(15,23,42,0.10);
        }
        input[type="range"].slider-aiu::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 22px; height: 22px;
            border-radius: 999px;
            background: linear-gradient(180deg, #FFFFFF 0%, #F4F7FB 100%);
            border: 2px solid #C8102E;
            box-shadow: 0 2px 6px rgba(200,16,46,0.30);
            cursor: pointer;
        }
        input[type="range"].slider-aiu::-moz-range-thumb {
            width: 22px; height: 22px;
            border-radius: 999px;
            background: #FFFFFF;
            border: 2px solid #C8102E;
            box-shadow: 0 2px 6px rgba(200,16,46,0.30);
            cursor: pointer;
        }

        /* Header logo plate (gives the red mark a soft white well) */
        .logo-plate {
            background: linear-gradient(180deg, #FFFFFF 0%, #FAFBFD 100%);
            border: 1px solid #ECEFF4;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.95), 0 2px 6px -2px rgba(15,23,42,0.06);
        }

        /* Alpine: hide elements until x-data is hydrated to avoid flash */
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="aiu-bg-soft min-h-screen antialiased">

    <header x-data="{ open: false }"
            class="bg-white/85 backdrop-blur-md border-b border-aiu-line sticky top-0 z-40 shadow-soft">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 sm:py-4 flex items-center justify-between gap-3 sm:gap-6">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group min-w-0">
                <div class="logo-plate h-14 sm:h-16 rounded-xl flex items-center justify-center px-3 py-2 group-hover:scale-105 transition-transform shrink-0">
                    <img src="{{ asset('img/aec-logo.png') }}" alt="AIU · ACIE" class="h-full w-auto object-contain">
                </div>
                <div class="leading-tight min-w-0 hidden sm:block">
                    <p class="font-heading font-bold text-aiu-ink-900 text-sm sm:text-base truncate">
                        Alamein International University
                    </p>
                    <p class="text-[10px] sm:text-[11px] uppercase tracking-[0.18em] sm:tracking-[0.22em] text-aiu-red font-bold truncate">
                        Center for Innovation &amp; Entrepreneurship
                    </p>
                </div>
            </a>

            {{-- Mobile hamburger --}}
            <button type="button" @click="open = !open"
                    :aria-expanded="open" aria-controls="mobile-nav" aria-label="Toggle navigation"
                    class="md:hidden p-2 rounded-lg text-aiu-ink-700 hover:bg-aiu-ink-50 transition shrink-0">
                <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="open" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            {{-- Desktop nav --}}
            <nav class="hidden md:flex items-center gap-1 text-sm font-medium">
                <a href="{{ route('home') }}" class="px-3 py-2 rounded-lg text-aiu-ink-700 hover:text-aiu-red hover:bg-aiu-red-50 transition">Leaderboard</a>
                <a href="{{ route('winners') }}" class="px-3 py-2 rounded-lg text-aiu-gold-600 hover:text-aiu-gold hover:bg-aiu-gold-50 transition font-bold">🏆 Winners</a>
                <a href="{{ route('community') }}" class="px-3 py-2 rounded-lg text-aiu-ink-700 hover:text-aiu-red hover:bg-aiu-red-50 transition">Community</a>
                @auth
                    @if (auth()->user()->canVoteRestrictedAwards())
                        <a href="{{ route('awards.vote') }}" class="px-3 py-2 rounded-lg text-aiu-red hover:text-aiu-red-700 hover:bg-aiu-red-50 transition font-bold">Vote awards</a>
                    @endif
                    @if (auth()->user()->hasAnyRole(['team_leader', 'team_member']))
                        <a href="{{ route('workspace') }}" class="px-3 py-2 rounded-lg text-aiu-ink-700 hover:text-aiu-red hover:bg-aiu-red-50 transition">Workspace</a>
                    @endif
                    @if (auth()->user()->hasRole('judge'))
                        <a href="{{ route('judge') }}" class="px-3 py-2 rounded-lg text-aiu-ink-700 hover:text-aiu-red hover:bg-aiu-red-50 transition">Judge</a>
                    @endif
                    @if (auth()->user()->hasRole('mentor'))
                        <a href="{{ route('mentor') }}" class="px-3 py-2 rounded-lg text-aiu-ink-700 hover:text-aiu-red hover:bg-aiu-red-50 transition">Mentor</a>
                    @endif
                    @if (auth()->user()->hasRole('super_admin'))
                        <a href="{{ url('/admin') }}" class="px-3 py-2 rounded-lg text-aiu-ink-700 hover:text-aiu-red hover:bg-aiu-red-50 transition">Admin</a>
                    @endif
                    <span class="mx-2 h-5 w-px bg-aiu-line"></span>
                    @livewire('notification-bell')
                    <a href="{{ route('profile') }}" class="inline-flex items-center gap-2 px-2 py-1 rounded-lg hover:bg-aiu-ink-50 transition" title="My profile">
                        <x-avatar :user="auth()->user()" size="sm" />
                        <span class="text-xs text-aiu-ink-700 font-semibold hidden lg:inline">{{ auth()->user()->name }}</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-2 rounded-lg text-aiu-ink-600 hover:text-aiu-red transition text-sm">
                            Sign out
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="ml-2 px-4 py-2 rounded-lg btn-aiu text-sm font-semibold">Sign in</a>
                @endauth
            </nav>
        </div>

        {{-- Mobile drawer --}}
        <nav id="mobile-nav" x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.outside="open = false"
             class="md:hidden border-t border-aiu-line bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex flex-col gap-1 text-sm font-medium">
                <a href="{{ route('home') }}" @click="open = false" class="px-3 py-2.5 rounded-lg text-aiu-ink-700 hover:text-aiu-red hover:bg-aiu-red-50 transition">Leaderboard</a>
                <a href="{{ route('winners') }}" @click="open = false" class="px-3 py-2.5 rounded-lg text-aiu-gold-600 hover:text-aiu-gold hover:bg-aiu-gold-50 transition font-bold">🏆 Winners</a>
                <a href="{{ route('community') }}" @click="open = false" class="px-3 py-2.5 rounded-lg text-aiu-ink-700 hover:text-aiu-red hover:bg-aiu-red-50 transition">Community</a>
                @auth
                    @if (auth()->user()->canVoteRestrictedAwards())
                        <a href="{{ route('awards.vote') }}" @click="open = false" class="px-3 py-2.5 rounded-lg text-aiu-red hover:text-aiu-red-700 hover:bg-aiu-red-50 transition font-bold">Vote awards</a>
                    @endif
                    @if (auth()->user()->hasAnyRole(['team_leader', 'team_member']))
                        <a href="{{ route('workspace') }}" @click="open = false" class="px-3 py-2.5 rounded-lg text-aiu-ink-700 hover:text-aiu-red hover:bg-aiu-red-50 transition">Workspace</a>
                    @endif
                    @if (auth()->user()->hasRole('judge'))
                        <a href="{{ route('judge') }}" @click="open = false" class="px-3 py-2.5 rounded-lg text-aiu-ink-700 hover:text-aiu-red hover:bg-aiu-red-50 transition">Judge</a>
                    @endif
                    @if (auth()->user()->hasRole('mentor'))
                        <a href="{{ route('mentor') }}" @click="open = false" class="px-3 py-2.5 rounded-lg text-aiu-ink-700 hover:text-aiu-red hover:bg-aiu-red-50 transition">Mentor</a>
                    @endif
                    @if (auth()->user()->hasRole('super_admin'))
                        <a href="{{ url('/admin') }}" @click="open = false" class="px-3 py-2.5 rounded-lg text-aiu-ink-700 hover:text-aiu-red hover:bg-aiu-red-50 transition">Admin</a>
                    @endif
                    <a href="{{ route('notifications') }}" @click="open = false" class="px-3 py-2.5 rounded-lg text-aiu-ink-700 hover:text-aiu-red hover:bg-aiu-red-50 transition">Notifications</a>
                    <a href="{{ route('profile') }}" @click="open = false" class="px-3 py-2.5 rounded-lg text-aiu-ink-700 hover:text-aiu-red hover:bg-aiu-red-50 transition">My profile</a>
                    <div class="my-1 h-px bg-aiu-line"></div>
                    <p class="px-3 pt-1 pb-2 text-[11px] text-aiu-ink-400">Signed in as <span class="text-aiu-ink-700 font-semibold">{{ auth()->user()->name }}</span></p>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-2.5 rounded-lg text-aiu-ink-600 hover:text-aiu-red hover:bg-aiu-ink-50 transition">
                            Sign out
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" @click="open = false" class="mt-1 px-4 py-2.5 rounded-lg btn-aiu text-sm font-semibold text-center">Sign in</a>
                @endauth
            </div>
        </nav>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="mt-16 border-t border-aiu-line bg-white/60 backdrop-blur">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-6 flex flex-col md:flex-row items-center justify-between gap-3 text-xs text-aiu-ink-600">
            <p>© {{ date('Y') }} Alamein International University · Center for Innovation &amp; Entrepreneurship</p>
            <p class="font-heading tracking-wide text-aiu-ink-700">Next City AI Hackathon · El Alamein, Egypt</p>
        </div>
        <div class="border-t border-aiu-line/60 bg-aiu-ink-50/40">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 py-3 text-center text-[11px] text-aiu-ink-500">
                Developed by <span class="font-semibold text-aiu-ink-700">Mohamed Elredeny</span> · TA <span class="text-aiu-red">@AIU</span>
            </div>
        </div>
    </footer>

    @livewireScripts

    @auth
        {{-- Real-time notifications via Pusher Channels.
             Listens on private channel App.Models.User.{id} for the standard
             Laravel notification broadcast, then dispatches a browser event
             that the NotificationBell Alpine listener picks up to play sound + refresh. --}}
        <script src="https://js.pusher.com/8.4/pusher.min.js"></script>
        <script>
            (function () {
                const key = @json(config('broadcasting.connections.pusher.key'));
                const cluster = @json(config('broadcasting.connections.pusher.options.cluster'));
                if (!key) return;

                // CSRF token + headers for the Laravel /broadcasting/auth endpoint
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content
                    || @json(csrf_token());

                const pusher = new Pusher(key, {
                    cluster: cluster || 'eu',
                    forceTLS: true,
                    authEndpoint: '/broadcasting/auth',
                    auth: {
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    },
                });

                const channel = pusher.subscribe('private-App.Models.User.{{ auth()->id() }}');
                channel.bind_global(function (eventName, data) {
                    // Laravel broadcasts notifications as either:
                    //   - 'Illuminate\\Notifications\\Events\\BroadcastNotificationCreated'
                    //   - or the broadcastType() string ('community.notification')
                    if (typeof eventName !== 'string') return;
                    if (eventName.startsWith('pusher:')) return;
                    // Tell the bell to refetch + play sound
                    window.dispatchEvent(new CustomEvent('pusher-notification-arrived', { detail: data }));
                });
            })();
        </script>
    @endauth
</body>
</html>
