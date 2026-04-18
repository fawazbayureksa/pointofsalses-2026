<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
        content="The all-in-one ERP & Point of Sale platform built for modern businesses. Manage sales, inventory, finance and multiple outlets from one place.">
    <title>{{ config('app.name', 'POS ERP') }} — Smart Business Management</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* =============================================
           BASE & DESIGN TOKENS
        ============================================= */
        * {
            font-family: 'Inter', sans-serif;
            scroll-behavior: smooth;
        }

        body {
            background-color: #060b18;
            color: #e2e8f0;
        }

        /* =============================================
           WCAG 2.4.1 — SKIP NAVIGATION
        ============================================= */
        .skip-link {
            position: absolute;
            top: -100%;
            left: 1rem;
            z-index: 9999;
            padding: 0.75rem 1.5rem;
            background: #0891b2;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.875rem;
            border-radius: 0 0 0.75rem 0.75rem;
            text-decoration: none;
            transition: top 0.2s ease;
        }

        .skip-link:focus {
            top: 0;
            outline: 3px solid #ffffff;
            outline-offset: 2px;
        }

        /* =============================================
           WCAG 2.4.7 — FOCUS INDICATORS
           All interactive elements must have a clear,
           visible focus indicator with ≥3:1 contrast.
        ============================================= */
        :focus-visible {
            outline: 3px solid #22d3ee;
            outline-offset: 3px;
            border-radius: 4px;
        }

        /* Remove default outline only for mouse users */
        :focus:not(:focus-visible) {
            outline: none;
        }

        /* =============================================
           WCAG 1.4.3 — CONTRAST (AA)
           All text must meet 4.5:1 (normal) / 3:1 (large/bold)
        ============================================= */

        /* Gradient mesh background */
        .hero-bg {
            background: #060b18;
            background-image:
                radial-gradient(ellipse 80% 60% at 20% 20%, rgba(8, 145, 178, 0.18) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 80%, rgba(16, 185, 129, 0.12) 0%, transparent 60%),
                radial-gradient(ellipse 50% 40% at 60% 10%, rgba(34, 211, 238, 0.1) 0%, transparent 50%);
        }

        /* Gradient text — decorative only; visible text color fallback ensured */
        .gradient-text {
            background: linear-gradient(135deg, #22d3ee 0%, #0891b2 25%, #10b981 60%, #34d399 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            /* Fallback for browsers without gradient-text support */
            color: #22d3ee;
        }

        /* Glass card */
        .glass {
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .glass-dark {
            background: rgba(13, 17, 35, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(8, 145, 178, 0.2);
        }

        .glass-light {
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* =============================================
           WCAG 2.3.3 / 2.3.1 — REDUCE MOTION
           Disable / tone down animations for
           users who prefer reduced motion.
        ============================================= */
        @keyframes float1 {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            50% {
                transform: translate(30px, -20px) scale(1.08);
            }
        }

        @keyframes float2 {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            50% {
                transform: translate(-20px, 25px) scale(1.06);
            }
        }

        @keyframes float3 {

            0%,
            100% {
                transform: translate(0, 0);
            }

            33% {
                transform: translate(15px, -15px);
            }

            66% {
                transform: translate(-10px, 10px);
            }
        }

        @keyframes pulse-glow {

            0%,
            100% {
                opacity: 0.5;
            }

            50% {
                opacity: 1;
            }
        }

        @keyframes slide-up {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounce-subtle {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-6px);
            }
        }

        .float-1 { animation: float1 7s ease-in-out infinite; }
        .float-2 { animation: float2 9s ease-in-out infinite; }
        .float-3 { animation: float3 11s ease-in-out infinite; }
        .pulse-glow { animation: pulse-glow 3s ease-in-out infinite; }
        .bounce-subtle { animation: bounce-subtle 3s ease-in-out infinite; }

        .animate-slide-up   { animation: slide-up 0.7s ease forwards; }
        .animate-slide-up-2 { animation: slide-up 0.7s 0.15s ease both; }
        .animate-slide-up-3 { animation: slide-up 0.7s 0.3s ease both; }
        .animate-slide-up-4 { animation: slide-up 0.7s 0.45s ease both; }

        /* WCAG 2.3.3 — Honour prefers-reduced-motion */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }

            .float-1,
            .float-2,
            .float-3,
            .pulse-glow,
            .bounce-subtle,
            .animate-slide-up,
            .animate-slide-up-2,
            .animate-slide-up-3,
            .animate-slide-up-4 {
                animation: none !important;
            }

            .reveal,
            .reveal-left,
            .reveal-right {
                opacity: 1 !important;
                transform: none !important;
                transition: none !important;
            }
        }

        /* =============================================
           COMPONENT STYLES
        ============================================= */

        /* Hover card lift */
        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 60px rgba(8, 145, 178, 0.2);
            border-color: rgba(8, 145, 178, 0.4);
        }

        /* Glow button */
        .btn-primary {
            background: linear-gradient(135deg, #0891b2, #06b6d4);
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(8, 145, 178, 0.35);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(8, 145, 178, 0.55);
            background: linear-gradient(135deg, #06b6d4, #22d3ee);
        }

        .btn-primary:focus-visible {
            outline: 3px solid #ffffff;
            outline-offset: 3px;
            box-shadow: 0 0 0 6px rgba(8, 145, 178, 0.4);
        }

        .btn-secondary {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.25);
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.4);
            transform: translateY(-2px);
        }

        .btn-secondary:focus-visible {
            outline: 3px solid #22d3ee;
            outline-offset: 3px;
        }

        /* Navbar */
        .navbar-scrolled {
            background: rgba(6, 11, 24, 0.95) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        /* Feature icon */
        .feature-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        /* Orb blobs — purely decorative */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
        }

        /* Stats counter */
        .stat-number {
            font-size: 2.8rem;
            font-weight: 800;
            line-height: 1;
        }

        /* Download badge */
        .store-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 14px;
            padding: 12px 20px;
            transition: all 0.3s ease;
            text-decoration: none;
            color: white;
            backdrop-filter: blur(8px);
        }

        .store-btn:hover {
            background: rgba(255, 255, 255, 0.13);
            border-color: rgba(8, 145, 178, 0.5);
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(8, 145, 178, 0.25);
        }

        .store-btn:focus-visible {
            outline: 3px solid #22d3ee;
            outline-offset: 3px;
        }

        /* Section divider */
        .section-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(8, 145, 178, 0.3), transparent);
        }

        /* Testimonial card */
        .testimonial-stars {
            color: #f59e0b;
            letter-spacing: 2px;
        }

        /* Mobile menu */
        #mobile-menu {
            transition: max-height 0.4s ease, opacity 0.4s ease;
            max-height: 0;
            opacity: 0;
            overflow: hidden;
        }

        #mobile-menu.open {
            max-height: 500px;
            opacity: 1;
        }

        /* Pricing card highlight */
        .pricing-popular {
            border-color: rgba(8, 145, 178, 0.6) !important;
            background: linear-gradient(145deg, rgba(8, 145, 178, 0.12), rgba(6, 182, 212, 0.08)) !important;
            transform: scale(1.02);
        }

        /* Scroll reveal */
        .reveal {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .reveal-left {
            opacity: 0;
            transform: translateX(-28px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .reveal-left.visible {
            opacity: 1;
            transform: translateX(0);
        }

        .reveal-right {
            opacity: 0;
            transform: translateX(28px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .reveal-right.visible {
            opacity: 1;
            transform: translateX(0);
        }

        /* Module pill tag */
        .module-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Progress bar animation */
        @keyframes progress-fill {
            from { width: 0; }
            to   { width: var(--fill); }
        }

        .progress-bar {
            animation: progress-fill 1.5s 0.5s ease forwards;
            width: 0;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #060b18; }
        ::-webkit-scrollbar-thumb { background: rgba(8, 145, 178, 0.4); border-radius: 3px; }

        /* =============================================
           WCAG 1.4.3 — HIGH CONTRAST TEXT OVERRIDES
           Text below contrast threshold is lifted.
        ============================================= */

        /* slate-400 (#94a3b8) on #060b18 ≈ 5.3:1 — PASSES AA */
        /* slate-500 (#64748b) on #060b18 ≈ 3.0:1 — FAILS AA for body text */
        /* Replace slate-500 used for body copy with slate-400 */
        .text-accessible-muted {
            color: #94a3b8; /* ~5.3:1 on #060b18 — passes AA */
        }

        /* Nav link focus ring */
        nav a:focus-visible {
            outline: 3px solid #22d3ee;
            outline-offset: 3px;
            border-radius: 4px;
        }

        /* Social icon links — clarify hit area */
        .social-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.75rem;
            transition: all 0.2s ease;
        }

        .social-link:focus-visible {
            outline: 3px solid #22d3ee;
            outline-offset: 2px;
        }
    </style>
</head>

<body>

    {{-- ============================================================
         WCAG 2.4.1 — BYPASS BLOCKS
         Skip link allows keyboard users to jump to main content.
    ============================================================ --}}
    <a href="#main-content" class="skip-link">Skip to main content</a>

    {{-- ============================
         NAVIGATION
    ============================= --}}
    <nav id="navbar"
         class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 py-4 px-6"
         aria-label="Primary navigation">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            {{-- Logo --}}
            <a href="/" class="flex items-center gap-3" aria-label="{{ config('app.name', 'POS ERP') }} — Home">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-500 to-teal-600 flex items-center justify-center shadow-lg shadow-cyan-500/30"
                     aria-hidden="true">
                    <i class="fas fa-cash-register text-white text-sm" aria-hidden="true"></i>
                </div>
                <span class="font-bold text-lg text-white tracking-tight">{{ config('app.name', 'POS ERP') }}</span>
            </a>

            {{-- Desktop nav --}}
            <div class="hidden md:flex items-center gap-8" role="list">
                <a href="#features" role="listitem" class="text-sm text-slate-300 hover:text-white transition-colors">Features</a>
                <a href="#modules"  role="listitem" class="text-sm text-slate-300 hover:text-white transition-colors">Modules</a>
                <a href="#pricing"  role="listitem" class="text-sm text-slate-300 hover:text-white transition-colors">Pricing</a>
                <a href="#download" role="listitem" class="text-sm text-slate-300 hover:text-white transition-colors">Download</a>
                <a href="{{ route('docs') }}" role="listitem" class="text-sm text-slate-300 hover:text-white transition-colors">Docs</a>
            </div>

            {{-- CTA --}}
            <div class="hidden md:flex items-center gap-3">
                @auth
                    <a href="{{ url('/admin/dashboard') }}"
                        class="btn-primary text-white text-sm font-semibold px-5 py-2.5 rounded-xl">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="btn-secondary text-sm text-slate-200 font-medium px-5 py-2.5 rounded-xl">
                        Sign In
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="btn-primary text-white text-sm font-semibold px-5 py-2.5 rounded-xl">
                            Get Started Free
                        </a>
                    @endif
                @endauth
            </div>

            {{-- WCAG 4.1.2 — Mobile hamburger with full ARIA --}}
            <button id="mobile-menu-btn"
                    onclick="toggleMobileMenu()"
                    class="md:hidden text-slate-300 hover:text-white p-2 rounded-lg transition-colors"
                    aria-label="Open navigation menu"
                    aria-expanded="false"
                    aria-controls="mobile-menu">
                <i class="fas fa-bars text-xl" aria-hidden="true"></i>
            </button>
        </div>

        {{-- Mobile menu — WCAG 4.1.2 role + aria-labelledby --}}
        <div id="mobile-menu"
             class="md:hidden glass-dark mx-0 mt-3 rounded-2xl"
             role="dialog"
             aria-label="Mobile navigation menu">
            <div class="p-5 flex flex-col gap-3">
                <a href="#features"
                    class="text-sm text-slate-200 hover:text-white py-2 border-b border-white/5">Features</a>
                <a href="#modules"
                    class="text-sm text-slate-200 hover:text-white py-2 border-b border-white/5">Modules</a>
                <a href="#pricing"
                    class="text-sm text-slate-200 hover:text-white py-2 border-b border-white/5">Pricing</a>
                <a href="#download"
                    class="text-sm text-slate-200 hover:text-white py-2 border-b border-white/5">Download</a>
                <a href="{{ route('docs') }}"
                    class="text-sm text-slate-200 hover:text-white py-2 border-b border-white/5">Docs</a>
                <div class="flex gap-3 pt-2">
                    @auth
                        <a href="{{ url('/admin/dashboard') }}"
                            class="btn-primary text-white text-sm font-semibold px-5 py-2.5 rounded-xl flex-1 text-center">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}"
                            class="btn-secondary text-sm text-slate-200 font-medium px-5 py-2.5 rounded-xl flex-1 text-center">Sign In</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="btn-primary text-white text-sm font-semibold px-5 py-2.5 rounded-xl flex-1 text-center">Get Started</a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </nav>


    {{-- WCAG 1.3.6 — <main> landmark so screen readers can jump directly --}}
    <main id="main-content">

        {{-- ============================
             HERO SECTION
        ============================= --}}
        <section class="hero-bg relative min-h-screen flex items-center overflow-hidden pt-20"
                 aria-label="Hero — smart business management">

            {{-- Decorative background orbs — hidden from AT --}}
            <div class="orb w-96 h-96 bg-cyan-600/20 top-10 -left-20 float-1" aria-hidden="true"></div>
            <div class="orb w-80 h-80 bg-teal-600/15 bottom-20 right-0 float-2"  aria-hidden="true"></div>
            <div class="orb w-64 h-64 bg-emerald-500/10 top-1/2 right-1/4 float-3" aria-hidden="true"></div>

            {{-- Decorative grid overlay --}}
            <div class="absolute inset-0 opacity-[0.03]" aria-hidden="true"
                style="background-image: linear-gradient(rgba(255,255,255,0.5) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.5) 1px, transparent 1px); background-size: 60px 60px;">
            </div>

            <div class="relative z-10 max-w-7xl mx-auto px-6 py-20 w-full">
                <div class="grid lg:grid-cols-2 gap-16 items-center">
                    {{-- Left content --}}
                    <div>
                        {{-- Badge --}}
                        <div class="animate-slide-up inline-flex items-center gap-2 glass rounded-full px-4 py-2 mb-8"
                             role="status" aria-live="polite">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 pulse-glow" aria-hidden="true"></span>
                            <span class="text-xs font-medium text-emerald-300">Now with Multi-Outlet Support</span>
                        </div>

                        {{-- WCAG 1.3.1 — Single h1 per page --}}
                        <h1 class="animate-slide-up-2 text-5xl lg:text-6xl xl:text-7xl font-black leading-[1.05] tracking-tight mb-6">
                            <span class="text-white">Manage Your</span><br>
                            <span class="gradient-text">Business Smarter</span><br>
                            <span class="text-white">Not Harder.</span>
                        </h1>

                        <p class="animate-slide-up-3 text-lg text-slate-300 leading-relaxed mb-10 max-w-lg">
                            The complete ERP &amp; POS platform for modern retail. From cashier to boardroom — control sales,
                            inventory, finance, and multiple outlets from one elegant dashboard.
                        </p>

                        {{-- CTA row --}}
                        <div class="animate-slide-up-4 flex flex-wrap gap-4 mb-12">
                            @auth
                                <a href="{{ url('/admin/dashboard') }}"
                                    class="btn-primary text-white font-semibold px-8 py-4 rounded-2xl text-base flex items-center gap-2">
                                    <i class="fas fa-tachometer-alt" aria-hidden="true"></i>
                                    Open Dashboard
                                </a>
                            @else
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}"
                                        class="btn-primary text-white font-semibold px-8 py-4 rounded-2xl text-base flex items-center gap-2">
                                        <i class="fas fa-rocket" aria-hidden="true"></i>
                                        Start Free Trial
                                    </a>
                                @endif
                                <a href="{{ route('login') }}"
                                    class="btn-secondary text-slate-200 font-medium px-8 py-4 rounded-2xl text-base flex items-center gap-2">
                                    <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
                                    Sign In
                                </a>
                            @endauth
                        </div>

                        {{-- Trust badges — WCAG 1.4.3 contrast raised --}}
                        <ul class="animate-slide-up-4 flex flex-wrap items-center gap-6 list-none p-0 m-0">
                            <li class="flex items-center gap-2 text-slate-300 text-sm">
                                <i class="fas fa-shield-alt text-emerald-400" aria-hidden="true"></i>
                                <span>Bank-grade security</span>
                            </li>
                            <li class="flex items-center gap-2 text-slate-300 text-sm">
                                <i class="fas fa-bolt text-cyan-400" aria-hidden="true"></i>
                                <span>Real-time sync</span>
                            </li>
                            <li class="flex items-center gap-2 text-slate-300 text-sm">
                                <i class="fas fa-cloud text-teal-400" aria-hidden="true"></i>
                                <span>Cloud-based</span>
                            </li>
                            <li class="flex items-center gap-2 text-slate-300 text-sm">
                                <i class="fas fa-gift text-amber-400" aria-hidden="true"></i>
                                <span>14-day free trial</span>
                            </li>
                        </ul>
                    </div>

                    {{-- Right: Dashboard mockup — purely decorative --}}
                    <div class="relative bounce-subtle hidden lg:block" aria-hidden="true">
                        <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/20 to-teal-500/10 rounded-3xl blur-3xl scale-105"></div>

                        {{-- Mock window --}}
                        <div class="relative glass-dark rounded-3xl overflow-hidden shadow-2xl shadow-cyan-900/40">
                            {{-- Title bar --}}
                            <div class="flex items-center gap-2 px-5 py-4 border-b border-white/5">
                                <div class="w-3 h-3 rounded-full bg-red-500/70"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-500/70"></div>
                                <div class="w-3 h-3 rounded-full bg-green-500/70"></div>
                                <div class="flex-1 text-center">
                                    <span class="text-xs text-slate-400 font-mono">{{ config('app.name') }} — Dashboard</span>
                                </div>
                            </div>

                            {{-- Mock dashboard body --}}
                            <div class="p-5 space-y-4">
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="glass rounded-2xl p-4">
                                        <p class="text-xs text-slate-400 mb-1">Today's Sales</p>
                                        <p class="text-xl font-bold text-white">Rp 4.2M</p>
                                        <p class="text-xs text-emerald-400 mt-1"><i class="fas fa-arrow-up"></i> +12.4%</p>
                                    </div>
                                    <div class="glass rounded-2xl p-4">
                                        <p class="text-xs text-slate-400 mb-1">Orders</p>
                                        <p class="text-xl font-bold text-white">138</p>
                                        <p class="text-xs text-emerald-400 mt-1"><i class="fas fa-arrow-up"></i> +8.1%</p>
                                    </div>
                                    <div class="glass rounded-2xl p-4">
                                        <p class="text-xs text-slate-400 mb-1">Stock Alerts</p>
                                        <p class="text-xl font-bold text-amber-400">7</p>
                                        <p class="text-xs text-amber-400 mt-1"><i class="fas fa-exclamation-triangle"></i> Low stock</p>
                                    </div>
                                </div>

                                    <div class="glass rounded-2xl p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <p class="text-xs font-semibold text-slate-300">Revenue — Last 7 Days</p>
                                            <span class="text-xs text-cyan-400">View report →</span>
                                        </div>
                                        <div class="flex items-end gap-2 h-20">
                                            <div class="flex-1 rounded-t-md bg-cyan-500/30" style="height:45%"></div>
                                            <div class="flex-1 rounded-t-md bg-cyan-500/50" style="height:60%"></div>
                                            <div class="flex-1 rounded-t-md bg-cyan-500/40" style="height:35%"></div>
                                            <div class="flex-1 rounded-t-md bg-cyan-500/60" style="height:75%"></div>
                                            <div class="flex-1 rounded-t-md bg-cyan-400/50" style="height:55%"></div>
                                            <div class="flex-1 rounded-t-md bg-cyan-400/70" style="height:85%"></div>
                                            <div class="flex-1 rounded-t-md bg-cyan-400"    style="height:100%"></div>
                                        </div>
                                    </div>

                                <div class="glass rounded-2xl p-4">
                                    <p class="text-xs font-semibold text-slate-300 mb-3">Recent Transactions</p>
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between text-xs">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                                                    <i class="fas fa-receipt text-emerald-400" style="font-size:9px"></i>
                                                </div>
                                                <span class="text-slate-300">ORD-20260001</span>
                                            </div>
                                            <span class="text-white font-medium">Rp 125,000</span>
                                            <span class="px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400">Paid</span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-lg bg-cyan-500/20 flex items-center justify-center">
                                                    <i class="fas fa-receipt text-cyan-400" style="font-size:9px"></i>
                                                </div>
                                                <span class="text-slate-300">ORD-20260002</span>
                                            </div>
                                            <span class="text-white font-medium">Rp 87,500</span>
                                            <span class="px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-400">Pending</span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-lg bg-teal-500/20 flex items-center justify-center">
                                                    <i class="fas fa-receipt text-teal-400" style="font-size:9px"></i>
                                                </div>
                                                <span class="text-slate-300">ORD-20260003</span>
                                            </div>
                                            <span class="text-white font-medium">Rp 240,000</span>
                                            <span class="px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400">Paid</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Floating badge cards --}}
                        <div class="absolute -bottom-6 -left-8 glass-dark rounded-2xl p-3 shadow-xl">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 flex items-center justify-center">
                                    <i class="fas fa-chart-line text-emerald-400 text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-300">Monthly Revenue</p>
                                    <p class="text-sm font-bold text-white">Rp 128M</p>
                                    <p class="text-xs text-emerald-400"><i class="fas fa-arrow-up"></i> 23% growth</p>
                                </div>
                            </div>
                        </div>

                        <div class="absolute -top-4 -right-4 glass-dark rounded-2xl p-3 shadow-xl">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-cyan-500/20 flex items-center justify-center">
                                    <i class="fas fa-store text-cyan-400 text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-300">Active Outlets</p>
                                    <p class="text-sm font-bold text-white">12 Outlets</p>
                                    <p class="text-xs text-cyan-400">All synced</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Decorative bottom wave --}}
            <div class="absolute bottom-0 left-0 right-0" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 80" class="block w-full"
                    style="fill:#060b18; opacity:0.8">
                    <path d="M0,40 C360,80 1080,0 1440,40 L1440,80 L0,80 Z" />
                </svg>
            </div>
        </section>


        {{-- ============================
             SOCIAL PROOF / STATS
        ============================= --}}
        <section class="py-20 bg-[#060b18]" aria-labelledby="stats-heading">
            <div class="max-w-7xl mx-auto px-6">
                {{-- WCAG 1.3.1 — visible label for the trusted-by list --}}
                <p id="stats-heading"
                   class="text-center text-xs font-semibold tracking-widest text-slate-400 uppercase mb-10">
                    Trusted by businesses across industries
                </p>

                {{-- Partner names — sematic list --}}
                <ul class="flex flex-wrap justify-center gap-10 mb-20 items-center list-none p-0 m-0"
                    aria-label="Partner businesses">
                    <li><span class="text-slate-400 font-bold text-lg tracking-tight">Kopi Nusantara</span></li>
                    <li><span class="text-slate-400 font-bold text-lg tracking-tight">Warung Pak Budi</span></li>
                    <li><span class="text-slate-400 font-bold text-lg tracking-tight">Toko Makmur</span></li>
                    <li><span class="text-slate-400 font-bold text-lg tracking-tight">Resto Bahari</span></li>
                    <li><span class="text-slate-400 font-bold text-lg tracking-tight">Minimart 24</span></li>
                </ul>

                {{-- Stats grid --}}
                <dl class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="reveal text-center glass rounded-3xl p-8 card-hover">
                        <dt class="text-slate-300 text-sm mb-2">Active Merchants</dt>
                        <dd class="stat-number gradient-text">12K+</dd>
                    </div>
                    <div class="reveal text-center glass rounded-3xl p-8 card-hover">
                        <dt class="text-slate-300 text-sm mb-2">Transactions / Month</dt>
                        <dd class="stat-number gradient-text">2.4M</dd>
                    </div>
                    <div class="reveal text-center glass rounded-3xl p-8 card-hover">
                        <dt class="text-slate-300 text-sm mb-2">Uptime SLA</dt>
                        <dd class="stat-number gradient-text">99.9%</dd>
                    </div>
                    <div class="reveal text-center glass rounded-3xl p-8 card-hover">
                        <dt class="text-slate-300 text-sm mb-2">Cities Covered</dt>
                        <dd class="stat-number gradient-text">48</dd>
                    </div>
                </dl>
            </div>
        </section>


        {{-- ============================
             FEATURES
        ============================= --}}
        <section id="features" class="py-24 bg-[#070c1b]" aria-labelledby="features-heading">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <div class="reveal inline-flex items-center gap-2 glass rounded-full px-4 py-2 mb-4">
                        <i class="fas fa-star text-cyan-400 text-xs" aria-hidden="true"></i>
                        <span class="text-xs font-semibold text-cyan-300 uppercase tracking-widest">Everything You Need</span>
                    </div>
                    <h2 id="features-heading" class="reveal text-4xl lg:text-5xl font-black text-white mb-4 leading-tight">
                        Built for <span class="gradient-text">speed &amp; scale</span>
                    </h2>
                    <p class="reveal text-lg text-slate-300 max-w-2xl mx-auto">
                        Every feature is designed to help your team work faster, sell smarter, and grow without limits.
                    </p>
                </div>

                <ul class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 list-none p-0 m-0">
                    {{-- Feature 1 --}}
                    <li class="reveal glass rounded-3xl p-7 card-hover">
                        <div class="feature-icon bg-cyan-500/15 text-cyan-400 mb-5" aria-hidden="true">
                            <i class="fas fa-cash-register"></i>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Point of Sale</h3>
                        <p class="text-slate-300 text-sm leading-relaxed">Lightning-fast checkout experience with barcode
                            scanning, split payments, discounts, and receipt printing — all on any device.</p>
                        <div class="mt-4 flex gap-2 flex-wrap" aria-label="Features: Barcode, Split Pay, Offline Mode">
                            <span class="module-tag bg-cyan-500/10 text-cyan-300">Barcode</span>
                            <span class="module-tag bg-cyan-500/10 text-cyan-300">Split Pay</span>
                            <span class="module-tag bg-cyan-500/10 text-cyan-300">Offline Mode</span>
                        </div>
                    </li>

                    {{-- Feature 2 --}}
                    <li class="reveal glass rounded-3xl p-7 card-hover">
                        <div class="feature-icon bg-emerald-500/15 text-emerald-400 mb-5" aria-hidden="true">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Inventory Control</h3>
                        <p class="text-slate-300 text-sm leading-relaxed">Real-time stock tracking with automatic
                            deductions on every sale. Transfer stock between outlets, set reorder points, and never run out.</p>
                        <div class="mt-4 flex gap-2 flex-wrap" aria-label="Features: Real-time, Transfer, Alerts">
                            <span class="module-tag bg-emerald-500/10 text-emerald-300">Real-time</span>
                            <span class="module-tag bg-emerald-500/10 text-emerald-300">Transfer</span>
                            <span class="module-tag bg-emerald-500/10 text-emerald-300">Alerts</span>
                        </div>
                    </li>

                    {{-- Feature 3 --}}
                    <li class="reveal glass rounded-3xl p-7 card-hover">
                        <div class="feature-icon bg-teal-500/15 text-teal-400 mb-5" aria-hidden="true">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Finance &amp; Reporting</h3>
                        <p class="text-slate-300 text-sm leading-relaxed">Comprehensive financial reports, revenue
                            analytics, payment breakdowns, and export to Excel/PDF. Know your numbers at a glance.</p>
                        <div class="mt-4 flex gap-2 flex-wrap" aria-label="Features: P&L, Export, Charts">
                            <span class="module-tag bg-teal-500/10 text-teal-300">P&amp;L</span>
                            <span class="module-tag bg-teal-500/10 text-teal-300">Export</span>
                            <span class="module-tag bg-teal-500/10 text-teal-300">Charts</span>
                        </div>
                    </li>

                    {{-- Feature 4 --}}
                    <li class="reveal glass rounded-3xl p-7 card-hover">
                        <div class="feature-icon bg-amber-500/15 text-amber-400 mb-5" aria-hidden="true">
                            <i class="fas fa-store-alt"></i>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Multi-Outlet</h3>
                        <p class="text-slate-300 text-sm leading-relaxed">Manage unlimited store locations from a single
                            dashboard. Each outlet has its own staff, inventory, and performance metrics.</p>
                        <div class="mt-4 flex gap-2 flex-wrap" aria-label="Features: Centralized, Per-Outlet, Unlimited">
                            <span class="module-tag bg-amber-500/10 text-amber-300">Centralized</span>
                            <span class="module-tag bg-amber-500/10 text-amber-300">Per-Outlet</span>
                            <span class="module-tag bg-amber-500/10 text-amber-300">Unlimited</span>
                        </div>
                    </li>

                    {{-- Feature 5 --}}
                    <li class="reveal glass rounded-3xl p-7 card-hover">
                        <div class="feature-icon bg-pink-500/15 text-pink-400 mb-5" aria-hidden="true">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Role &amp; Permissions</h3>
                        <p class="text-slate-300 text-sm leading-relaxed">Granular access control with custom roles. Assign
                            permissions per feature, per module, even per outlet — keeping data secure.</p>
                        <div class="mt-4 flex gap-2 flex-wrap" aria-label="Features: RBAC, Custom Roles, Audit Log">
                            <span class="module-tag bg-pink-500/10 text-pink-300">RBAC</span>
                            <span class="module-tag bg-pink-500/10 text-pink-300">Custom Roles</span>
                            <span class="module-tag bg-pink-500/10 text-pink-300">Audit Log</span>
                        </div>
                    </li>

                    {{-- Feature 6 --}}
                    <li class="reveal glass rounded-3xl p-7 card-hover">
                        <div class="feature-icon bg-cyan-500/15 text-cyan-400 mb-5" aria-hidden="true">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Multi-Tenant SaaS</h3>
                        <p class="text-slate-300 text-sm leading-relaxed">Fully isolated tenant environments. Perfect for
                            franchise owners, holding companies, or white-label resellers. One platform, many businesses.</p>
                        <div class="mt-4 flex gap-2 flex-wrap" aria-label="Features: Isolated, Scalable, White-label">
                            <span class="module-tag bg-cyan-500/10 text-cyan-300">Isolated</span>
                            <span class="module-tag bg-cyan-500/10 text-cyan-300">Scalable</span>
                            <span class="module-tag bg-cyan-500/10 text-cyan-300">White-label</span>
                        </div>
                    </li>
                </ul>
            </div>
        </section>


        {{-- ============================
             MODULES DEEP DIVE
        ============================= --}}
        <section id="modules" class="py-24 bg-[#060b18]" aria-labelledby="modules-heading">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 id="modules-heading" class="reveal text-4xl lg:text-5xl font-black text-white mb-4">
                        One platform. <span class="gradient-text">Every module.</span>
                    </h2>
                    <p class="reveal text-slate-300 text-lg max-w-xl mx-auto">All modules are deeply integrated — data
                        flows automatically so you never have to re-enter anything.</p>
                </div>

                <div class="grid lg:grid-cols-2 gap-8 items-center mb-16">
                    {{-- Left modules list --}}
                    <ul class="space-y-4 list-none p-0 m-0" aria-label="Available modules">
                        <li class="reveal-left glass-dark rounded-2xl p-5 flex items-start gap-4 card-hover border border-cyan-500/30">
                            <div class="w-12 h-12 rounded-2xl bg-cyan-500/20 flex items-center justify-center flex-shrink-0" aria-hidden="true">
                                <i class="fas fa-cash-register text-cyan-400 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-white mb-1">POS Module</h3>
                                <p class="text-sm text-slate-300">Cashier interface, order management, payment processing,
                                    customer loyalty, and receipt printing.</p>
                            </div>
                            <i class="fas fa-chevron-right text-cyan-400 ml-auto mt-1 flex-shrink-0" aria-hidden="true"></i>
                        </li>

                        <li class="reveal-left glass rounded-2xl p-5 flex items-start gap-4 card-hover">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 flex items-center justify-center flex-shrink-0" aria-hidden="true">
                                <i class="fas fa-warehouse text-emerald-400 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-white mb-1">Inventory Module</h3>
                                <p class="text-sm text-slate-300">Stock tracking, movement history, adjustments,
                                    inter-outlet transfers, and low-stock alerts.</p>
                            </div>
                            <i class="fas fa-chevron-right text-slate-400 ml-auto mt-1 flex-shrink-0" aria-hidden="true"></i>
                        </li>

                        <li class="reveal-left glass rounded-2xl p-5 flex items-start gap-4 card-hover">
                            <div class="w-12 h-12 rounded-2xl bg-teal-500/20 flex items-center justify-center flex-shrink-0" aria-hidden="true">
                                <i class="fas fa-file-invoice-dollar text-teal-400 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-white mb-1">Finance Module</h3>
                                <p class="text-sm text-slate-300">Revenue tracking, expense management, profit &amp; loss
                                    statements, and financial dashboards.</p>
                            </div>
                            <i class="fas fa-chevron-right text-slate-400 ml-auto mt-1 flex-shrink-0" aria-hidden="true"></i>
                        </li>

                        <li class="reveal-left glass rounded-2xl p-5 flex items-start gap-4 card-hover">
                            <div class="w-12 h-12 rounded-2xl bg-amber-500/20 flex items-center justify-center flex-shrink-0" aria-hidden="true">
                                <i class="fas fa-chart-bar text-amber-400 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-white mb-1">Reporting Module</h3>
                                <p class="text-sm text-slate-300">Detailed sales, inventory, and staff reports. Filter by
                                    date, outlet, category — export in any format.</p>
                            </div>
                            <i class="fas fa-chevron-right text-slate-400 ml-auto mt-1 flex-shrink-0" aria-hidden="true"></i>
                        </li>
                    </ul>

                    {{-- Right metrics card --}}
                    <div class="reveal-right glass-dark rounded-3xl p-8 space-y-6">
                        <h3 class="font-bold text-white text-xl">Live Performance Overview</h3>

                        <div class="space-y-4">
                            {{-- WCAG 4.1.2 — role="progressbar" with proper ARIA attributes --}}
                            <div>
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="text-slate-300" id="pb-label-1">Sales Processing Speed</span>
                                    <span class="text-white font-semibold" aria-hidden="true">98%</span>
                                </div>
                                <div class="h-2 rounded-full bg-white/5 overflow-hidden">
                                    <div class="progress-bar h-full rounded-full bg-gradient-to-r from-cyan-500 to-teal-500"
                                         role="progressbar"
                                         aria-valuenow="98"
                                         aria-valuemin="0"
                                         aria-valuemax="100"
                                         aria-labelledby="pb-label-1"
                                         style="--fill:98%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="text-slate-300" id="pb-label-2">Inventory Accuracy</span>
                                    <span class="text-white font-semibold" aria-hidden="true">99.7%</span>
                                </div>
                                <div class="h-2 rounded-full bg-white/5 overflow-hidden">
                                    <div class="progress-bar h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500"
                                         role="progressbar"
                                         aria-valuenow="99.7"
                                         aria-valuemin="0"
                                         aria-valuemax="100"
                                         aria-labelledby="pb-label-2"
                                         style="--fill:99.7%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="text-slate-300" id="pb-label-3">Report Generation</span>
                                    <span class="text-white font-semibold" aria-hidden="true">95%</span>
                                </div>
                                <div class="h-2 rounded-full bg-white/5 overflow-hidden">
                                    <div class="progress-bar h-full rounded-full bg-gradient-to-r from-teal-500 to-emerald-500"
                                         role="progressbar"
                                         aria-valuenow="95"
                                         aria-valuemin="0"
                                         aria-valuemax="100"
                                         aria-labelledby="pb-label-3"
                                         style="--fill:95%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="text-slate-300" id="pb-label-4">System Uptime</span>
                                    <span class="text-white font-semibold" aria-hidden="true">99.9%</span>
                                </div>
                                <div class="h-2 rounded-full bg-white/5 overflow-hidden">
                                    <div class="progress-bar h-full rounded-full bg-gradient-to-r from-amber-500 to-orange-500"
                                         role="progressbar"
                                         aria-valuenow="99.9"
                                         aria-valuemin="0"
                                         aria-valuemax="100"
                                         aria-labelledby="pb-label-4"
                                         style="--fill:99.9%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="section-divider"></div>

                        <dl class="grid grid-cols-2 gap-4">
                            <div class="glass rounded-2xl p-4 text-center">
                                <dd class="text-2xl font-black text-white">&lt;&nbsp;0.3s</dd>
                                <dt class="text-xs text-slate-300 mt-1">Avg. Response Time</dt>
                            </div>
                            <div class="glass rounded-2xl p-4 text-center">
                                <dd class="text-2xl font-black text-white">24/7</dd>
                                <dt class="text-xs text-slate-300 mt-1">Cloud Availability</dt>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </section>


        {{-- ============================
             HOW IT WORKS
        ============================= --}}
        <section class="py-24 bg-[#070c1b]" aria-labelledby="how-it-works-heading">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 id="how-it-works-heading" class="reveal text-4xl lg:text-5xl font-black text-white mb-4">
                        Get running in <span class="gradient-text">3 steps</span>
                    </h2>
                    <p class="reveal text-slate-300 text-lg">No lengthy onboarding. No IT team required. Start selling in minutes.</p>
                </div>

                <ol class="grid md:grid-cols-3 gap-8 relative list-none p-0 m-0"
                    aria-label="Getting started steps">
                    {{-- Step connector line — decorative --}}
                    <div class="hidden md:block absolute top-14 left-1/3 right-1/3 h-0.5 bg-gradient-to-r from-cyan-500 via-teal-500 to-emerald-500 opacity-40"
                         aria-hidden="true"></div>

                    <li class="reveal text-center">
                        <div class="w-28 h-28 rounded-3xl bg-gradient-to-br from-cyan-600 to-cyan-800 flex items-center justify-center mx-auto mb-6 shadow-2xl shadow-cyan-900/40 relative"
                             aria-hidden="true">
                            <i class="fas fa-building text-white text-3xl"></i>
                            <div class="absolute -top-3 -right-3 w-8 h-8 rounded-full bg-cyan-500 flex items-center justify-center text-white text-xs font-black">
                                1
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">
                            <span class="sr-only">Step 1: </span>Create Your Account
                        </h3>
                        <p class="text-slate-300 text-sm leading-relaxed">Register your business, add your outlets, and
                            configure your product catalog. Takes under 5 minutes.</p>
                    </li>

                    <li class="reveal text-center">
                        <div class="w-28 h-28 rounded-3xl bg-gradient-to-br from-teal-600 to-teal-800 flex items-center justify-center mx-auto mb-6 shadow-2xl shadow-teal-900/40 relative"
                             aria-hidden="true">
                            <i class="fas fa-sliders-h text-white text-3xl"></i>
                            <div class="absolute -top-3 -right-3 w-8 h-8 rounded-full bg-teal-500 flex items-center justify-center text-white text-xs font-black">
                                2
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">
                            <span class="sr-only">Step 2: </span>Configure &amp; Invite Team
                        </h3>
                        <p class="text-slate-300 text-sm leading-relaxed">Set roles, assign staff to outlets, and configure
                            payment methods. Customize to fit your workflow.</p>
                    </li>

                    <li class="reveal text-center">
                        <div class="w-28 h-28 rounded-3xl bg-gradient-to-br from-emerald-600 to-emerald-800 flex items-center justify-center mx-auto mb-6 shadow-2xl shadow-emerald-900/40 relative"
                             aria-hidden="true">
                            <i class="fas fa-rocket text-white text-3xl"></i>
                            <div class="absolute -top-3 -right-3 w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs font-black">
                                3
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">
                            <span class="sr-only">Step 3: </span>Start Selling
                        </h3>
                        <p class="text-slate-300 text-sm leading-relaxed">Open the cashier, process orders, and watch your
                            dashboard fill with real-time insights.</p>
                    </li>
                </ol>
            </div>
        </section>


        {{-- ============================
             DOWNLOAD APP
        ============================= --}}
        <section id="download" class="py-24 bg-[#060b18]" aria-labelledby="download-heading">
            <div class="max-w-7xl mx-auto px-6">
                <div class="glass-dark rounded-3xl overflow-hidden relative">
                    <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-br from-cyan-600/15 to-teal-600/10 rounded-full blur-3xl" aria-hidden="true"></div>
                    <div class="absolute bottom-0 left-0 w-64 h-64 bg-gradient-to-tr from-emerald-600/10 to-teal-600/8 rounded-full blur-3xl" aria-hidden="true"></div>

                    <div class="relative z-10 grid lg:grid-cols-2 gap-12 p-10 lg:p-16 items-center">
                        {{-- Text side --}}
                        <div>
                            <div class="inline-flex items-center gap-2 glass rounded-full px-4 py-2 mb-6">
                                <i class="fas fa-mobile-alt text-cyan-400 text-xs" aria-hidden="true"></i>
                                <span class="text-xs font-semibold text-cyan-300 uppercase tracking-widest">Mobile App</span>
                            </div>
                            <h2 id="download-heading" class="text-4xl lg:text-5xl font-black text-white leading-tight mb-4">
                                Take your business<br><span class="gradient-text">everywhere you go</span>
                            </h2>
                            <p class="text-slate-300 text-lg leading-relaxed mb-8">
                                The {{ config('app.name') }} mobile app lets you monitor sales, check inventory, approve
                                transactions, and manage your team — all from your phone.
                            </p>

                            <div class="flex flex-col sm:flex-row gap-4">
                                {{-- App Store --}}
                                <a href="#" class="store-btn" aria-label="Download on the App Store">
                                    <svg class="w-8 h-8 flex-shrink-0" viewBox="0 0 24 24" fill="white"
                                        xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                                        <path
                                            d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z" />
                                    </svg>
                                    <div>
                                        <p class="text-xs text-slate-300">Download on the</p>
                                        <p class="font-semibold text-white">App Store</p>
                                    </div>
                                </a>

                                {{-- Google Play --}}
                                <a href="#" class="store-btn" aria-label="Get it on Google Play">
                                    <svg class="w-8 h-8 flex-shrink-0" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                                        <path
                                            d="M3 20.5v-17c0-.83.94-1.3 1.6-.8l14 8.5c.6.37.6 1.23 0 1.6l-14 8.5c-.66.5-1.6.03-1.6-.8z"
                                            fill="#10b981" />
                                        <path d="M3 20.5L13.5 10 3 3.5v17z" fill="#34d399" opacity=".7" />
                                        <path d="M3 3.5l10.5 6.5L18 7.5 5.6 3.7C4.5 3.3 3.4 3.8 3 4.2v-.7z" fill="#6ee7b7" opacity=".5" />
                                    </svg>
                                    <div>
                                        <p class="text-xs text-slate-300">Get it on</p>
                                        <p class="font-semibold text-white">Google Play</p>
                                    </div>
                                </a>

                                {{-- Huawei AppGallery --}}
                                <a href="#" class="store-btn" aria-label="Explore on Huawei AppGallery">
                                    <svg class="w-8 h-8 flex-shrink-0" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                                        <rect width="24" height="24" rx="6" fill="#CC0000" opacity=".15" />
                                        <path
                                            d="M12 4C7.6 4 4 7.6 4 12s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 14c-3.3 0-6-2.7-6-6s2.7-6 6-6 6 2.7 6 6-2.7 6-6 6zm-1-9v4l3.5 2-0.7 1.2L10 14.5V9h1z"
                                            fill="#ef4444" />
                                    </svg>
                                    <div>
                                        <p class="text-xs text-slate-300">Explore on</p>
                                        <p class="font-semibold text-white">AppGallery</p>
                                    </div>
                                </a>
                            </div>

                            <p class="text-xs text-slate-400 mt-6 flex items-center gap-2">
                                <i class="fas fa-info-circle text-slate-400" aria-hidden="true"></i>
                                Available soon — links will go live on launch day
                            </p>
                        </div>

                        {{-- Phone mockup — decorative --}}
                        <div class="flex justify-center" aria-hidden="true">
                            <div class="relative">
                                <div class="w-64 h-auto glass-dark rounded-[3rem] p-3 shadow-2xl shadow-cyan-900/40 border border-white/10">
                                    <div class="bg-[#040810] rounded-[2.5rem] overflow-hidden">
                                        <div class="flex justify-center pt-3 pb-2">
                                            <div class="w-24 h-5 rounded-full bg-black"></div>
                                        </div>
                                        <div class="px-4 pb-6 space-y-3">
                                            <div class="text-center py-2">
                                                <p class="text-xs text-slate-400">Good morning,</p>
                                                <p class="text-sm font-bold text-white">Admin Dashboard</p>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div class="glass rounded-xl p-3 text-center">
                                                    <p style="font-size:10px" class="text-slate-400">Sales</p>
                                                    <p class="text-white font-bold text-sm">4.2M</p>
                                                </div>
                                                <div class="glass rounded-xl p-3 text-center">
                                                    <p style="font-size:10px" class="text-slate-400">Orders</p>
                                                    <p class="text-white font-bold text-sm">138</p>
                                                </div>
                                            </div>
                                            <div class="glass rounded-xl p-3">
                                                <p style="font-size:10px" class="text-slate-400 mb-2">Today's Revenue</p>
                                                <div class="flex items-end gap-1 h-12">
                                                    <div class="flex-1 rounded-t bg-cyan-500/40" style="height:40%"></div>
                                                    <div class="flex-1 rounded-t bg-cyan-500/50" style="height:65%"></div>
                                                    <div class="flex-1 rounded-t bg-cyan-500/60" style="height:50%"></div>
                                                    <div class="flex-1 rounded-t bg-cyan-400/70" style="height:80%"></div>
                                                    <div class="flex-1 rounded-t bg-cyan-400"    style="height:60%"></div>
                                                    <div class="flex-1 rounded-t bg-emerald-400"   style="height:100%"></div>
                                                </div>
                                            </div>
                                            <div class="glass rounded-2xl p-3 flex justify-around">
                                                <i class="fas fa-home text-cyan-400"    style="font-size:14px"></i>
                                                <i class="fas fa-cash-register text-slate-500" style="font-size:14px"></i>
                                                <i class="fas fa-boxes text-slate-500"    style="font-size:14px"></i>
                                                <i class="fas fa-chart-bar text-slate-500" style="font-size:14px"></i>
                                                <i class="fas fa-user text-slate-500"     style="font-size:14px"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="absolute -right-16 top-20 glass-dark rounded-2xl p-3 shadow-xl float-1">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                                            <i class="fas fa-bell text-emerald-400 text-sm"></i>
                                        </div>
                                        <div>
                                            <p style="font-size:10px" class="text-slate-300">New Order!</p>
                                            <p class="text-xs font-semibold text-white">Rp 185,000</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="absolute -left-16 bottom-24 glass-dark rounded-2xl p-3 shadow-xl float-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-amber-500/20 flex items-center justify-center">
                                            <i class="fas fa-exclamation-triangle text-amber-400 text-sm"></i>
                                        </div>
                                        <div>
                                            <p style="font-size:10px" class="text-slate-300">Low Stock</p>
                                            <p class="text-xs font-semibold text-white">Indomie Goreng</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        {{-- ============================
             TESTIMONIALS
        ============================= --}}
        <section class="py-24 bg-[#070c1b]" aria-labelledby="testimonials-heading">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 id="testimonials-heading" class="reveal text-4xl lg:text-5xl font-black text-white mb-4">
                        Loved by <span class="gradient-text">business owners</span>
                    </h2>
                    <p class="reveal text-slate-300 text-lg">Don't take our word for it — hear from the people who use it daily.</p>
                </div>

                <ul class="grid md:grid-cols-3 gap-6 list-none p-0 m-0">
                    <li class="reveal glass rounded-3xl p-7 card-hover">
                        {{-- WCAG 1.4.3 — stars rendered as text with aria-label --}}
                        <div class="testimonial-stars mb-4" role="img" aria-label="5 out of 5 stars">★★★★★</div>
                        <blockquote>
                            <p class="text-slate-300 text-sm leading-relaxed mb-6">"This system completely changed how we run
                                our 4 outlets. The inventory syncing alone saves my team 3 hours every day. The dashboard is
                                absolutely beautiful."</p>
                            <footer class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-cyan-500 to-teal-600 flex items-center justify-center text-white font-bold text-sm"
                                     aria-hidden="true">AB</div>
                                <div>
                                    <cite class="text-sm font-semibold text-white not-italic">Ahmad Bachtiar</cite>
                                    <p class="text-xs text-slate-400">Owner, Kopi Nusantara</p>
                                </div>
                            </footer>
                        </blockquote>
                    </li>

                    <li class="reveal glass rounded-3xl p-7 card-hover">
                        <div class="testimonial-stars mb-4" role="img" aria-label="5 out of 5 stars">★★★★★</div>
                        <blockquote>
                            <p class="text-slate-300 text-sm leading-relaxed mb-6">"The POS module is so fast — my cashiers
                                love it. No freezing, no crashes. And the financial reports give me exactly what I need to make
                                decisions quickly."</p>
                            <footer class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-sm"
                                     aria-hidden="true">SR</div>
                                <div>
                                    <cite class="text-sm font-semibold text-white not-italic">Siti Rahayu</cite>
                                    <p class="text-xs text-slate-400">Manager, Toko Makmur</p>
                                </div>
                            </footer>
                        </blockquote>
                    </li>

                    <li class="reveal glass rounded-3xl p-7 card-hover">
                        <div class="testimonial-stars mb-4" role="img" aria-label="5 out of 5 stars">★★★★★</div>
                        <blockquote>
                            <p class="text-slate-300 text-sm leading-relaxed mb-6">"We switched from a manual spreadsheet
                                system. The difference is night and day. Setup took 30 minutes and we've never looked back. Best
                                investment we've made."</p>
                            <footer class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white font-bold text-sm"
                                     aria-hidden="true">DW</div>
                                <div>
                                    <cite class="text-sm font-semibold text-white not-italic">Dodi Wibowo</cite>
                                    <p class="text-xs text-slate-400">Director, Resto Bahari</p>
                                </div>
                            </footer>
                        </blockquote>
                    </li>
                </ul>
            </div>
        </section>


        {{-- ============================
             FREE TRIAL SECTION
        ============================= --}}
        <section class="py-20 bg-[#060b18]" aria-labelledby="trial-heading">
            <div class="max-w-7xl mx-auto px-6">
                <div class="reveal glass-dark rounded-3xl p-10 lg:p-14 overflow-hidden relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-600/8 via-cyan-600/6 to-teal-600/5 rounded-3xl" aria-hidden="true"></div>
                    <div class="absolute -top-16 -right-16 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl" aria-hidden="true"></div>
                    <div class="absolute -bottom-16 -left-16 w-64 h-64 bg-cyan-500/10 rounded-full blur-3xl" aria-hidden="true"></div>

                    <div class="relative z-10 grid lg:grid-cols-2 gap-12 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/20 rounded-full px-4 py-2 mb-6">
                                <i class="fas fa-gift text-emerald-400 text-sm" aria-hidden="true"></i>
                                <span class="text-xs font-semibold text-emerald-300 uppercase tracking-wider">Free Trial</span>
                            </div>

                            <h2 id="trial-heading" class="text-4xl lg:text-5xl font-black text-white mb-4 leading-tight">
                                Try everything<br><span class="gradient-text">free for 14 days</span>
                            </h2>
                            <p class="text-slate-300 text-lg mb-8 leading-relaxed">
                                Get full access to all Professional features the moment you sign up. No credit card. No
                                commitment. Cancel anytime — your data stays safe.
                            </p>

                            @guest
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}"
                                        class="btn-primary inline-flex items-center gap-2 text-white font-bold px-8 py-4 rounded-2xl text-base">
                                        <i class="fas fa-rocket" aria-hidden="true"></i>
                                        Start Free Trial
                                    </a>
                                @endif
                            @endguest

                            <p class="text-xs text-slate-400 mt-4 flex items-center gap-2">
                                <i class="fas fa-lock text-slate-400" aria-hidden="true"></i>
                                No credit card required. Cancel before the trial ends and you won't be charged.
                            </p>
                        </div>

                        <ul class="grid grid-cols-2 gap-4 list-none p-0 m-0">
                            <li class="glass rounded-2xl p-5">
                                <div class="w-10 h-10 rounded-xl bg-cyan-500/20 flex items-center justify-center mb-3" aria-hidden="true">
                                    <i class="fas fa-cash-register text-cyan-400"></i>
                                </div>
                                <p class="text-sm font-semibold text-white mb-1">Full POS Module</p>
                                <p class="text-xs text-slate-400">Cashier, receipts, returns — all included</p>
                            </li>
                            <li class="glass rounded-2xl p-5">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 flex items-center justify-center mb-3" aria-hidden="true">
                                    <i class="fas fa-boxes text-emerald-400"></i>
                                </div>
                                <p class="text-sm font-semibold text-white mb-1">Inventory Control</p>
                                <p class="text-xs text-slate-400">Stock tracking across all outlets</p>
                            </li>
                            <li class="glass rounded-2xl p-5">
                                <div class="w-10 h-10 rounded-xl bg-teal-500/20 flex items-center justify-center mb-3" aria-hidden="true">
                                    <i class="fas fa-chart-bar text-teal-400"></i>
                                </div>
                                <p class="text-sm font-semibold text-white mb-1">Advanced Reports</p>
                                <p class="text-xs text-slate-400">Revenue, profit &amp; analytics</p>
                            </li>
                            <li class="glass rounded-2xl p-5">
                                <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center mb-3" aria-hidden="true">
                                    <i class="fas fa-users text-amber-400"></i>
                                </div>
                                <p class="text-sm font-semibold text-white mb-1">Team Management</p>
                                <p class="text-xs text-slate-400">Unlimited users &amp; roles</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>


        {{-- ============================
             PRICING
        ============================= --}}
        <section id="pricing" class="py-24 bg-[#060b18]" aria-labelledby="pricing-heading">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 id="pricing-heading" class="reveal text-4xl lg:text-5xl font-black text-white mb-4">
                        Simple, <span class="gradient-text">honest pricing</span>
                    </h2>
                    <p class="reveal text-slate-300 text-lg">No hidden fees. Cancel anytime. Scale as you grow.</p>
                    <p class="reveal text-sm text-emerald-400 mt-2 flex items-center justify-center gap-2">
                        <i class="fas fa-gift" aria-hidden="true"></i>
                        Every paid plan starts with a 14-day free trial
                    </p>
                </div>

                <ul class="grid md:grid-cols-3 gap-6 items-start list-none p-0 m-0"
                    aria-label="Pricing plans">
                    {{-- Starter --}}
                    <li class="reveal glass rounded-3xl p-8 card-hover">
                        <div class="mb-6">
                            <p class="text-sm font-semibold text-slate-300 uppercase tracking-widest mb-1">Starter</p>
                            <div class="flex items-end gap-1 mb-1">
                                <span class="text-4xl font-black text-white">Free</span>
                            </div>
                            <p class="text-xs text-slate-400">Perfect for a single store</p>
                        </div>
                        <div class="section-divider mb-6"></div>
                        <ul class="space-y-3 mb-8 list-none p-0" aria-label="Starter plan features">
                            <li class="flex items-center gap-3 text-sm text-slate-200">
                                <i class="fas fa-check text-emerald-400 w-4" aria-hidden="true"></i>
                                <span>1 Outlet</span>
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-200">
                                <i class="fas fa-check text-emerald-400 w-4" aria-hidden="true"></i>
                                <span>Up to 3 Users</span>
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-200">
                                <i class="fas fa-check text-emerald-400 w-4" aria-hidden="true"></i>
                                <span>POS Module</span>
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-200">
                                <i class="fas fa-check text-emerald-400 w-4" aria-hidden="true"></i>
                                <span>Basic Inventory</span>
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-400">
                                <i class="fas fa-times text-slate-500 w-4" aria-hidden="true"></i>
                                <span aria-label="Not included: Finance Module">Finance Module</span>
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-400">
                                <i class="fas fa-times text-slate-500 w-4" aria-hidden="true"></i>
                                <span aria-label="Not included: Advanced Reports">Advanced Reports</span>
                            </li>
                        </ul>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="block text-center py-3 rounded-2xl btn-secondary text-white text-sm font-semibold">
                                Get Started Free
                            </a>
                        @endif
                    </li>

                    {{-- Professional (popular) --}}
                    <li class="reveal glass pricing-popular rounded-3xl p-8 card-hover relative">
                        <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                            <span class="bg-gradient-to-r from-cyan-500 to-teal-600 text-white text-xs font-bold px-5 py-2 rounded-full shadow-lg shadow-cyan-500/30"
                                  aria-label="Most popular plan">MOST POPULAR</span>
                        </div>
                        <div class="mb-6 mt-2">
                            <p class="text-sm font-semibold text-cyan-300 uppercase tracking-widest mb-1">Professional</p>
                            <div class="flex items-end gap-1 mb-1">
                                <span class="text-4xl font-black text-white">Rp 299K</span>
                                <span class="text-slate-300 text-sm mb-1">/mo</span>
                            </div>
                            <p class="text-xs text-slate-400">For growing businesses</p>
                            <div class="flex items-center gap-1.5 mt-2">
                                <i class="fas fa-gift text-emerald-400 text-xs" aria-hidden="true"></i>
                                <span class="text-xs text-emerald-400 font-medium">14-day free trial included</span>
                            </div>
                        </div>
                        <div class="section-divider mb-6"></div>
                        <ul class="space-y-3 mb-8 list-none p-0" aria-label="Professional plan features">
                            <li class="flex items-center gap-3 text-sm text-slate-200">
                                <i class="fas fa-check text-emerald-400 w-4" aria-hidden="true"></i>
                                <span>Up to 5 Outlets</span>
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-200">
                                <i class="fas fa-check text-emerald-400 w-4" aria-hidden="true"></i>
                                <span>Unlimited Users</span>
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-200">
                                <i class="fas fa-check text-emerald-400 w-4" aria-hidden="true"></i>
                                <span>Full POS Module</span>
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-200">
                                <i class="fas fa-check text-emerald-400 w-4" aria-hidden="true"></i>
                                <span>Full Inventory</span>
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-200">
                                <i class="fas fa-check text-emerald-400 w-4" aria-hidden="true"></i>
                                <span>Finance Module</span>
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-200">
                                <i class="fas fa-check text-emerald-400 w-4" aria-hidden="true"></i>
                                <span>Advanced Reports</span>
                            </li>
                        </ul>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="block text-center py-3 rounded-2xl btn-primary text-white text-sm font-semibold">
                                Start 14-Day Trial
                            </a>
                        @endif
                    </li>

                    {{-- Enterprise --}}
                    <li class="reveal glass rounded-3xl p-8 card-hover">
                        <div class="mb-6">
                            <p class="text-sm font-semibold text-slate-300 uppercase tracking-widest mb-1">Enterprise</p>
                            <div class="flex items-end gap-1 mb-1">
                                <span class="text-4xl font-black text-white">Custom</span>
                            </div>
                            <p class="text-xs text-slate-400">For enterprise &amp; franchises</p>
                        </div>
                        <div class="section-divider mb-6"></div>
                        <ul class="space-y-3 mb-8 list-none p-0" aria-label="Enterprise plan features">
                            <li class="flex items-center gap-3 text-sm text-slate-200">
                                <i class="fas fa-check text-emerald-400 w-4" aria-hidden="true"></i>
                                <span>Unlimited Outlets</span>
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-200">
                                <i class="fas fa-check text-emerald-400 w-4" aria-hidden="true"></i>
                                <span>Unlimited Users</span>
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-200">
                                <i class="fas fa-check text-emerald-400 w-4" aria-hidden="true"></i>
                                <span>All Modules</span>
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-200">
                                <i class="fas fa-check text-emerald-400 w-4" aria-hidden="true"></i>
                                <span>Multi-Tenant Setup</span>
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-200">
                                <i class="fas fa-check text-emerald-400 w-4" aria-hidden="true"></i>
                                <span>Dedicated Support</span>
                            </li>
                            <li class="flex items-center gap-3 text-sm text-slate-200">
                                <i class="fas fa-check text-emerald-400 w-4" aria-hidden="true"></i>
                                <span>Custom Integration</span>
                            </li>
                        </ul>
                        <a href="mailto:hello@posnerp.com"
                            class="block text-center py-3 rounded-2xl btn-secondary text-white text-sm font-semibold">
                            Contact Sales
                        </a>
                    </li>
                </ul>
            </div>
        </section>


        {{-- ============================
             FINAL CTA
        ============================= --}}
        <section class="py-24 bg-[#070c1b]" aria-labelledby="cta-heading">
            <div class="max-w-4xl mx-auto px-6 text-center">
                <div class="relative glass-dark rounded-3xl p-14 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-cyan-600/10 via-teal-600/8 to-emerald-600/6 rounded-3xl" aria-hidden="true"></div>
                    <div class="absolute -top-20 -right-20 w-64 h-64 bg-cyan-500/15 rounded-full blur-3xl" aria-hidden="true"></div>
                    <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl" aria-hidden="true"></div>

                    <div class="relative z-10">
                        <h2 id="cta-heading" class="text-4xl lg:text-6xl font-black text-white leading-tight mb-6">
                            Ready to transform<br><span class="gradient-text">your business?</span>
                        </h2>
                        <p class="text-slate-300 text-lg mb-10 max-w-xl mx-auto">
                            Join thousands of merchants already using {{ config('app.name') }}. Free to start, no credit card required.
                        </p>
                        <div class="flex flex-wrap gap-4 justify-center">
                            @auth
                                <a href="{{ url('/admin/dashboard') }}"
                                    class="btn-primary text-white font-bold px-10 py-4 rounded-2xl text-lg flex items-center gap-2">
                                    <i class="fas fa-tachometer-alt" aria-hidden="true"></i>
                                    Open Dashboard
                                </a>
                            @else
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}"
                                        class="btn-primary text-white font-bold px-10 py-4 rounded-2xl text-lg flex items-center gap-2">
                                        <i class="fas fa-rocket" aria-hidden="true"></i>
                                        Start For Free
                                    </a>
                                @endif
                                <a href="{{ route('login') }}"
                                    class="btn-secondary text-slate-200 font-medium px-10 py-4 rounded-2xl text-lg flex items-center gap-2">
                                    <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
                                    Login
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>{{-- /main --}}


    {{-- ============================
         FOOTER
    ============================= --}}
    <footer class="bg-[#040810] border-t border-white/5 py-16" aria-label="Site footer">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-10 mb-12">
                {{-- Brand --}}
                <div class="col-span-2 md:col-span-1">
                    <a href="/" class="flex items-center gap-3 mb-4"
                       aria-label="{{ config('app.name', 'POS ERP') }} — Home">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-500 to-teal-600 flex items-center justify-center shadow-lg shadow-cyan-500/30"
                             aria-hidden="true">
                            <i class="fas fa-cash-register text-white text-sm" aria-hidden="true"></i>
                        </div>
                        <span class="font-bold text-lg text-white">{{ config('app.name', 'POS ERP') }}</span>
                    </a>
                    <p class="text-sm text-slate-400 leading-relaxed mb-5">The complete ERP &amp; POS solution for modern
                        businesses, from single stores to nationwide franchises.</p>
                    {{-- WCAG 2.4.4 — icon-only links need aria-label --}}
                    <div class="flex gap-3">
                        <a href="#"
                           class="social-link glass text-slate-300 hover:text-white hover:bg-cyan-500/20 transition-all"
                           aria-label="{{ config('app.name', 'POS ERP') }} on Twitter">
                            <i class="fab fa-twitter text-sm" aria-hidden="true"></i>
                        </a>
                        <a href="#"
                           class="social-link glass text-slate-300 hover:text-white hover:bg-cyan-500/20 transition-all"
                           aria-label="{{ config('app.name', 'POS ERP') }} on Instagram">
                            <i class="fab fa-instagram text-sm" aria-hidden="true"></i>
                        </a>
                        <a href="#"
                           class="social-link glass text-slate-300 hover:text-white hover:bg-cyan-500/20 transition-all"
                           aria-label="{{ config('app.name', 'POS ERP') }} on LinkedIn">
                            <i class="fab fa-linkedin text-sm" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>

                {{-- Product --}}
                <nav aria-label="Product links">
                    <p class="text-sm font-semibold text-white mb-4 uppercase tracking-widest">Product</p>
                    <ul class="space-y-2.5 list-none p-0 m-0">
                        <li><a href="#features" class="text-sm text-slate-400 hover:text-white transition-colors">Features</a></li>
                        <li><a href="#modules"  class="text-sm text-slate-400 hover:text-white transition-colors">Modules</a></li>
                        <li><a href="#pricing"  class="text-sm text-slate-400 hover:text-white transition-colors">Pricing</a></li>
                        <li><a href="#download" class="text-sm text-slate-400 hover:text-white transition-colors">Download App</a></li>
                    </ul>
                </nav>

                {{-- Company --}}
                <nav aria-label="Company links">
                    <p class="text-sm font-semibold text-white mb-4 uppercase tracking-widest">Company</p>
                    <ul class="space-y-2.5 list-none p-0 m-0">
                        <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors">About Us</a></li>
                        <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors">Careers</a></li>
                        <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors">Blog</a></li>
                        <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors">Contact</a></li>
                    </ul>
                </nav>

                {{-- Support --}}
                <nav aria-label="Support links">
                    <p class="text-sm font-semibold text-white mb-4 uppercase tracking-widest">Support</p>
                    <ul class="space-y-2.5 list-none p-0 m-0">
                        <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors">Documentation</a></li>
                        <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors">Help Center</a></li>
                        <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="text-sm text-slate-400 hover:text-white transition-colors">Terms of Service</a></li>
                    </ul>
                </nav>
            </div>

            <div class="section-divider mb-8"></div>

            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-sm text-slate-400">
                    &copy; {{ date('Y') }} {{ config('app.name', 'POS ERP') }}. All rights reserved.
                </p>
                <p class="text-sm text-slate-500 flex items-center gap-2">
                    Built with
                    <i class="fas fa-heart text-red-500 text-xs" aria-hidden="true"></i>
                    <span class="sr-only">love</span>
                    on <a href="https://laravel.com" target="_blank" rel="noopener noreferrer"
                          class="text-cyan-400 hover:text-cyan-300">Laravel</a>
                </p>
            </div>
        </div>
    </footer>


    <script>
        // =============================================
        // WCAG 4.1.2 — Mobile menu toggle
        // Updates aria-expanded to reflect open/closed state
        // =============================================
        function toggleMobileMenu() {
            const menu   = document.getElementById('mobile-menu');
            const btn    = document.getElementById('mobile-menu-btn');
            const isOpen = menu.classList.toggle('open');

            // Update ARIA state
            btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            btn.setAttribute('aria-label', isOpen ? 'Close navigation menu' : 'Open navigation menu');

            // Update hamburger icon
            const icon = btn.querySelector('i');
            if (icon) {
                icon.className = isOpen ? 'fas fa-times text-xl' : 'fas fa-bars text-xl';
            }

            // Trap focus inside menu when open (WCAG 2.1.2)
            if (isOpen) {
                const firstLink = menu.querySelector('a');
                if (firstLink) firstLink.focus();
            }
        }

        // Close mobile menu on Escape key (WCAG 2.1.2)
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const menu = document.getElementById('mobile-menu');
                const btn  = document.getElementById('mobile-menu-btn');
                if (menu.classList.contains('open')) {
                    menu.classList.remove('open');
                    btn.setAttribute('aria-expanded', 'false');
                    btn.setAttribute('aria-label', 'Open navigation menu');
                    const icon = btn.querySelector('i');
                    if (icon) icon.className = 'fas fa-bars text-xl';
                    btn.focus(); // Return focus to trigger (WCAG 2.1.2)
                }
            }
        });

        // =============================================
        // Sticky navbar
        // =============================================
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('navbar-scrolled', window.scrollY > 50);
        });

        // =============================================
        // Smooth scroll for anchor links
        // =============================================
        document.querySelectorAll('a[href^="#"]').forEach(a => {
            a.addEventListener('click', e => {
                const target = document.querySelector(a.getAttribute('href'));
                if (target) {
                    e.preventDefault();

                    // Respect prefers-reduced-motion
                    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    target.scrollIntoView({
                        behavior: prefersReduced ? 'auto' : 'smooth',
                        block: 'start'
                    });

                    // Move focus to the target section for keyboard/screen reader users
                    if (!target.hasAttribute('tabindex')) {
                        target.setAttribute('tabindex', '-1');
                    }
                    target.focus({ preventScroll: true });

                    // Close mobile menu
                    document.getElementById('mobile-menu').classList.remove('open');
                    document.getElementById('mobile-menu-btn').setAttribute('aria-expanded', 'false');
                }
            });
        });

        // =============================================
        // Scroll reveal (respects reduced-motion)
        // =============================================
        const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (prefersReduced) {
            // Make all reveal elements visible immediately
            document.querySelectorAll('.reveal, .reveal-left, .reveal-right').forEach(el => {
                el.classList.add('visible');
            });
        } else {
            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.classList.add('visible');
                        }, (entry.target.dataset.delay || 0));
                        revealObserver.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.12,
                rootMargin: '0px 0px -40px 0px'
            });

            document.querySelectorAll('.reveal, .reveal-left, .reveal-right').forEach((el, i) => {
                el.dataset.delay = (i % 4) * 80;
                revealObserver.observe(el);
            });
        }

        // =============================================
        // Progress bar animation on scroll
        // =============================================
        const progressObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'none';
                    entry.target.offsetHeight; // reflow
                    entry.target.style.animation = '';
                    progressObserver.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.3
        });

        document.querySelectorAll('.progress-bar').forEach(el => progressObserver.observe(el));
    </script>

</body>

</html>
