<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="The all-in-one ERP & Point of Sale platform built for modern businesses. Manage sales, inventory, finance and multiple outlets from one place.">
    <title>{{ config('app.name', 'POS ERP') }} — Smart Business Management</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        * { font-family: 'Inter', sans-serif; scroll-behavior: smooth; }
        body { background-color: #060b18; color: #e2e8f0; }

        /* Gradient mesh background */
        .hero-bg {
            background: #060b18;
            background-image:
                radial-gradient(ellipse 80% 60% at 20% 20%, rgba(99,102,241,0.18) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 80%, rgba(16,185,129,0.12) 0%, transparent 60%),
                radial-gradient(ellipse 50% 40% at 60% 10%, rgba(139,92,246,0.1) 0%, transparent 50%);
        }

        /* Animated gradient text */
        .gradient-text {
            background: linear-gradient(135deg, #818cf8 0%, #6366f1 25%, #10b981 60%, #34d399 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .gradient-text-warm {
            background: linear-gradient(135deg, #f59e0b 0%, #ef4444 50%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Glass card */
        .glass {
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.08);
        }
        .glass-dark {
            background: rgba(13,17,35,0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(99,102,241,0.2);
        }
        .glass-light {
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.1);
        }

        /* Floating animated orb */
        @keyframes float1 { 0%,100%{transform:translate(0,0) scale(1);} 50%{transform:translate(30px,-20px) scale(1.08);} }
        @keyframes float2 { 0%,100%{transform:translate(0,0) scale(1);} 50%{transform:translate(-20px,25px) scale(1.06);} }
        @keyframes float3 { 0%,100%{transform:translate(0,0);} 33%{transform:translate(15px,-15px);} 66%{transform:translate(-10px,10px);} }
        @keyframes pulse-glow { 0%,100%{opacity:0.5;} 50%{opacity:1;} }
        @keyframes slide-up { from{opacity:0;transform:translateY(30px);} to{opacity:1;transform:translateY(0);} }
        @keyframes slide-in-left { from{opacity:0;transform:translateX(-40px);} to{opacity:1;transform:translateX(0);} }
        @keyframes counter-up { from{opacity:0;transform:scale(0.8);} to{opacity:1;transform:scale(1);} }
        @keyframes shimmer { 0%{background-position:-200% 0;} 100%{background-position:200% 0;} }
        @keyframes rotate-slow { from{transform:rotate(0deg);} to{transform:rotate(360deg);} }
        @keyframes bounce-subtle { 0%,100%{transform:translateY(0);} 50%{transform:translateY(-6px);} }

        .float-1 { animation: float1 7s ease-in-out infinite; }
        .float-2 { animation: float2 9s ease-in-out infinite; }
        .float-3 { animation: float3 11s ease-in-out infinite; }
        .pulse-glow { animation: pulse-glow 3s ease-in-out infinite; }
        .bounce-subtle { animation: bounce-subtle 3s ease-in-out infinite; }

        .animate-slide-up { animation: slide-up 0.7s ease forwards; }
        .animate-slide-up-2 { animation: slide-up 0.7s 0.15s ease both; }
        .animate-slide-up-3 { animation: slide-up 0.7s 0.3s ease both; }
        .animate-slide-up-4 { animation: slide-up 0.7s 0.45s ease both; }

        /* Hover card lift */
        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 60px rgba(99,102,241,0.2);
            border-color: rgba(99,102,241,0.4);
        }

        /* Glow button */
        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(99,102,241,0.35);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(99,102,241,0.55);
            background: linear-gradient(135deg, #818cf8, #a78bfa);
        }

        .btn-secondary {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.4);
            transform: translateY(-2px);
        }

        /* Navbar */
        .navbar-scrolled {
            background: rgba(6,11,24,0.92) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border-bottom: 1px solid rgba(255,255,255,0.08) !important;
        }

        /* Feature icon */
        .feature-icon {
            width: 52px; height: 52px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
        }

        /* Orb blobs */
        .orb {
            position: absolute; border-radius: 50%; filter: blur(80px); pointer-events: none;
        }

        /* Stats counter */
        .stat-number { font-size: 2.8rem; font-weight: 800; line-height: 1; }

        /* Download badge */
        .store-btn {
            display: flex; align-items: center; gap: 12px;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.14);
            border-radius: 14px; padding: 12px 20px;
            transition: all 0.3s ease; text-decoration: none; color: white;
            backdrop-filter: blur(8px);
        }
        .store-btn:hover {
            background: rgba(255,255,255,0.13);
            border-color: rgba(99,102,241,0.5);
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(99,102,241,0.25);
        }

        /* Section divider */
        .section-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(99,102,241,0.3), transparent);
        }

        /* Testimonial card */
        .testimonial-stars { color: #f59e0b; letter-spacing: 2px; }

        /* Mobile menu */
        #mobile-menu { transition: max-height 0.4s ease, opacity 0.4s ease; max-height: 0; opacity: 0; overflow: hidden; }
        #mobile-menu.open { max-height: 500px; opacity: 1; }

        /* Pricing card highlight */
        .pricing-popular {
            border-color: rgba(99,102,241,0.6) !important;
            background: linear-gradient(145deg, rgba(99,102,241,0.12), rgba(139,92,246,0.08)) !important;
            transform: scale(1.02);
        }

        /* Scroll reveal */
        .reveal { opacity: 0; transform: translateY(28px); transition: opacity 0.6s ease, transform 0.6s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        .reveal-left { opacity: 0; transform: translateX(-28px); transition: opacity 0.6s ease, transform 0.6s ease; }
        .reveal-left.visible { opacity: 1; transform: translateX(0); }
        .reveal-right { opacity: 0; transform: translateX(28px); transition: opacity 0.6s ease, transform 0.6s ease; }
        .reveal-right.visible { opacity: 1; transform: translateX(0); }

        /* Module pill tag */
        .module-tag {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 12px; border-radius: 100px; font-size: 12px; font-weight: 600;
        }

        /* Progress bar animation */
        @keyframes progress-fill { from{width:0;} to{width:var(--fill);} }
        .progress-bar { animation: progress-fill 1.5s 0.5s ease forwards; width: 0; }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #060b18; }
        ::-webkit-scrollbar-thumb { background: rgba(99,102,241,0.4); border-radius: 3px; }
    </style>
</head>
<body>

<!-- ============================
     NAVIGATION
============================= -->
<nav id="navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 py-4 px-6">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
        <!-- Logo -->
        <a href="/" class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                <i class="fas fa-cash-register text-white text-sm"></i>
            </div>
            <span class="font-bold text-lg text-white tracking-tight">{{ config('app.name', 'POS ERP') }}</span>
        </a>

        <!-- Desktop nav -->
        <div class="hidden md:flex items-center gap-8">
            <a href="#features" class="text-sm text-slate-400 hover:text-white transition-colors">Features</a>
            <a href="#modules" class="text-sm text-slate-400 hover:text-white transition-colors">Modules</a>
            <a href="#pricing" class="text-sm text-slate-400 hover:text-white transition-colors">Pricing</a>
            <a href="#download" class="text-sm text-slate-400 hover:text-white transition-colors">Download</a>
        </div>

        <!-- CTA -->
        <div class="hidden md:flex items-center gap-3">
            @auth
                <a href="{{ url('/admin/dashboard') }}" class="btn-primary text-white text-sm font-semibold px-5 py-2.5 rounded-xl">
                    Go to Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-secondary text-sm text-slate-300 font-medium px-5 py-2.5 rounded-xl">
                    Sign In
                </a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn-primary text-white text-sm font-semibold px-5 py-2.5 rounded-xl">
                        Get Started Free
                    </a>
                @endif
            @endauth
        </div>

        <!-- Mobile hamburger -->
        <button onclick="document.getElementById('mobile-menu').classList.toggle('open')" class="md:hidden text-slate-400 hover:text-white p-2">
            <i class="fas fa-bars text-xl"></i>
        </button>
    </div>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="md:hidden glass-dark mx-0 mt-3 rounded-2xl">
        <div class="p-5 flex flex-col gap-3">
            <a href="#features" class="text-sm text-slate-300 hover:text-white py-2 border-b border-white/5">Features</a>
            <a href="#modules" class="text-sm text-slate-300 hover:text-white py-2 border-b border-white/5">Modules</a>
            <a href="#pricing" class="text-sm text-slate-300 hover:text-white py-2 border-b border-white/5">Pricing</a>
            <a href="#download" class="text-sm text-slate-300 hover:text-white py-2 border-b border-white/5">Download</a>
            <div class="flex gap-3 pt-2">
                @auth
                    <a href="{{ url('/admin/dashboard') }}" class="btn-primary text-white text-sm font-semibold px-5 py-2.5 rounded-xl flex-1 text-center">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn-secondary text-sm text-slate-300 font-medium px-5 py-2.5 rounded-xl flex-1 text-center">Sign In</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn-primary text-white text-sm font-semibold px-5 py-2.5 rounded-xl flex-1 text-center">Get Started</a>
                    @endif
                @endauth
            </div>
        </div>
    </div>
</nav>


<!-- ============================
     HERO SECTION
============================= -->
<section class="hero-bg relative min-h-screen flex items-center overflow-hidden pt-20">
    <!-- Background orbs -->
    <div class="orb w-96 h-96 bg-indigo-600/20 top-10 -left-20 float-1"></div>
    <div class="orb w-80 h-80 bg-violet-600/15 bottom-20 right-0 float-2"></div>
    <div class="orb w-64 h-64 bg-emerald-500/10 top-1/2 right-1/4 float-3"></div>

    <!-- Grid overlay -->
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: linear-gradient(rgba(255,255,255,0.5) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.5) 1px, transparent 1px); background-size: 60px 60px;"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-6 py-20 w-full">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            <!-- Left content -->
            <div>
                <!-- Badge -->
                <div class="animate-slide-up inline-flex items-center gap-2 glass rounded-full px-4 py-2 mb-8">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 pulse-glow"></span>
                    <span class="text-xs font-medium text-emerald-300">Now with Multi-Outlet Support</span>
                </div>

                <!-- Headline -->
                <h1 class="animate-slide-up-2 text-5xl lg:text-6xl xl:text-7xl font-black leading-[1.05] tracking-tight mb-6">
                    <span class="text-white">Manage Your</span><br>
                    <span class="gradient-text">Business Smarter</span><br>
                    <span class="text-white">Not Harder.</span>
                </h1>

                <!-- Sub -->
                <p class="animate-slide-up-3 text-lg text-slate-400 leading-relaxed mb-10 max-w-lg">
                    The complete ERP & POS platform for modern retail. From cashier to boardroom — control sales, inventory, finance, and multiple outlets from one elegant dashboard.
                </p>

                <!-- CTA row -->
                <div class="animate-slide-up-4 flex flex-wrap gap-4 mb-12">
                    @auth
                        <a href="{{ url('/admin/dashboard') }}" class="btn-primary text-white font-semibold px-8 py-4 rounded-2xl text-base flex items-center gap-2">
                            <i class="fas fa-tachometer-alt"></i> Open Dashboard
                        </a>
                    @else
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn-primary text-white font-semibold px-8 py-4 rounded-2xl text-base flex items-center gap-2">
                                <i class="fas fa-rocket"></i> Start Free Trial
                            </a>
                        @endif
                        <a href="{{ route('login') }}" class="btn-secondary text-slate-300 font-medium px-8 py-4 rounded-2xl text-base flex items-center gap-2">
                            <i class="fas fa-sign-in-alt"></i> Sign In
                        </a>
                    @endauth
                </div>

                <!-- Trust badges -->
                <div class="animate-slide-up-4 flex flex-wrap items-center gap-6">
                    <div class="flex items-center gap-2 text-slate-500 text-sm">
                        <i class="fas fa-shield-alt text-emerald-400"></i>
                        <span>Bank-grade security</span>
                    </div>
                    <div class="flex items-center gap-2 text-slate-500 text-sm">
                        <i class="fas fa-bolt text-indigo-400"></i>
                        <span>Real-time sync</span>
                    </div>
                    <div class="flex items-center gap-2 text-slate-500 text-sm">
                        <i class="fas fa-cloud text-violet-400"></i>
                        <span>Cloud-based</span>
                    </div>
                    <div class="flex items-center gap-2 text-slate-500 text-sm">
                        <i class="fas fa-gift text-amber-400"></i>
                        <span>14-day free trial</span>
                    </div>
                </div>
            </div>

            <!-- Right: Dashboard mockup -->
            <div class="relative bounce-subtle hidden lg:block">
                <!-- Outer glow -->
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 to-violet-500/10 rounded-3xl blur-3xl scale-105"></div>

                <!-- Mock window -->
                <div class="relative glass-dark rounded-3xl overflow-hidden shadow-2xl shadow-indigo-900/40">
                    <!-- Title bar -->
                    <div class="flex items-center gap-2 px-5 py-4 border-b border-white/5">
                        <div class="w-3 h-3 rounded-full bg-red-500/70"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-500/70"></div>
                        <div class="w-3 h-3 rounded-full bg-green-500/70"></div>
                        <div class="flex-1 text-center">
                            <span class="text-xs text-slate-500 font-mono">{{ config('app.name') }} — Dashboard</span>
                        </div>
                    </div>

                    <!-- Mock dashboard body -->
                    <div class="p-5 space-y-4">
                        <!-- KPI row -->
                        <div class="grid grid-cols-3 gap-3">
                            <div class="glass rounded-2xl p-4">
                                <p class="text-xs text-slate-500 mb-1">Today's Sales</p>
                                <p class="text-xl font-bold text-white">Rp 4.2M</p>
                                <p class="text-xs text-emerald-400 mt-1"><i class="fas fa-arrow-up"></i> +12.4%</p>
                            </div>
                            <div class="glass rounded-2xl p-4">
                                <p class="text-xs text-slate-500 mb-1">Orders</p>
                                <p class="text-xl font-bold text-white">138</p>
                                <p class="text-xs text-emerald-400 mt-1"><i class="fas fa-arrow-up"></i> +8.1%</p>
                            </div>
                            <div class="glass rounded-2xl p-4">
                                <p class="text-xs text-slate-500 mb-1">Stock Alerts</p>
                                <p class="text-xl font-bold text-amber-400">7</p>
                                <p class="text-xs text-amber-400 mt-1"><i class="fas fa-exclamation-triangle"></i> Low stock</p>
                            </div>
                        </div>

                        <!-- Chart placeholder -->
                        <div class="glass rounded-2xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-xs font-semibold text-slate-300">Revenue — Last 7 Days</p>
                                <span class="text-xs text-indigo-400">View report →</span>
                            </div>
                            <!-- Bar chart visual -->
                            <div class="flex items-end gap-2 h-20">
                                <div class="flex-1 rounded-t-md bg-indigo-500/30" style="height:45%"></div>
                                <div class="flex-1 rounded-t-md bg-indigo-500/50" style="height:60%"></div>
                                <div class="flex-1 rounded-t-md bg-indigo-500/40" style="height:35%"></div>
                                <div class="flex-1 rounded-t-md bg-indigo-500/60" style="height:75%"></div>
                                <div class="flex-1 rounded-t-md bg-indigo-400/50" style="height:55%"></div>
                                <div class="flex-1 rounded-t-md bg-indigo-400/70" style="height:85%"></div>
                                <div class="flex-1 rounded-t-md bg-indigo-400" style="height:100%"></div>
                            </div>
                        </div>

                        <!-- Recent orders -->
                        <div class="glass rounded-2xl p-4">
                            <p class="text-xs font-semibold text-slate-300 mb-3">Recent Transactions</p>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-lg bg-emerald-500/20 flex items-center justify-center"><i class="fas fa-receipt text-emerald-400" style="font-size:9px"></i></div>
                                        <span class="text-slate-400">ORD-20260001</span>
                                    </div>
                                    <span class="text-white font-medium">Rp 125,000</span>
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400">Paid</span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-lg bg-indigo-500/20 flex items-center justify-center"><i class="fas fa-receipt text-indigo-400" style="font-size:9px"></i></div>
                                        <span class="text-slate-400">ORD-20260002</span>
                                    </div>
                                    <span class="text-white font-medium">Rp 87,500</span>
                                    <span class="px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-400">Pending</span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-lg bg-violet-500/20 flex items-center justify-center"><i class="fas fa-receipt text-violet-400" style="font-size:9px"></i></div>
                                        <span class="text-slate-400">ORD-20260003</span>
                                    </div>
                                    <span class="text-white font-medium">Rp 240,000</span>
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400">Paid</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Floating badge cards -->
                <div class="absolute -bottom-6 -left-8 glass-dark rounded-2xl p-3 shadow-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/20 flex items-center justify-center">
                            <i class="fas fa-chart-line text-emerald-400 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Monthly Revenue</p>
                            <p class="text-sm font-bold text-white">Rp 128M</p>
                            <p class="text-xs text-emerald-400"><i class="fas fa-arrow-up"></i> 23% growth</p>
                        </div>
                    </div>
                </div>

                <div class="absolute -top-4 -right-4 glass-dark rounded-2xl p-3 shadow-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/20 flex items-center justify-center">
                            <i class="fas fa-store text-indigo-400 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Active Outlets</p>
                            <p class="text-sm font-bold text-white">12 Outlets</p>
                            <p class="text-xs text-indigo-400">All synced</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom wave -->
    <div class="absolute bottom-0 left-0 right-0">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 80" class="block w-full" style="fill:#060b18; opacity:0.8">
            <path d="M0,40 C360,80 1080,0 1440,40 L1440,80 L0,80 Z"/>
        </svg>
    </div>
</section>


<!-- ============================
     SOCIAL PROOF / STATS
============================= -->
<section class="py-20 bg-[#060b18]">
    <div class="max-w-7xl mx-auto px-6">
        <!-- Logos row -->
        <p class="text-center text-xs font-semibold tracking-widest text-slate-600 uppercase mb-10">Trusted by businesses across industries</p>
        <div class="flex flex-wrap justify-center gap-10 mb-20 items-center">
            <span class="text-slate-600 font-bold text-lg tracking-tight">Kopi Nusantara</span>
            <span class="text-slate-600 font-bold text-lg tracking-tight">Warung Pak Budi</span>
            <span class="text-slate-600 font-bold text-lg tracking-tight">Toko Makmur</span>
            <span class="text-slate-600 font-bold text-lg tracking-tight">Resto Bahari</span>
            <span class="text-slate-600 font-bold text-lg tracking-tight">Minimart 24</span>
        </div>

        <!-- Stats grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="reveal text-center glass rounded-3xl p-8 card-hover">
                <div class="stat-number gradient-text mb-2">12K+</div>
                <p class="text-slate-400 text-sm">Active Merchants</p>
            </div>
            <div class="reveal text-center glass rounded-3xl p-8 card-hover">
                <div class="stat-number gradient-text mb-2">2.4M</div>
                <p class="text-slate-400 text-sm">Transactions / Month</p>
            </div>
            <div class="reveal text-center glass rounded-3xl p-8 card-hover">
                <div class="stat-number gradient-text mb-2">99.9%</div>
                <p class="text-slate-400 text-sm">Uptime SLA</p>
            </div>
            <div class="reveal text-center glass rounded-3xl p-8 card-hover">
                <div class="stat-number gradient-text mb-2">48</div>
                <p class="text-slate-400 text-sm">Cities Covered</p>
            </div>
        </div>
    </div>
</section>


<!-- ============================
     FEATURES
============================= -->
<section id="features" class="py-24 bg-[#070c1b]">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-16">
            <div class="reveal inline-flex items-center gap-2 glass rounded-full px-4 py-2 mb-4">
                <i class="fas fa-star text-indigo-400 text-xs"></i>
                <span class="text-xs font-semibold text-indigo-300 uppercase tracking-widest">Everything You Need</span>
            </div>
            <h2 class="reveal text-4xl lg:text-5xl font-black text-white mb-4 leading-tight">
                Built for <span class="gradient-text">speed & scale</span>
            </h2>
            <p class="reveal text-lg text-slate-400 max-w-2xl mx-auto">
                Every feature is designed to help your team work faster, sell smarter, and grow without limits.
            </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Feature 1 -->
            <div class="reveal glass rounded-3xl p-7 card-hover">
                <div class="feature-icon bg-indigo-500/15 text-indigo-400 mb-5">
                    <i class="fas fa-cash-register"></i>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Point of Sale</h3>
                <p class="text-slate-400 text-sm leading-relaxed">Lightning-fast checkout experience with barcode scanning, split payments, discounts, and receipt printing — all on any device.</p>
                <div class="mt-4 flex gap-2 flex-wrap">
                    <span class="module-tag bg-indigo-500/10 text-indigo-300">Barcode</span>
                    <span class="module-tag bg-indigo-500/10 text-indigo-300">Split Pay</span>
                    <span class="module-tag bg-indigo-500/10 text-indigo-300">Offline Mode</span>
                </div>
            </div>

            <!-- Feature 2 -->
            <div class="reveal glass rounded-3xl p-7 card-hover">
                <div class="feature-icon bg-emerald-500/15 text-emerald-400 mb-5">
                    <i class="fas fa-boxes"></i>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Inventory Control</h3>
                <p class="text-slate-400 text-sm leading-relaxed">Real-time stock tracking with automatic deductions on every sale. Transfer stock between outlets, set reorder points, and never run out.</p>
                <div class="mt-4 flex gap-2 flex-wrap">
                    <span class="module-tag bg-emerald-500/10 text-emerald-300">Real-time</span>
                    <span class="module-tag bg-emerald-500/10 text-emerald-300">Transfer</span>
                    <span class="module-tag bg-emerald-500/10 text-emerald-300">Alerts</span>
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="reveal glass rounded-3xl p-7 card-hover">
                <div class="feature-icon bg-violet-500/15 text-violet-400 mb-5">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Finance & Reporting</h3>
                <p class="text-slate-400 text-sm leading-relaxed">Comprehensive financial reports, revenue analytics, payment breakdowns, and export to Excel/PDF. Know your numbers at a glance.</p>
                <div class="mt-4 flex gap-2 flex-wrap">
                    <span class="module-tag bg-violet-500/10 text-violet-300">P&L</span>
                    <span class="module-tag bg-violet-500/10 text-violet-300">Export</span>
                    <span class="module-tag bg-violet-500/10 text-violet-300">Charts</span>
                </div>
            </div>

            <!-- Feature 4 -->
            <div class="reveal glass rounded-3xl p-7 card-hover">
                <div class="feature-icon bg-amber-500/15 text-amber-400 mb-5">
                    <i class="fas fa-store-alt"></i>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Multi-Outlet</h3>
                <p class="text-slate-400 text-sm leading-relaxed">Manage unlimited store locations from a single dashboard. Each outlet has its own staff, inventory, and performance metrics.</p>
                <div class="mt-4 flex gap-2 flex-wrap">
                    <span class="module-tag bg-amber-500/10 text-amber-300">Centralized</span>
                    <span class="module-tag bg-amber-500/10 text-amber-300">Per-Outlet</span>
                    <span class="module-tag bg-amber-500/10 text-amber-300">Unlimited</span>
                </div>
            </div>

            <!-- Feature 5 -->
            <div class="reveal glass rounded-3xl p-7 card-hover">
                <div class="feature-icon bg-pink-500/15 text-pink-400 mb-5">
                    <i class="fas fa-users-cog"></i>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Role & Permissions</h3>
                <p class="text-slate-400 text-sm leading-relaxed">Granular access control with custom roles. Assign permissions per feature, per module, even per outlet — keeping data secure.</p>
                <div class="mt-4 flex gap-2 flex-wrap">
                    <span class="module-tag bg-pink-500/10 text-pink-300">RBAC</span>
                    <span class="module-tag bg-pink-500/10 text-pink-300">Custom Roles</span>
                    <span class="module-tag bg-pink-500/10 text-pink-300">Audit Log</span>
                </div>
            </div>

            <!-- Feature 6 -->
            <div class="reveal glass rounded-3xl p-7 card-hover">
                <div class="feature-icon bg-cyan-500/15 text-cyan-400 mb-5">
                    <i class="fas fa-layer-group"></i>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Multi-Tenant SaaS</h3>
                <p class="text-slate-400 text-sm leading-relaxed">Fully isolated tenant environments. Perfect for franchise owners, holding companies, or white-label resellers. One platform, many businesses.</p>
                <div class="mt-4 flex gap-2 flex-wrap">
                    <span class="module-tag bg-cyan-500/10 text-cyan-300">Isolated</span>
                    <span class="module-tag bg-cyan-500/10 text-cyan-300">Scalable</span>
                    <span class="module-tag bg-cyan-500/10 text-cyan-300">White-label</span>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ============================
     MODULES DEEP DIVE
============================= -->
<section id="modules" class="py-24 bg-[#060b18]">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-16">
            <h2 class="reveal text-4xl lg:text-5xl font-black text-white mb-4">
                One platform. <span class="gradient-text">Every module.</span>
            </h2>
            <p class="reveal text-slate-400 text-lg max-w-xl mx-auto">All modules are deeply integrated — data flows automatically so you never have to re-enter anything.</p>
        </div>

        <div class="grid lg:grid-cols-2 gap-8 items-center mb-16">
            <!-- Left modules list -->
            <div class="space-y-4">
                <div class="reveal-left glass-dark rounded-2xl p-5 flex items-start gap-4 card-hover cursor-pointer border border-indigo-500/30">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/20 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-cash-register text-indigo-400 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-white mb-1">POS Module</h3>
                        <p class="text-sm text-slate-400">Cashier interface, order management, payment processing, customer loyalty, and receipt printing.</p>
                    </div>
                    <i class="fas fa-chevron-right text-indigo-400 ml-auto mt-1 flex-shrink-0"></i>
                </div>

                <div class="reveal-left glass rounded-2xl p-5 flex items-start gap-4 card-hover cursor-pointer">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-warehouse text-emerald-400 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-white mb-1">Inventory Module</h3>
                        <p class="text-sm text-slate-400">Stock tracking, movement history, adjustments, inter-outlet transfers, and low-stock alerts.</p>
                    </div>
                    <i class="fas fa-chevron-right text-slate-600 ml-auto mt-1 flex-shrink-0"></i>
                </div>

                <div class="reveal-left glass rounded-2xl p-5 flex items-start gap-4 card-hover cursor-pointer">
                    <div class="w-12 h-12 rounded-2xl bg-violet-500/20 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-file-invoice-dollar text-violet-400 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-white mb-1">Finance Module</h3>
                        <p class="text-sm text-slate-400">Revenue tracking, expense management, profit & loss statements, and financial dashboards.</p>
                    </div>
                    <i class="fas fa-chevron-right text-slate-600 ml-auto mt-1 flex-shrink-0"></i>
                </div>

                <div class="reveal-left glass rounded-2xl p-5 flex items-start gap-4 card-hover cursor-pointer">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/20 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-chart-bar text-amber-400 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-white mb-1">Reporting Module</h3>
                        <p class="text-sm text-slate-400">Detailed sales, inventory, and staff reports. Filter by date, outlet, category — export in any format.</p>
                    </div>
                    <i class="fas fa-chevron-right text-slate-600 ml-auto mt-1 flex-shrink-0"></i>
                </div>
            </div>

            <!-- Right metrics card -->
            <div class="reveal-right glass-dark rounded-3xl p-8 space-y-6">
                <h3 class="font-bold text-white text-xl">Live Performance Overview</h3>

                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-slate-400">Sales Processing Speed</span>
                            <span class="text-white font-semibold">98%</span>
                        </div>
                        <div class="h-2 rounded-full bg-white/5 overflow-hidden">
                            <div class="progress-bar h-full rounded-full bg-gradient-to-r from-indigo-500 to-violet-500" style="--fill:98%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-slate-400">Inventory Accuracy</span>
                            <span class="text-white font-semibold">99.7%</span>
                        </div>
                        <div class="h-2 rounded-full bg-white/5 overflow-hidden">
                            <div class="progress-bar h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500" style="--fill:99.7%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-slate-400">Report Generation</span>
                            <span class="text-white font-semibold">95%</span>
                        </div>
                        <div class="h-2 rounded-full bg-white/5 overflow-hidden">
                            <div class="progress-bar h-full rounded-full bg-gradient-to-r from-violet-500 to-pink-500" style="--fill:95%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-slate-400">System Uptime</span>
                            <span class="text-white font-semibold">99.9%</span>
                        </div>
                        <div class="h-2 rounded-full bg-white/5 overflow-hidden">
                            <div class="progress-bar h-full rounded-full bg-gradient-to-r from-amber-500 to-orange-500" style="--fill:99.9%"></div>
                        </div>
                    </div>
                </div>

                <div class="section-divider"></div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="glass rounded-2xl p-4 text-center">
                        <p class="text-2xl font-black text-white">< 0.3s</p>
                        <p class="text-xs text-slate-500 mt-1">Avg. Response Time</p>
                    </div>
                    <div class="glass rounded-2xl p-4 text-center">
                        <p class="text-2xl font-black text-white">24/7</p>
                        <p class="text-xs text-slate-500 mt-1">Cloud Availability</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ============================
     HOW IT WORKS
============================= -->
<section class="py-24 bg-[#070c1b]">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-16">
            <h2 class="reveal text-4xl lg:text-5xl font-black text-white mb-4">
                Get running in <span class="gradient-text">3 steps</span>
            </h2>
            <p class="reveal text-slate-400 text-lg">No lengthy onboarding. No IT team required. Start selling in minutes.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8 relative">
            <!-- Step connector line -->
            <div class="hidden md:block absolute top-14 left-1/3 right-1/3 h-0.5 bg-gradient-to-r from-indigo-500 via-violet-500 to-emerald-500 opacity-40"></div>

            <div class="reveal text-center">
                <div class="w-28 h-28 rounded-3xl bg-gradient-to-br from-indigo-600 to-indigo-800 flex items-center justify-center mx-auto mb-6 shadow-2xl shadow-indigo-900/40 relative">
                    <i class="fas fa-building text-white text-3xl"></i>
                    <div class="absolute -top-3 -right-3 w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-black">1</div>
                </div>
                <h3 class="text-xl font-bold text-white mb-3">Create Your Account</h3>
                <p class="text-slate-400 text-sm leading-relaxed">Register your business, add your outlets, and configure your product catalog. Takes under 5 minutes.</p>
            </div>

            <div class="reveal text-center">
                <div class="w-28 h-28 rounded-3xl bg-gradient-to-br from-violet-600 to-violet-800 flex items-center justify-center mx-auto mb-6 shadow-2xl shadow-violet-900/40 relative">
                    <i class="fas fa-sliders-h text-white text-3xl"></i>
                    <div class="absolute -top-3 -right-3 w-8 h-8 rounded-full bg-violet-500 flex items-center justify-center text-white text-xs font-black">2</div>
                </div>
                <h3 class="text-xl font-bold text-white mb-3">Configure & Invite Team</h3>
                <p class="text-slate-400 text-sm leading-relaxed">Set roles, assign staff to outlets, and configure payment methods. Customize to fit your workflow.</p>
            </div>

            <div class="reveal text-center">
                <div class="w-28 h-28 rounded-3xl bg-gradient-to-br from-emerald-600 to-emerald-800 flex items-center justify-center mx-auto mb-6 shadow-2xl shadow-emerald-900/40 relative">
                    <i class="fas fa-rocket text-white text-3xl"></i>
                    <div class="absolute -top-3 -right-3 w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs font-black">3</div>
                </div>
                <h3 class="text-xl font-bold text-white mb-3">Start Selling</h3>
                <p class="text-slate-400 text-sm leading-relaxed">Open the cashier, process orders, and watch your dashboard fill with real-time insights.</p>
            </div>
        </div>
    </div>
</section>


<!-- ============================
     DOWNLOAD APP
============================= -->
<section id="download" class="py-24 bg-[#060b18]">
    <div class="max-w-7xl mx-auto px-6">
        <div class="glass-dark rounded-3xl overflow-hidden relative">
            <!-- Background decoration -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-br from-indigo-600/15 to-violet-600/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-gradient-to-tr from-emerald-600/10 to-teal-600/8 rounded-full blur-3xl"></div>

            <div class="relative z-10 grid lg:grid-cols-2 gap-12 p-10 lg:p-16 items-center">
                <!-- Text side -->
                <div>
                    <div class="inline-flex items-center gap-2 glass rounded-full px-4 py-2 mb-6">
                        <i class="fas fa-mobile-alt text-indigo-400 text-xs"></i>
                        <span class="text-xs font-semibold text-indigo-300 uppercase tracking-widest">Mobile App</span>
                    </div>
                    <h2 class="text-4xl lg:text-5xl font-black text-white leading-tight mb-4">
                        Take your business<br><span class="gradient-text">everywhere you go</span>
                    </h2>
                    <p class="text-slate-400 text-lg leading-relaxed mb-8">
                        The {{ config('app.name') }} mobile app lets you monitor sales, check inventory, approve transactions, and manage your team — all from your phone.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <!-- App Store -->
                        <a href="#" class="store-btn">
                            <svg class="w-8 h-8 flex-shrink-0" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                                <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                            </svg>
                            <div>
                                <p class="text-xs text-slate-400">Download on the</p>
                                <p class="font-semibold text-white">App Store</p>
                            </div>
                        </a>

                        <!-- Google Play -->
                        <a href="#" class="store-btn">
                            <svg class="w-8 h-8 flex-shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 20.5v-17c0-.83.94-1.3 1.6-.8l14 8.5c.6.37.6 1.23 0 1.6l-14 8.5c-.66.5-1.6.03-1.6-.8z" fill="#10b981"/>
                                <path d="M3 20.5L13.5 10 3 3.5v17z" fill="#34d399" opacity=".7"/>
                                <path d="M3 3.5l10.5 6.5L18 7.5 5.6 3.7C4.5 3.3 3.4 3.8 3 4.2v-.7z" fill="#6ee7b7" opacity=".5"/>
                            </svg>
                            <div>
                                <p class="text-xs text-slate-400">Get it on</p>
                                <p class="font-semibold text-white">Google Play</p>
                            </div>
                        </a>

                        <!-- Huawei AppGallery -->
                        <a href="#" class="store-btn">
                            <svg class="w-8 h-8 flex-shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="24" height="24" rx="6" fill="#CC0000" opacity=".15"/>
                                <path d="M12 4C7.6 4 4 7.6 4 12s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 14c-3.3 0-6-2.7-6-6s2.7-6 6-6 6 2.7 6 6-2.7 6-6 6zm-1-9v4l3.5 2-0.7 1.2L10 14.5V9h1z" fill="#ef4444"/>
                            </svg>
                            <div>
                                <p class="text-xs text-slate-400">Explore on</p>
                                <p class="font-semibold text-white">AppGallery</p>
                            </div>
                        </a>
                    </div>

                    <!-- QR hint -->
                    <p class="text-xs text-slate-600 mt-6 flex items-center gap-2">
                        <i class="fas fa-info-circle text-slate-500"></i>
                        Available soon — links will go live on launch day
                    </p>
                </div>

                <!-- Phone mockup side -->
                <div class="flex justify-center">
                    <div class="relative">
                        <!-- Phone frame -->
                        <div class="w-64 h-auto glass-dark rounded-[3rem] p-3 shadow-2xl shadow-indigo-900/40 border border-white/10">
                            <div class="bg-[#040810] rounded-[2.5rem] overflow-hidden">
                                <!-- Notch -->
                                <div class="flex justify-center pt-3 pb-2">
                                    <div class="w-24 h-5 rounded-full bg-black"></div>
                                </div>
                                <!-- App UI -->
                                <div class="px-4 pb-6 space-y-3">
                                    <div class="text-center py-2">
                                        <p class="text-xs text-slate-500">Good morning,</p>
                                        <p class="text-sm font-bold text-white">Admin Dashboard</p>
                                    </div>
                                    <!-- Mini KPIs -->
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="glass rounded-xl p-3 text-center">
                                            <p style="font-size:10px" class="text-slate-500">Sales</p>
                                            <p class="text-white font-bold text-sm">4.2M</p>
                                        </div>
                                        <div class="glass rounded-xl p-3 text-center">
                                            <p style="font-size:10px" class="text-slate-500">Orders</p>
                                            <p class="text-white font-bold text-sm">138</p>
                                        </div>
                                    </div>
                                    <!-- Mini chart -->
                                    <div class="glass rounded-xl p-3">
                                        <p style="font-size:10px" class="text-slate-500 mb-2">Today's Revenue</p>
                                        <div class="flex items-end gap-1 h-12">
                                            <div class="flex-1 rounded-t bg-indigo-500/40" style="height:40%"></div>
                                            <div class="flex-1 rounded-t bg-indigo-500/50" style="height:65%"></div>
                                            <div class="flex-1 rounded-t bg-indigo-500/60" style="height:50%"></div>
                                            <div class="flex-1 rounded-t bg-indigo-400/70" style="height:80%"></div>
                                            <div class="flex-1 rounded-t bg-indigo-400" style="height:60%"></div>
                                            <div class="flex-1 rounded-t bg-emerald-400" style="height:100%"></div>
                                        </div>
                                    </div>
                                    <!-- Nav bar -->
                                    <div class="glass rounded-2xl p-3 flex justify-around">
                                        <i class="fas fa-home text-indigo-400" style="font-size:14px"></i>
                                        <i class="fas fa-cash-register text-slate-600" style="font-size:14px"></i>
                                        <i class="fas fa-boxes text-slate-600" style="font-size:14px"></i>
                                        <i class="fas fa-chart-bar text-slate-600" style="font-size:14px"></i>
                                        <i class="fas fa-user text-slate-600" style="font-size:14px"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Floating elements around phone -->
                        <div class="absolute -right-16 top-20 glass-dark rounded-2xl p-3 shadow-xl float-1">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                                    <i class="fas fa-bell text-emerald-400 text-sm"></i>
                                </div>
                                <div>
                                    <p style="font-size:10px" class="text-slate-400">New Order!</p>
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
                                    <p style="font-size:10px" class="text-slate-400">Low Stock</p>
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


<!-- ============================
     TESTIMONIALS
============================= -->
<section class="py-24 bg-[#070c1b]">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-16">
            <h2 class="reveal text-4xl lg:text-5xl font-black text-white mb-4">
                Loved by <span class="gradient-text">business owners</span>
            </h2>
            <p class="reveal text-slate-400 text-lg">Don't take our word for it — hear from the people who use it daily.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
            <div class="reveal glass rounded-3xl p-7 card-hover">
                <div class="testimonial-stars mb-4">★★★★★</div>
                <p class="text-slate-300 text-sm leading-relaxed mb-6">"This system completely changed how we run our 4 outlets. The inventory syncing alone saves my team 3 hours every day. The dashboard is absolutely beautiful."</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white font-bold text-sm">AB</div>
                    <div>
                        <p class="text-sm font-semibold text-white">Ahmad Bachtiar</p>
                        <p class="text-xs text-slate-500">Owner, Kopi Nusantara</p>
                    </div>
                </div>
            </div>

            <div class="reveal glass rounded-3xl p-7 card-hover">
                <div class="testimonial-stars mb-4">★★★★★</div>
                <p class="text-slate-300 text-sm leading-relaxed mb-6">"The POS module is so fast — my cashiers love it. No freezing, no crashes. And the financial reports give me exactly what I need to make decisions quickly."</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-sm">SR</div>
                    <div>
                        <p class="text-sm font-semibold text-white">Siti Rahayu</p>
                        <p class="text-xs text-slate-500">Manager, Toko Makmur</p>
                    </div>
                </div>
            </div>

            <div class="reveal glass rounded-3xl p-7 card-hover">
                <div class="testimonial-stars mb-4">★★★★★</div>
                <p class="text-slate-300 text-sm leading-relaxed mb-6">"We switched from a manual spreadsheet system. The difference is night and day. Setup took 30 minutes and we've never looked back. Best investment we've made."</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white font-bold text-sm">DW</div>
                    <div>
                        <p class="text-sm font-semibold text-white">Dodi Wibowo</p>
                        <p class="text-xs text-slate-500">Director, Resto Bahari</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ============================
     FREE TRIAL SECTION
============================= -->
<section class="py-20 bg-[#060b18]">
    <div class="max-w-7xl mx-auto px-6">
        <div class="reveal glass-dark rounded-3xl p-10 lg:p-14 overflow-hidden relative">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-600/8 via-indigo-600/6 to-violet-600/5 rounded-3xl"></div>
            <div class="absolute -top-16 -right-16 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-16 -left-16 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl"></div>

            <div class="relative z-10 grid lg:grid-cols-2 gap-12 items-center">
                <!-- Left -->
                <div>
                    <div class="inline-flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/20 rounded-full px-4 py-2 mb-6">
                        <i class="fas fa-gift text-emerald-400 text-sm"></i>
                        <span class="text-xs font-semibold text-emerald-300 uppercase tracking-wider">Free Trial</span>
                    </div>

                    <h2 class="text-4xl lg:text-5xl font-black text-white mb-4 leading-tight">
                        Try everything<br><span class="gradient-text">free for 14 days</span>
                    </h2>
                    <p class="text-slate-400 text-lg mb-8 leading-relaxed">
                        Get full access to all Professional features the moment you sign up. No credit card. No commitment. Cancel anytime — your data stays safe.
                    </p>

                    @guest
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn-primary inline-flex items-center gap-2 text-white font-bold px-8 py-4 rounded-2xl text-base">
                                <i class="fas fa-rocket"></i> Start Free Trial
                            </a>
                        @endif
                    @endguest

                    <p class="text-xs text-slate-600 mt-4 flex items-center gap-2">
                        <i class="fas fa-lock text-slate-600"></i>
                        No credit card required. Cancel before the trial ends and you won't be charged.
                    </p>
                </div>

                <!-- Right: What you get -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="glass rounded-2xl p-5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/20 flex items-center justify-center mb-3">
                            <i class="fas fa-cash-register text-indigo-400"></i>
                        </div>
                        <p class="text-sm font-semibold text-white mb-1">Full POS Module</p>
                        <p class="text-xs text-slate-500">Cashier, receipts, returns — all included</p>
                    </div>
                    <div class="glass rounded-2xl p-5">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/20 flex items-center justify-center mb-3">
                            <i class="fas fa-boxes text-emerald-400"></i>
                        </div>
                        <p class="text-sm font-semibold text-white mb-1">Inventory Control</p>
                        <p class="text-xs text-slate-500">Stock tracking across all outlets</p>
                    </div>
                    <div class="glass rounded-2xl p-5">
                        <div class="w-10 h-10 rounded-xl bg-violet-500/20 flex items-center justify-center mb-3">
                            <i class="fas fa-chart-bar text-violet-400"></i>
                        </div>
                        <p class="text-sm font-semibold text-white mb-1">Advanced Reports</p>
                        <p class="text-xs text-slate-500">Revenue, profit & analytics</p>
                    </div>
                    <div class="glass rounded-2xl p-5">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center mb-3">
                            <i class="fas fa-users text-amber-400"></i>
                        </div>
                        <p class="text-sm font-semibold text-white mb-1">Team Management</p>
                        <p class="text-xs text-slate-500">Unlimited users & roles</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ============================
     PRICING
============================= -->
<section id="pricing" class="py-24 bg-[#060b18]">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-16">
            <h2 class="reveal text-4xl lg:text-5xl font-black text-white mb-4">
                Simple, <span class="gradient-text">honest pricing</span>
            </h2>
            <p class="reveal text-slate-400 text-lg">No hidden fees. Cancel anytime. Scale as you grow.</p>
            <p class="reveal text-sm text-emerald-400 mt-2 flex items-center justify-center gap-2">
                <i class="fas fa-gift"></i> Every paid plan starts with a 14-day free trial
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-6 items-start">
            <!-- Starter -->
            <div class="reveal glass rounded-3xl p-8 card-hover">
                <div class="mb-6">
                    <p class="text-sm font-semibold text-slate-400 uppercase tracking-widest mb-1">Starter</p>
                    <div class="flex items-end gap-1 mb-1">
                        <span class="text-4xl font-black text-white">Free</span>
                    </div>
                    <p class="text-xs text-slate-500">Perfect for a single store</p>
                </div>
                <div class="section-divider mb-6"></div>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-center gap-3 text-sm text-slate-300"><i class="fas fa-check text-emerald-400 w-4"></i> 1 Outlet</li>
                    <li class="flex items-center gap-3 text-sm text-slate-300"><i class="fas fa-check text-emerald-400 w-4"></i> Up to 3 Users</li>
                    <li class="flex items-center gap-3 text-sm text-slate-300"><i class="fas fa-check text-emerald-400 w-4"></i> POS Module</li>
                    <li class="flex items-center gap-3 text-sm text-slate-300"><i class="fas fa-check text-emerald-400 w-4"></i> Basic Inventory</li>
                    <li class="flex items-center gap-3 text-sm text-slate-400"><i class="fas fa-times text-slate-600 w-4"></i> Finance Module</li>
                    <li class="flex items-center gap-3 text-sm text-slate-400"><i class="fas fa-times text-slate-600 w-4"></i> Advanced Reports</li>
                </ul>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="block text-center py-3 rounded-2xl btn-secondary text-white text-sm font-semibold">Get Started Free</a>
                @endif
            </div>

            <!-- Professional (popular) -->
            <div class="reveal glass pricing-popular rounded-3xl p-8 card-hover relative">
                <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                    <span class="bg-gradient-to-r from-indigo-500 to-violet-600 text-white text-xs font-bold px-5 py-2 rounded-full shadow-lg shadow-indigo-500/30">MOST POPULAR</span>
                </div>
                <div class="mb-6 mt-2">
                    <p class="text-sm font-semibold text-indigo-300 uppercase tracking-widest mb-1">Professional</p>
                    <div class="flex items-end gap-1 mb-1">
                        <span class="text-4xl font-black text-white">Rp 299K</span>
                        <span class="text-slate-400 text-sm mb-1">/mo</span>
                    </div>
                    <p class="text-xs text-slate-500">For growing businesses</p>
                    <div class="flex items-center gap-1.5 mt-2">
                        <i class="fas fa-gift text-emerald-400 text-xs"></i>
                        <span class="text-xs text-emerald-400 font-medium">14-day free trial included</span>
                    </div>
                </div>
                <div class="section-divider mb-6"></div>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-center gap-3 text-sm text-slate-300"><i class="fas fa-check text-emerald-400 w-4"></i> Up to 5 Outlets</li>
                    <li class="flex items-center gap-3 text-sm text-slate-300"><i class="fas fa-check text-emerald-400 w-4"></i> Unlimited Users</li>
                    <li class="flex items-center gap-3 text-sm text-slate-300"><i class="fas fa-check text-emerald-400 w-4"></i> Full POS Module</li>
                    <li class="flex items-center gap-3 text-sm text-slate-300"><i class="fas fa-check text-emerald-400 w-4"></i> Full Inventory</li>
                    <li class="flex items-center gap-3 text-sm text-slate-300"><i class="fas fa-check text-emerald-400 w-4"></i> Finance Module</li>
                    <li class="flex items-center gap-3 text-sm text-slate-300"><i class="fas fa-check text-emerald-400 w-4"></i> Advanced Reports</li>
                </ul>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="block text-center py-3 rounded-2xl btn-primary text-white text-sm font-semibold">Start 14-Day Trial</a>
                @endif
            </div>

            <!-- Enterprise -->
            <div class="reveal glass rounded-3xl p-8 card-hover">
                <div class="mb-6">
                    <p class="text-sm font-semibold text-slate-400 uppercase tracking-widest mb-1">Enterprise</p>
                    <div class="flex items-end gap-1 mb-1">
                        <span class="text-4xl font-black text-white">Custom</span>
                    </div>
                    <p class="text-xs text-slate-500">For enterprise & franchises</p>
                </div>
                <div class="section-divider mb-6"></div>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-center gap-3 text-sm text-slate-300"><i class="fas fa-check text-emerald-400 w-4"></i> Unlimited Outlets</li>
                    <li class="flex items-center gap-3 text-sm text-slate-300"><i class="fas fa-check text-emerald-400 w-4"></i> Unlimited Users</li>
                    <li class="flex items-center gap-3 text-sm text-slate-300"><i class="fas fa-check text-emerald-400 w-4"></i> All Modules</li>
                    <li class="flex items-center gap-3 text-sm text-slate-300"><i class="fas fa-check text-emerald-400 w-4"></i> Multi-Tenant Setup</li>
                    <li class="flex items-center gap-3 text-sm text-slate-300"><i class="fas fa-check text-emerald-400 w-4"></i> Dedicated Support</li>
                    <li class="flex items-center gap-3 text-sm text-slate-300"><i class="fas fa-check text-emerald-400 w-4"></i> Custom Integration</li>
                </ul>
                <a href="mailto:hello@posnerp.com" class="block text-center py-3 rounded-2xl btn-secondary text-white text-sm font-semibold">Contact Sales</a>
            </div>
        </div>
    </div>
</section>


<!-- ============================
     FINAL CTA
============================= -->
<section class="py-24 bg-[#070c1b]">
    <div class="max-w-4xl mx-auto px-6 text-center">
        <div class="relative glass-dark rounded-3xl p-14 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/10 via-violet-600/8 to-emerald-600/6 rounded-3xl"></div>
            <div class="absolute -top-20 -right-20 w-64 h-64 bg-indigo-500/15 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl"></div>

            <div class="relative z-10">
                <h2 class="text-4xl lg:text-6xl font-black text-white leading-tight mb-6">
                    Ready to transform<br><span class="gradient-text">your business?</span>
                </h2>
                <p class="text-slate-400 text-lg mb-10 max-w-xl mx-auto">
                    Join thousands of merchants already using {{ config('app.name') }}. Free to start, no credit card required.
                </p>
                <div class="flex flex-wrap gap-4 justify-center">
                    @auth
                        <a href="{{ url('/admin/dashboard') }}" class="btn-primary text-white font-bold px-10 py-4 rounded-2xl text-lg flex items-center gap-2">
                            <i class="fas fa-tachometer-alt"></i> Open Dashboard
                        </a>
                    @else
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn-primary text-white font-bold px-10 py-4 rounded-2xl text-lg flex items-center gap-2">
                                <i class="fas fa-rocket"></i> Start For Free
                            </a>
                        @endif
                        <a href="{{ route('login') }}" class="btn-secondary text-slate-300 font-medium px-10 py-4 rounded-2xl text-lg flex items-center gap-2">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ============================
     FOOTER
============================= -->
<footer class="bg-[#040810] border-t border-white/5 py-16">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-10 mb-12">
            <!-- Brand -->
            <div class="col-span-2 md:col-span-1">
                <a href="/" class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                        <i class="fas fa-cash-register text-white text-sm"></i>
                    </div>
                    <span class="font-bold text-lg text-white">{{ config('app.name', 'POS ERP') }}</span>
                </a>
                <p class="text-sm text-slate-500 leading-relaxed mb-5">The complete ERP & POS solution for modern businesses, from single stores to nationwide franchises.</p>
                <div class="flex gap-3">
                    <a href="#" class="w-9 h-9 rounded-xl glass flex items-center justify-center text-slate-400 hover:text-white hover:bg-indigo-500/20 transition-all">
                        <i class="fab fa-twitter text-sm"></i>
                    </a>
                    <a href="#" class="w-9 h-9 rounded-xl glass flex items-center justify-center text-slate-400 hover:text-white hover:bg-indigo-500/20 transition-all">
                        <i class="fab fa-instagram text-sm"></i>
                    </a>
                    <a href="#" class="w-9 h-9 rounded-xl glass flex items-center justify-center text-slate-400 hover:text-white hover:bg-indigo-500/20 transition-all">
                        <i class="fab fa-linkedin text-sm"></i>
                    </a>
                </div>
            </div>

            <!-- Product -->
            <div>
                <p class="text-sm font-semibold text-white mb-4 uppercase tracking-widest">Product</p>
                <ul class="space-y-2.5">
                    <li><a href="#features" class="text-sm text-slate-500 hover:text-white transition-colors">Features</a></li>
                    <li><a href="#modules" class="text-sm text-slate-500 hover:text-white transition-colors">Modules</a></li>
                    <li><a href="#pricing" class="text-sm text-slate-500 hover:text-white transition-colors">Pricing</a></li>
                    <li><a href="#download" class="text-sm text-slate-500 hover:text-white transition-colors">Download App</a></li>
                </ul>
            </div>

            <!-- Company -->
            <div>
                <p class="text-sm font-semibold text-white mb-4 uppercase tracking-widest">Company</p>
                <ul class="space-y-2.5">
                    <li><a href="#" class="text-sm text-slate-500 hover:text-white transition-colors">About Us</a></li>
                    <li><a href="#" class="text-sm text-slate-500 hover:text-white transition-colors">Careers</a></li>
                    <li><a href="#" class="text-sm text-slate-500 hover:text-white transition-colors">Blog</a></li>
                    <li><a href="#" class="text-sm text-slate-500 hover:text-white transition-colors">Contact</a></li>
                </ul>
            </div>

            <!-- Legal -->
            <div>
                <p class="text-sm font-semibold text-white mb-4 uppercase tracking-widest">Support</p>
                <ul class="space-y-2.5">
                    <li><a href="#" class="text-sm text-slate-500 hover:text-white transition-colors">Documentation</a></li>
                    <li><a href="#" class="text-sm text-slate-500 hover:text-white transition-colors">Help Center</a></li>
                    <li><a href="#" class="text-sm text-slate-500 hover:text-white transition-colors">Privacy Policy</a></li>
                    <li><a href="#" class="text-sm text-slate-500 hover:text-white transition-colors">Terms of Service</a></li>
                </ul>
            </div>
        </div>

        <div class="section-divider mb-8"></div>

        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-sm text-slate-600">&copy; {{ date('Y') }} {{ config('app.name', 'POS ERP') }}. All rights reserved.</p>
            <p class="text-sm text-slate-700 flex items-center gap-2">
                Built with <i class="fas fa-heart text-red-500 text-xs"></i> on <a href="https://laravel.com" target="_blank" class="text-indigo-400 hover:text-indigo-300">Laravel</a>
            </p>
        </div>
    </div>
</footer>


<script>
    // Sticky navbar
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('navbar-scrolled', window.scrollY > 50);
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', e => {
            const target = document.querySelector(a.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                // Close mobile menu
                document.getElementById('mobile-menu').classList.remove('open');
            }
        });
    });

    // Scroll reveal
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.classList.add('visible');
                }, (entry.target.dataset.delay || 0));
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.reveal, .reveal-left, .reveal-right').forEach((el, i) => {
        el.dataset.delay = (i % 4) * 80;
        revealObserver.observe(el);
    });

    // Progress bar animation on scroll
    const progressObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'none';
                entry.target.offsetHeight; // reflow
                entry.target.style.animation = '';
                progressObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.3 });

    document.querySelectorAll('.progress-bar').forEach(el => progressObserver.observe(el));
</script>

</body>
</html>
