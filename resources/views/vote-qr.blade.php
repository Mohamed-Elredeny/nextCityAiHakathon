<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote QR — Next City AI Hackathon</title>
    <link rel="icon" type="image/png" href="{{ asset('img/aiu-logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        tailwind.config = { theme: { extend: {
            fontFamily: { heading: ['Ubuntu', 'system-ui'], sans: ['Roboto', 'system-ui'] },
            colors: { aiu: { red: '#C8102E', 'red-50': '#FEF1F3', gold: '#cf9040', ink: { 900: '#0F172A', 700: '#334155', 600: '#475569', 400: '#94A3B8', 50: '#F4F7FB' } } },
        }}};
    </script>
    <style>
        body { font-family: 'Roboto', system-ui; color: #0F172A; }
        h1, h2, h3, .font-heading { font-family: 'Ubuntu', system-ui; letter-spacing: -0.01em; }
        .aiu-bg-soft {
            background:
                radial-gradient(1200px 700px at 8% -10%, rgba(200,16,46,0.05), transparent 55%),
                radial-gradient(1000px 600px at 100% 0%, rgba(207,144,64,0.06), transparent 55%),
                linear-gradient(180deg, #F8FAFC 0%, #F4F7FB 100%);
        }
        .card-3d {
            background: linear-gradient(180deg, #FFFFFF 0%, #FCFDFE 100%);
            border: 1px solid rgba(229,233,240,0.9);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.9), 0 1px 2px rgba(15,23,42,0.04), 0 16px 48px -12px rgba(15,23,42,0.18);
        }
        .qr-frame {
            background: white;
            border: 8px solid #C8102E;
            border-radius: 28px;
            padding: 18px;
            box-shadow: inset 0 0 0 4px white, 0 24px 60px -16px rgba(200,16,46,0.35);
        }
        @media print {
            body { background: white !important; }
            .no-print { display: none !important; }
            .card-3d { box-shadow: none; border: 1px solid #E5E9F0; }
        }
    </style>
</head>
<body class="aiu-bg-soft min-h-screen flex items-center justify-center p-6">
    <div class="card-3d rounded-3xl p-10 lg:p-16 max-w-2xl w-full text-center">
        <div class="flex items-center justify-center gap-4 mb-8">
            <div class="bg-white border border-[#ECEFF4] rounded-2xl p-3 w-20 h-20 flex items-center justify-center shadow-md">
                <img src="{{ asset('img/aiu-logo.png') }}" alt="AIU" class="w-full h-full object-contain">
            </div>
            <div class="text-left leading-tight">
                <p class="text-aiu-red font-bold uppercase tracking-[0.22em] text-[10px]">Alamein International University</p>
                <p class="font-heading text-xl font-bold">Next City AI Hackathon</p>
            </div>
        </div>

        <p class="text-aiu-red uppercase tracking-[0.32em] text-xs font-bold mb-2">People's Choice Award</p>
        <h1 class="font-heading text-4xl lg:text-5xl font-bold leading-tight">Scan to vote</h1>
        <p class="mt-4 text-aiu-ink-600 text-lg">
            Pick the team that impressed you most — one vote per person.
        </p>

        <div class="mt-10 flex flex-col items-center gap-4">
            <div class="qr-frame inline-block">
                <div id="qrcode" class="block"></div>
            </div>
            <p class="font-mono text-sm text-aiu-ink-600 break-all max-w-xs">{{ $voteUrl }}</p>
        </div>

        <p class="mt-8 text-xs text-aiu-ink-400">
            Voting closes at the end of Day 2 · One vote per device
        </p>

        <div class="no-print mt-10 flex items-center justify-center gap-3">
            <a href="{{ route('home') }}" class="inline-flex px-4 py-2 rounded-lg bg-white border border-[#E5E9F0] hover:bg-[#F4F7FB] text-sm font-semibold text-aiu-ink-700 transition">
                ← Back
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-bold text-white shadow-md
                                                     bg-gradient-to-b from-[#D1132F] to-[#B80E27]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print poster
            </button>
        </div>
    </div>

    <script>
        new QRCode(document.getElementById('qrcode'), {
            text: @json($voteUrl),
            width: 320,
            height: 320,
            colorDark : '#0F172A',
            colorLight : '#FFFFFF',
            correctLevel : QRCode.CorrectLevel.H,
        });
    </script>
</body>
</html>
