<!DOCTYPE html>
<html lang="fr" class="">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Orizona — La plateforme immobilière nouvelle génération pour trouver, gérer et louer des propriétés en toute simplicité.">
    <title>Orizona — Plateforme Immobilière</title>

    <!-- Fonts (même que les autres pages) -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet">

    <!-- Tailwind CSS v4 (inline build comme les autres layouts) -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            /* Minimal inline styles for welcome page using same tokens */
            *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; border: 0; }
            html { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; line-height: 1.5; -webkit-text-size-adjust: 100%; scroll-behavior: smooth; }
            body { min-height: 100vh; background-color: #FDFDFC; color: #1b1b18; }
            a { color: inherit; text-decoration: none; }
            img, svg, video { display: block; max-width: 100%; }

            @media (prefers-color-scheme: dark) {
                body { background-color: #0a0a0a; color: #EDEDEC; }
            }

            /* Layout utilities */
            .container { max-width: 80rem; margin-inline: auto; padding-inline: 1.5rem; }
            .flex { display: flex; }
            .flex-col { flex-direction: column; }
            .items-center { align-items: center; }
            .justify-between { justify-content: space-between; }
            .justify-center { justify-content: center; }
            .gap-2 { gap: 0.5rem; }
            .gap-3 { gap: 0.75rem; }
            .gap-4 { gap: 1rem; }
            .gap-6 { gap: 1.5rem; }
            .gap-8 { gap: 2rem; }
            .grid { display: grid; }
            .hidden { display: none; }
            .relative { position: relative; }
            .fixed { position: fixed; }
            .inset-0 { inset: 0; }
            .z-50 { z-index: 50; }
            .w-full { width: 100%; }
            .max-w-3xl { max-width: 48rem; }
            .max-w-4xl { max-width: 56rem; }
            .max-w-5xl { max-width: 64rem; }
            .text-center { text-align: center; }
            .text-left { text-align: left; }
            .flex-wrap { flex-wrap: wrap; }
            .overflow-hidden { overflow: hidden; }
            .truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

            /* Spacing */
            .pt-24 { padding-top: 6rem; }
            .py-4 { padding-block: 1rem; }
            .py-6 { padding-block: 1.5rem; }
            .py-8 { padding-block: 2rem; }
            .py-16 { padding-block: 4rem; }
            .py-20 { padding-block: 5rem; }
            .py-24 { padding-block: 6rem; }
            .px-4 { padding-inline: 1rem; }
            .px-5 { padding-inline: 1.25rem; }
            .px-6 { padding-inline: 1.5rem; }
            .p-4 { padding: 1rem; }
            .p-5 { padding: 1.25rem; }
            .p-6 { padding: 1.5rem; }
            .p-8 { padding: 2rem; }
            .mb-2 { margin-bottom: 0.5rem; }
            .mb-3 { margin-bottom: 0.75rem; }
            .mb-4 { margin-bottom: 1rem; }
            .mb-6 { margin-bottom: 1.5rem; }
            .mb-8 { margin-bottom: 2rem; }
            .mb-12 { margin-bottom: 3rem; }
            .mb-16 { margin-bottom: 4rem; }
            .mt-1 { margin-top: 0.25rem; }
            .mt-2 { margin-top: 0.5rem; }
            .mt-3 { margin-top: 0.75rem; }
            .mx-auto { margin-inline: auto; }

            /* Typography */
            .text-xs  { font-size: 0.75rem; line-height: 1rem; }
            .text-sm  { font-size: 0.875rem; line-height: 1.25rem; }
            .text-base { font-size: 1rem; line-height: 1.5rem; }
            .text-lg  { font-size: 1.125rem; line-height: 1.75rem; }
            .text-xl  { font-size: 1.25rem; line-height: 1.75rem; }
            .text-2xl { font-size: 1.5rem; line-height: 2rem; }
            .text-3xl { font-size: 1.875rem; line-height: 2.25rem; }
            .text-4xl { font-size: 2.25rem; line-height: 2.5rem; }
            .text-5xl { font-size: 3rem; line-height: 1; }
            .font-medium   { font-weight: 500; }
            .font-semibold { font-weight: 600; }
            .font-bold     { font-weight: 700; }
            .font-extrabold{ font-weight: 800; }
            .tracking-tight { letter-spacing: -0.025em; }
            .leading-relaxed { line-height: 1.625; }
            .leading-tight   { line-height: 1.25; }
            .uppercase { text-transform: uppercase; }

            /* Colors - light */
            .text-orizona    { color: #f53003; }
            .text-muted      { color: #706f6c; }
            .text-main       { color: #1b1b18; }
            .text-white      { color: #fff; }
            .bg-orizona      { background-color: #f53003; }
            .bg-surface      { background-color: #FDFDFC; }
            .bg-card         { background-color: #FDFDFC; }
            .bg-muted-light  { background-color: #f5f5f4; }
            .bg-accent-light { background-color: #fff2f2; }
            .border-main     { border-color: #e5e5e4; }
            .border-accent   { border-color: #fca5a5; }

            /* Dark mode */
            @media (prefers-color-scheme: dark) {
                .text-main    { color: #EDEDEC; }
                .text-muted   { color: #A1A09A; }
                .bg-surface   { background-color: #0a0a0a; }
                .bg-card      { background-color: #161615; }
                .bg-muted-light { background-color: #1a1a18; }
                .bg-accent-light { background-color: #1D0002; }
                .border-main  { border-color: #3E3E3A; }
                .border-accent { border-color: rgba(220,38,38,0.3); }
                .dm-text-ededec { color: #EDEDEC; }
            }

            /* Borders */
            .border   { border-style: solid; border-width: 1px; }
            .border-t { border-top-style: solid; border-top-width: 1px; }
            .border-b { border-bottom-style: solid; border-bottom-width: 1px; }
            .rounded-lg  { border-radius: 0.5rem; }
            .rounded-xl  { border-radius: 0.75rem; }
            .rounded-2xl { border-radius: 1rem; }
            .rounded-3xl { border-radius: 1.5rem; }
            .rounded-full { border-radius: 9999px; }

            /* Shadows */
            .shadow-sm { box-shadow: 0 1px 3px 0 rgba(0,0,0,.1), 0 1px 2px -1px rgba(0,0,0,.1); }
            .shadow-md { box-shadow: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -2px rgba(0,0,0,.1); }
            .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0,0,0,.1), 0 4px 6px -4px rgba(0,0,0,.1); }
            .shadow-xl { box-shadow: 0 20px 25px -5px rgba(0,0,0,.1), 0 8px 10px -6px rgba(0,0,0,.1); }
            .shadow-orizona { box-shadow: 0 10px 30px -5px rgba(245,48,3,0.25); }

            /* Transitions */
            .transition-all { transition: all 0.15s ease; }
            .transition { transition: color 0.15s ease, background-color 0.15s ease, border-color 0.15s ease; }

            /* Header */
            .header-glass {
                background: rgba(253,253,252,0.92);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                border-bottom: 1px solid #e5e5e4;
            }
            @media (prefers-color-scheme: dark) {
                .header-glass {
                    background: rgba(10,10,10,0.92);
                    border-bottom-color: #3E3E3A;
                }
            }

            /* Hero gradient background */
            .hero-bg {
                background: radial-gradient(ellipse 80% 60% at 50% -10%, rgba(245,48,3,0.08) 0%, transparent 70%);
            }
            @media (prefers-color-scheme: dark) {
                .hero-bg {
                    background: radial-gradient(ellipse 80% 60% at 50% -10%, rgba(245,48,3,0.12) 0%, transparent 70%);
                }
            }

            /* Orizona badge */
            .badge-orizona {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.375rem 0.875rem;
                background: #fff2f2;
                border: 1px solid #fca5a5;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 600;
                color: #f53003;
            }
            @media (prefers-color-scheme: dark) {
                .badge-orizona {
                    background: rgba(29,0,2,0.6);
                    border-color: rgba(220,38,38,0.3);
                    color: #f87171;
                }
            }

            /* Dot pulse */
            .dot-pulse {
                width: 0.5rem;
                height: 0.5rem;
                border-radius: 9999px;
                background-color: #f53003;
                animation: pulse 2s cubic-bezier(0.4,0,0.6,1) infinite;
            }
            @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

            /* Buttons */
            .btn-primary {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.75rem 1.75rem;
                background-color: #f53003;
                color: #fff;
                font-weight: 700;
                font-size: 0.9375rem;
                border-radius: 0.75rem;
                border: none;
                cursor: pointer;
                transition: all 0.2s ease;
                box-shadow: 0 4px 14px rgba(245,48,3,0.3);
                text-decoration: none;
            }
            .btn-primary:hover { background-color: #d42900; transform: translateY(-1px); box-shadow: 0 8px 20px rgba(245,48,3,0.35); }

            .btn-secondary {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.75rem 1.75rem;
                background-color: transparent;
                color: #1b1b18;
                font-weight: 600;
                font-size: 0.9375rem;
                border-radius: 0.75rem;
                border: 1px solid #e5e5e4;
                cursor: pointer;
                transition: all 0.2s ease;
                text-decoration: none;
            }
            .btn-secondary:hover { background-color: #f5f5f4; border-color: #d4d4d0; }
            @media (prefers-color-scheme: dark) {
                .btn-secondary { color: #EDEDEC; border-color: #3E3E3A; }
                .btn-secondary:hover { background-color: #161615; }
            }

            /* Feature cards */
            .feature-card {
                background-color: #FDFDFC;
                border: 1px solid #e5e5e4;
                border-radius: 1rem;
                padding: 1.5rem;
                transition: all 0.2s ease;
            }
            .feature-card:hover { border-color: #f53003; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
            @media (prefers-color-scheme: dark) {
                .feature-card { background-color: #161615; border-color: #3E3E3A; }
                .feature-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.3); }
            }

            .feature-icon {
                width: 3rem;
                height: 3rem;
                border-radius: 0.75rem;
                background: #fff2f2;
                border: 1px solid rgba(245,48,3,0.15);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.25rem;
                margin-bottom: 1rem;
            }
            @media (prefers-color-scheme: dark) {
                .feature-icon { background: rgba(29,0,2,0.5); border-color: rgba(245,48,3,0.2); }
            }

            /* Role cards */
            .role-card {
                background-color: #FDFDFC;
                border: 1px solid #e5e5e4;
                border-radius: 1.25rem;
                padding: 2rem 1.5rem;
                text-align: center;
                transition: all 0.2s ease;
            }
            .role-card:hover { border-color: #f53003; }
            @media (prefers-color-scheme: dark) {
                .role-card { background-color: #161615; border-color: #3E3E3A; }
            }

            .role-avatar {
                width: 4rem;
                height: 4rem;
                border-radius: 9999px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.75rem;
                margin: 0 auto 1rem;
                background: linear-gradient(135deg, #fff2f2, #ffe4e1);
                border: 2px solid rgba(245,48,3,0.15);
            }
            @media (prefers-color-scheme: dark) {
                .role-avatar { background: linear-gradient(135deg, rgba(29,0,2,0.6), rgba(69,10,10,0.4)); }
            }

            /* Step indicator */
            .step-number {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 2rem;
                height: 2rem;
                border-radius: 9999px;
                background-color: #f53003;
                color: #fff;
                font-size: 0.8125rem;
                font-weight: 700;
                flex-shrink: 0;
            }

            .step-card {
                background-color: #FDFDFC;
                border: 1px solid #e5e5e4;
                border-radius: 1rem;
                padding: 1.25rem 1.5rem;
                display: flex;
                align-items: flex-start;
                gap: 1rem;
            }
            @media (prefers-color-scheme: dark) {
                .step-card { background-color: #161615; border-color: #3E3E3A; }
            }

            /* Stat items */
            .stat-item {
                text-align: center;
                padding: 1.5rem;
                background-color: #FDFDFC;
                border: 1px solid #e5e5e4;
                border-radius: 1rem;
            }
            @media (prefers-color-scheme: dark) {
                .stat-item { background-color: #161615; border-color: #3E3E3A; }
            }

            /* Grid helpers */
            @media (min-width: 48rem) {
                .md-grid-2 { grid-template-columns: repeat(2, 1fr); }
                .md-grid-3 { grid-template-columns: repeat(3, 1fr); }
                .md-grid-4 { grid-template-columns: repeat(4, 1fr); }
                .md-flex-row { flex-direction: row; }
                .md-text-left { text-align: left; }
                .md-hidden { display: none; }
                .md-flex { display: flex; }
                .md-text-5xl { font-size: 3rem; line-height: 1; }
                .md-text-6xl { font-size: 3.75rem; line-height: 1; }
            }

            /* Divider */
            .section-divider {
                border: none;
                height: 1px;
                background: linear-gradient(to right, transparent, #e5e5e4, transparent);
                margin-block: 0;
            }
            @media (prefers-color-scheme: dark) {
                .section-divider { background: linear-gradient(to right, transparent, #3E3E3A, transparent); }
            }

            /* Footer */
            .footer-area {
                border-top: 1px solid #e5e5e4;
                padding: 2rem 0;
                font-size: 0.8125rem;
                color: #706f6c;
            }
            @media (prefers-color-scheme: dark) {
                .footer-area { border-top-color: #3E3E3A; color: #A1A09A; }
            }
        </style>
    @endif
</head>

<body class="bg-surface">

    {{-- ── HEADER / NAVBAR ─────────────────────────────────────────────── --}}
    <header class="header-glass fixed top-0 left-0 right-0 z-50">
        <div class="container" style="padding-block: 1rem;">
            <div class="flex items-center justify-between">

                {{-- Logo --}}
                <a href="/" class="flex items-center gap-3" style="text-decoration:none;">
                    <div style="width:2.25rem; height:2.25rem; border-radius:0.625rem; background:#f53003; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:1.1rem; color:#fff; flex-shrink:0; box-shadow:0 4px 12px rgba(245,48,3,0.3);">
                        O
                    </div>
                    <span style="font-size:1.375rem; font-weight:800; letter-spacing:-0.025em; color:#1b1b18;" class="text-main">Orizona</span>
                </a>

                {{-- Nav desktop --}}
                <nav class="md-flex hidden items-center gap-8" style="font-size:0.875rem; font-weight:500;">
                    <a href="#fonctionnalites" class="text-muted transition" style="text-decoration:none;">Fonctionnalités</a>
                    <a href="#espaces" class="text-muted transition" style="text-decoration:none;">Espaces</a>
                    <a href="#comment" class="text-muted transition" style="text-decoration:none;">Comment ça marche</a>
                    <a href="{{ route('how-it-works') }}" class="text-muted transition" style="text-decoration:none;">Guide complet</a>
                </nav>

                {{-- CTA Auth --}}
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-primary" style="padding: 0.5rem 1.25rem; font-size:0.875rem;">
                            Mon Dashboard →
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn-secondary" style="padding: 0.5rem 1.25rem; font-size:0.875rem;">
                            Se connecter
                        </a>
                        <a href="{{ route('register') }}" class="btn-primary" style="padding: 0.5rem 1.25rem; font-size:0.875rem;">
                            Rejoindre →
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    {{-- ── HERO ─────────────────────────────────────────────────────────── --}}
    <section class="hero-bg" style="padding-top: 8rem; padding-bottom: 5rem;">
        <div class="container" style="max-width: 64rem; text-align: center;">

            {{-- Badge --}}
            <div style="display:flex; justify-content:center; margin-bottom: 1.5rem;">
                <span class="badge-orizona">
                    <span class="dot-pulse"></span>
                    Plateforme Immobilière ORIZONA — Togo
                </span>
            </div>

            {{-- Headline --}}
            <h1 class="text-main tracking-tight" style="font-size: clamp(2.25rem, 6vw, 4rem); font-weight: 800; line-height: 1.1; margin-bottom: 1.5rem;">
                Trouvez, gérez et louez des propriétés
                <span style="color:#f53003;"> en toute sérénité.</span>
            </h1>

            <p class="text-muted leading-relaxed" style="font-size:1.0625rem; max-width: 44rem; margin: 0 auto 2.5rem;">
                Orizona connecte les clients, agents immobiliers et propriétaires sur une plateforme sécurisée, avec contrats numériques, messagerie intégrée et suivi en temps réel.
            </p>

            {{-- CTA Buttons --}}
            <div class="flex flex-wrap justify-center gap-4" style="margin-bottom:3.5rem;">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary">
                        Accéder à mon espace →
                    </a>
                @else
                    <a href="{{ route('register') }}" class="btn-primary">
                        Créer un compte gratuitement
                    </a>
                    <a href="{{ route('login') }}" class="btn-secondary">
                        J'ai déjà un compte
                    </a>
                @endauth
            </div>

            {{-- Stats Row --}}
            <div class="grid" style="grid-template-columns: repeat(3, 1fr); gap: 1rem; max-width: 40rem; margin: 0 auto;">
                <div class="stat-item">
                    <p style="font-size:1.75rem; font-weight:800; color:#f53003; line-height:1;">3</p>
                    <p class="text-muted" style="font-size:0.8125rem; margin-top:0.25rem;">Rôles dédiés</p>
                </div>
                <div class="stat-item">
                    <p style="font-size:1.75rem; font-weight:800; color:#f53003; line-height:1;">100%</p>
                    <p class="text-muted" style="font-size:0.8125rem; margin-top:0.25rem;">Processus numérique</p>
                </div>
                <div class="stat-item">
                    <p style="font-size:1.75rem; font-weight:800; color:#f53003; line-height:1;">24/7</p>
                    <p class="text-muted" style="font-size:0.8125rem; margin-top:0.25rem;">Accès en ligne</p>
                </div>
            </div>
        </div>
    </section>

    <hr class="section-divider">

    {{-- ── FONCTIONNALITÉS ──────────────────────────────────────────────── --}}
    <section id="fonctionnalites" style="padding-block: 5rem;" class="bg-muted-light">
        <div class="container" style="max-width: 72rem;">
            <div style="text-align:center; margin-bottom:3rem;">
                <span style="font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#f53003;">Fonctionnalités</span>
                <h2 class="text-main" style="font-size:1.875rem; font-weight:800; margin-top:0.5rem; letter-spacing:-0.025em;">Tout ce dont vous avez besoin</h2>
                <p class="text-muted leading-relaxed" style="margin-top:0.75rem; max-width:40rem; margin-inline:auto;">
                    Une plateforme complète pour gérer l'écosystème immobilier de bout en bout.
                </p>
            </div>

            <div class="grid" style="grid-template-columns: repeat(1, 1fr); gap: 1rem;">
                <div style="display:grid; gap:1rem; grid-template-columns: 1fr 1fr 1fr;" class="md-grid-3">

                    <div class="feature-card">
                        <div class="feature-icon">🏠</div>
                        <h3 class="text-main" style="font-size:1rem; font-weight:700; margin-bottom:0.5rem;">Répertoire de Propriétés</h3>
                        <p class="text-muted" style="font-size:0.875rem; line-height:1.6;">Parcourez les biens disponibles avec photos, vidéos, prix et localisation précise.</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">📋</div>
                        <h3 class="text-main" style="font-size:1rem; font-weight:700; margin-bottom:0.5rem;">Contrats Numériques</h3>
                        <p class="text-muted" style="font-size:0.875rem; line-height:1.6;">Génération automatique des contrats d'occupation avec validation multi-parties.</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">💬</div>
                        <h3 class="text-main" style="font-size:1rem; font-weight:700; margin-bottom:0.5rem;">Messagerie Intégrée</h3>
                        <p class="text-muted" style="font-size:0.875rem; line-height:1.6;">Communiquez directement avec les agents et l'administration depuis votre espace.</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">⭐</div>
                        <h3 class="text-main" style="font-size:1rem; font-weight:700; margin-bottom:0.5rem;">Score de Qualité</h3>
                        <p class="text-muted" style="font-size:0.875rem; line-height:1.6;">Classement automatique des biens de 1 à 5 étoiles selon leur état et équipements.</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">🔔</div>
                        <h3 class="text-main" style="font-size:1rem; font-weight:700; margin-bottom:0.5rem;">Notifications Temps Réel</h3>
                        <p class="text-muted" style="font-size:0.875rem; line-height:1.6;">Soyez alerté à chaque étape : demandes, approbations, messages et contrats.</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">🛡️</div>
                        <h3 class="text-main" style="font-size:1rem; font-weight:700; margin-bottom:0.5rem;">Validation Multi-Niveaux</h3>
                        <p class="text-muted" style="font-size:0.875rem; line-height:1.6;">Agent → Propriétaire → Admin : chaque occupation est validée à chaque étape.</p>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <hr class="section-divider">

    {{-- ── ESPACES DÉDIÉS ───────────────────────────────────────────────── --}}
    <section id="espaces" style="padding-block: 5rem;">
        <div class="container" style="max-width: 64rem;">
            <div style="text-align:center; margin-bottom:3rem;">
                <span style="font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#f53003;">Espaces Dédiés</span>
                <h2 class="text-main" style="font-size:1.875rem; font-weight:800; margin-top:0.5rem; letter-spacing:-0.025em;">Une plateforme, trois rôles</h2>
                <p class="text-muted" style="margin-top:0.75rem;">Chaque utilisateur dispose d'un espace personnalisé et adapté à son rôle.</p>
            </div>

            <div style="display:grid; grid-template-columns: repeat(1,1fr); gap:1.25rem;" class="md-grid-3">

                <div class="role-card">
                    <div class="role-avatar">👤</div>
                    <h3 class="text-main" style="font-size:1.125rem; font-weight:700; margin-bottom:0.5rem;">Client</h3>
                    <p class="text-muted" style="font-size:0.875rem; line-height:1.6; margin-bottom:1rem;">Recherchez et occupez des propriétés. Suivez vos demandes, consultez vos contrats et contactez votre agent.</p>
                    <ul class="text-muted" style="font-size:0.8125rem; text-align:left; list-style:none; space-y:0.25rem;">
                        <li style="padding:0.2rem 0;">✓ Parcourir les biens disponibles</li>
                        <li style="padding:0.2rem 0;">✓ Soumettre une demande d'occupation</li>
                        <li style="padding:0.2rem 0;">✓ Accéder à son contrat</li>
                        <li style="padding:0.2rem 0;">✓ Contacter l'administration</li>
                    </ul>
                </div>

                <div class="role-card" style="border-color:#f53003; position:relative; overflow:hidden;">
                    <div style="position:absolute; top:0.75rem; right:0.75rem; background:#f53003; color:#fff; font-size:0.65rem; font-weight:700; padding:0.2rem 0.6rem; border-radius:9999px; text-transform:uppercase; letter-spacing:0.05em;">Principal</div>
                    <div class="role-avatar">🏢</div>
                    <h3 class="text-main" style="font-size:1.125rem; font-weight:700; margin-bottom:0.5rem;">Agent Immobilier</h3>
                    <p class="text-muted" style="font-size:0.875rem; line-height:1.6; margin-bottom:1rem;">Gérez le portefeuille de propriétés et accompagnez les clients. Première validation des demandes d'occupation.</p>
                    <ul class="text-muted" style="font-size:0.8125rem; text-align:left; list-style:none;">
                        <li style="padding:0.2rem 0;">✓ Répertorier des propriétés</li>
                        <li style="padding:0.2rem 0;">✓ Approuver / refuser les demandes</li>
                        <li style="padding:0.2rem 0;">✓ Messagerie avec les clients</li>
                        <li style="padding:0.2rem 0;">✓ Visualiser son portefeuille</li>
                    </ul>
                </div>

                <div class="role-card">
                    <div class="role-avatar">🏡</div>
                    <h3 class="text-main" style="font-size:1.125rem; font-weight:700; margin-bottom:0.5rem;">Propriétaire</h3>
                    <p class="text-muted" style="font-size:0.875rem; line-height:1.6; margin-bottom:1rem;">Consultez vos biens mis en gestion et validez en dernier ressort toute demande d'occupation de votre propriété.</p>
                    <ul class="text-muted" style="font-size:0.8125rem; text-align:left; list-style:none;">
                        <li style="padding:0.2rem 0;">✓ Voir ses propriétés</li>
                        <li style="padding:0.2rem 0;">✓ Valider / refuser les occupations</li>
                        <li style="padding:0.2rem 0;">✓ Consulter ses contrats actifs</li>
                        <li style="padding:0.2rem 0;">✓ Suivi des locataires</li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <hr class="section-divider">

    {{-- ── COMMENT ÇA MARCHE ───────────────────────────────────────────── --}}
    <section id="comment" class="bg-muted-light" style="padding-block: 5rem;">
        <div class="container" style="max-width: 56rem;">
            <div style="text-align:center; margin-bottom:3rem;">
                <span style="font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#f53003;">Processus</span>
                <h2 class="text-main" style="font-size:1.875rem; font-weight:800; margin-top:0.5rem; letter-spacing:-0.025em;">Comment ça marche ?</h2>
                <p class="text-muted" style="margin-top:0.75rem;">Un processus simple, transparent et sécurisé en 4 étapes.</p>
            </div>

            <div style="display:grid; gap:0.875rem;">
                <div class="step-card">
                    <span class="step-number">1</span>
                    <div>
                        <h3 class="text-main" style="font-size:0.9375rem; font-weight:700; margin-bottom:0.25rem;">Le client trouve le bien qui lui convient</h3>
                        <p class="text-muted" style="font-size:0.875rem; line-height:1.6;">Il parcourt les propriétés disponibles et clique sur <strong>"Je suis intéressé(e)"</strong> pour soumettre sa demande.</p>
                    </div>
                </div>

                <div class="step-card">
                    <span class="step-number">2</span>
                    <div>
                        <h3 class="text-main" style="font-size:0.9375rem; font-weight:700; margin-bottom:0.25rem;">L'agent immobilier examine et valide</h3>
                        <p class="text-muted" style="font-size:0.875rem; line-height:1.6;">L'agent reçoit la demande, contacte le client, et donne son approbation (ou refus motivé).</p>
                    </div>
                </div>

                <div class="step-card">
                    <span class="step-number">3</span>
                    <div>
                        <h3 class="text-main" style="font-size:0.9375rem; font-weight:700; margin-bottom:0.25rem;">Le propriétaire donne son accord final</h3>
                        <p class="text-muted" style="font-size:0.875rem; line-height:1.6;">Le propriétaire examine le dossier et valide ou refuse l'occupation de son bien.</p>
                    </div>
                </div>

                <div class="step-card">
                    <span class="step-number">4</span>
                    <div>
                        <h3 class="text-main" style="font-size:0.9375rem; font-weight:700; margin-bottom:0.25rem;">Le contrat est généré et l'occupation débute</h3>
                        <p class="text-muted" style="font-size:0.875rem; line-height:1.6;">Un contrat numérique est automatiquement créé. Toutes les parties peuvent le consulter à tout moment.</p>
                    </div>
                </div>
            </div>

            <div style="text-align:center; margin-top:2rem;">
                <a href="{{ route('how-it-works') }}" class="btn-secondary" style="font-size:0.875rem;">
                    Voir le guide complet →
                </a>
            </div>
        </div>
    </section>

    <hr class="section-divider">

    {{-- ── CTA FINAL ────────────────────────────────────────────────────── --}}
    <section style="padding-block: 5rem;">
        <div class="container" style="max-width: 48rem; text-align:center;">
            <div style="background:#fff2f2; border:1px solid rgba(245,48,3,0.2); border-radius:1.5rem; padding:3rem 2rem;" class="bg-accent-light">
                <h2 class="text-main" style="font-size:1.875rem; font-weight:800; margin-bottom:1rem; letter-spacing:-0.025em;">Prêt à commencer ?</h2>
                <p class="text-muted leading-relaxed" style="font-size:1rem; margin-bottom:2rem; max-width:36rem; margin-inline:auto;">
                    Rejoignez la plateforme et profitez d'une gestion immobilière 100% numérique, simple et sécurisée.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-primary">
                            Accéder à mon espace →
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="btn-primary">
                            Créer un compte
                        </a>
                        <a href="{{ route('login') }}" class="btn-secondary">
                            Se connecter
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    {{-- ── FOOTER ───────────────────────────────────────────────────────── --}}
    <footer class="footer-area">
        <div class="container">
            <div class="flex flex-wrap items-center justify-between gap-4" style="font-size:0.8125rem;">
                <div class="flex items-center gap-3">
                    <div style="width:1.75rem; height:1.75rem; border-radius:0.5rem; background:#f53003; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:0.9rem; color:#fff;">O</div>
                    <span class="text-main" style="font-weight:600;">Orizona</span>
                </div>
                <p>© {{ date('Y') }} Orizona — Plateforme Immobilière. Tous droits réservés.</p>
                <p style="font-size:0.75rem; opacity:0.6;">Laravel v{{ app()->version() }} · PHP v{{ PHP_VERSION }}</p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth active state for nav links
        document.querySelectorAll('nav a[href^="#"]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        // Hover effect on nav links matching current section
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 20) {
                header.style.boxShadow = '0 2px 12px rgba(0,0,0,0.08)';
            } else {
                header.style.boxShadow = 'none';
            }
        });
    </script>
</body>
</html>
