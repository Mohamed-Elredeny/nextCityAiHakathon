<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Hackathon Big Screen' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('img/aec-logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: {
                fontFamily: { heading: ['Ubuntu', 'system-ui'], sans: ['Roboto', 'system-ui'] },
                colors: {
                    aiu: {
                        red: '#C8102E', 'red-50': '#FEF1F3', 'red-700': '#7E091D',
                        gold: '#cf9040', 'gold-50': '#FAF1E1', 'gold-600': '#a87231',
                        navy: '#0F4778',
                        ink: { 900: '#0F172A', 700: '#334155', 600: '#475569', 400: '#94A3B8', 200: '#E2E8F0', 100: '#EEF2F7', 50: '#F4F7FB' },
                        line: '#E5E9F0',
                    },
                },
            }},
        };
    </script>
    @livewireStyles
    <style>
        :root { color-scheme: light; }
        body { font-family: 'Roboto', system-ui; color: #0F172A; overflow: hidden; }
        h1, h2, h3, .font-heading { font-family: 'Ubuntu', system-ui; letter-spacing: -0.01em; }
        .tabular-nums { font-variant-numeric: tabular-nums; }
        .aiu-bg-soft {
            background:
                radial-gradient(1400px 700px at 8% -10%, rgba(200,16,46,0.06), transparent 55%),
                radial-gradient(1100px 600px at 100% 0%, rgba(207,144,64,0.08), transparent 55%),
                linear-gradient(180deg, #F8FAFC 0%, #F4F7FB 100%);
        }
        .card-3d {
            background: linear-gradient(180deg, #FFFFFF 0%, #FCFDFE 100%);
            border: 1px solid rgba(229,233,240,0.9);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.9), 0 1px 2px rgba(15,23,42,0.04), 0 8px 24px -8px rgba(15,23,42,0.10);
        }
        .btn-aiu-solid {
            background: linear-gradient(180deg, #D1132F 0%, #B80E27 100%);
            color: white;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.20), 0 1px 2px rgba(200,16,46,0.30), 0 8px 18px -6px rgba(200,16,46,0.45);
        }
        .logo-plate {
            background: linear-gradient(180deg, #FFFFFF 0%, #FAFBFD 100%);
            border: 1px solid #ECEFF4;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.95), 0 2px 6px -2px rgba(15,23,42,0.06);
        }
        @keyframes slide-in { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        .row-anim { animation: slide-in 350ms ease-out; }
    </style>
</head>
<body class="aiu-bg-soft min-h-screen">
    {{ $slot }}
    @livewireScripts
</body>
</html>
