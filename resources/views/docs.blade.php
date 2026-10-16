<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Complete usage guide and documentation for {{ config('app.name', 'POS ERP') }} — learn how to set up and manage your business.">
    <title>Documentation — {{ config('app.name', 'POS ERP') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        * { font-family: 'Inter', sans-serif; scroll-behavior: smooth; }
        body { background-color: #060b18; color: #cbd5e1; }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #060b18; }
        ::-webkit-scrollbar-thumb { background: rgba(99,102,241,0.45); border-radius: 3px; }

        /* ── Glass ── */
        .glass {
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.08);
        }
        .glass-dark {
            background: rgba(13,17,35,0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(99,102,241,0.18);
        }

        /* ── Sidebar ── */
        #sidebar {
            width: 280px;
            min-width: 280px;
            height: 100vh;
            position: sticky;
            top: 0;
            overflow-y: auto;
            background: rgba(7,11,22,0.96);
            border-right: 1px solid rgba(255,255,255,0.06);
        }
        #sidebar::-webkit-scrollbar { width: 3px; }
        #sidebar::-webkit-scrollbar-thumb { background: rgba(99,102,241,0.3); }

        .nav-section-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #475569;
            padding: 16px 20px 6px;
        }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 20px;
            font-size: 13.5px;
            color: #94a3b8;
            border-radius: 0;
            transition: all 0.2s ease;
            border-left: 2px solid transparent;
            text-decoration: none;
            cursor: pointer;
        }
        .nav-link:hover { color: #e2e8f0; background: rgba(255,255,255,0.04); border-left-color: rgba(99,102,241,0.4); }
        .nav-link.active { color: #818cf8; background: rgba(99,102,241,0.08); border-left-color: #6366f1; font-weight: 600; }
        .nav-link i { width: 16px; text-align: center; font-size: 12px; opacity: 0.75; }

        /* ── Content ── */
        .doc-content { max-width: 820px; }

        /* ── Section headings ── */
        .doc-section { scroll-margin-top: 24px; }

        h1.page-title {
            font-size: 2.4rem;
            font-weight: 800;
            line-height: 1.2;
            color: #f1f5f9;
        }
        h2.section-title {
            font-size: 1.55rem;
            font-weight: 700;
            color: #f1f5f9;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
            margin-bottom: 1.25rem;
        }
        h3.subsection-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #e2e8f0;
            margin-bottom: 0.6rem;
        }

        p.doc-p { font-size: 0.9375rem; line-height: 1.8; color: #94a3b8; margin-bottom: 1rem; }

        /* ── Gradient text ── */
        .gradient-text {
            background: linear-gradient(135deg, #818cf8 0%, #6366f1 30%, #10b981 70%, #34d399 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ── Badge ── */
        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 100px;
            font-size: 11px; font-weight: 600;
        }
        .badge-indigo { background: rgba(99,102,241,0.15); color: #818cf8; border: 1px solid rgba(99,102,241,0.25); }
        .badge-green  { background: rgba(16,185,129,0.12); color: #34d399; border: 1px solid rgba(16,185,129,0.25); }
        .badge-amber  { background: rgba(245,158,11,0.12); color: #fbbf24; border: 1px solid rgba(245,158,11,0.25); }
        .badge-red    { background: rgba(239,68,68,0.12);  color: #f87171; border: 1px solid rgba(239,68,68,0.25); }
        .badge-violet { background: rgba(139,92,246,0.12); color: #a78bfa; border: 1px solid rgba(139,92,246,0.25); }

        /* ── Step card ── */
        .step-card {
            display: flex; gap: 14px; align-items: flex-start;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 14px; padding: 16px 18px;
            transition: border-color 0.25s ease;
        }
        .step-card:hover { border-color: rgba(99,102,241,0.3); }
        .step-num {
            width: 32px; height: 32px; min-width: 32px;
            border-radius: 10px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700; color: white;
        }

        /* ── Info / warning / tip callout ── */
        .callout {
            border-radius: 12px; padding: 14px 16px;
            display: flex; gap: 12px; align-items: flex-start;
            margin-bottom: 1.2rem; font-size: 0.875rem; line-height: 1.65;
        }
        .callout-info    { background: rgba(99,102,241,0.08);  border: 1px solid rgba(99,102,241,0.2);  color: #a5b4fc; }
        .callout-tip     { background: rgba(16,185,129,0.08);  border: 1px solid rgba(16,185,129,0.2);  color: #6ee7b7; }
        .callout-warning { background: rgba(245,158,11,0.08);  border: 1px solid rgba(245,158,11,0.2);  color: #fde68a; }
        .callout-danger  { background: rgba(239,68,68,0.08);   border: 1px solid rgba(239,68,68,0.2);   color: #fca5a5; }
        .callout-icon { font-size: 15px; margin-top: 1px; flex-shrink: 0; }

        /* ── Feature table ── */
        .doc-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        .doc-table th {
            text-align: left; padding: 10px 14px;
            font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase;
            color: #475569; background: rgba(255,255,255,0.03);
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        .doc-table td {
            padding: 11px 14px; border-bottom: 1px solid rgba(255,255,255,0.05);
            color: #94a3b8; vertical-align: top;
        }
        .doc-table tr:hover td { background: rgba(255,255,255,0.02); }

        /* ── Code inline ── */
        code {
            font-family: 'JetBrains Mono', monospace;
            background: rgba(99,102,241,0.12);
            color: #a5b4fc;
            padding: 2px 7px; border-radius: 5px;
            font-size: 83%;
        }

        /* ── Module section box ── */
        .module-box {
            border-radius: 18px; overflow: hidden;
            border: 1px solid rgba(255,255,255,0.07);
            margin-bottom: 2.5rem;
        }
        .module-box-header {
            display: flex; align-items: center; gap: 14px;
            padding: 18px 22px;
            background: rgba(255,255,255,0.03);
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        .module-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
        }
        .module-box-body { padding: 22px; }

        /* ── Shortcut pill ── */
        .kbd {
            display: inline-flex; align-items: center;
            font-family: 'JetBrains Mono', monospace;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 6px; padding: 2px 8px;
            font-size: 11.5px; color: #e2e8f0;
        }

        /* ── TOC progress bar at top ── */
        #read-progress {
            position: fixed; top: 0; left: 0; height: 3px; z-index: 100;
            background: linear-gradient(90deg, #6366f1, #10b981);
            transition: width 0.1s linear;
            width: 0%;
        }

        /* ── Mobile sidebar overlay ── */
        #sidebar-overlay { display: none; }
        @media (max-width: 1024px) {
            #sidebar {
                position: fixed; top: 0; left: -300px; z-index: 60;
                transition: left 0.3s ease; height: 100vh;
                width: 280px; min-width: 280px;
            }
            #sidebar.open { left: 0; }
            #sidebar-overlay.active { display: block; }
        }

        /* ── Animations ── */
        @keyframes fadeIn { from{opacity:0;transform:translateY(12px);} to{opacity:1;transform:translateY(0);} }
        .doc-section { animation: fadeIn 0.4s ease both; }

        /* ── Search highlight ── */
        mark { background: rgba(99,102,241,0.3); color: #c7d2fe; border-radius: 3px; padding: 0 2px; }

        /* ── Checklist ── */
        .checklist { list-style: none; padding: 0; margin: 0 0 1rem; }
        .checklist li {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 6px 0; font-size: 0.9rem; color: #94a3b8;
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        .checklist li:last-child { border-bottom: none; }
        .checklist li i { color: #34d399; font-size: 13px; margin-top: 3px; flex-shrink: 0; }

        /* On-page nav highlight pulse */
        @keyframes navPulse { 0%{background:rgba(99,102,241,0.18);} 100%{background:rgba(99,102,241,0.08);} }
        .nav-link.active { animation: navPulse 0.5s ease forwards; }
    </style>
</head>
<body>

<!-- Reading progress bar -->
<div id="read-progress"></div>

<!-- Mobile sidebar overlay -->
<div id="sidebar-overlay" onclick="closeSidebar()" class="fixed inset-0 bg-black/60 z-50 backdrop-blur-sm"></div>

<!-- ================================================================
     TOP NAVBAR (mobile)
================================================================ -->
<header class="lg:hidden fixed top-0 left-0 right-0 z-40 flex items-center justify-between px-4 py-3 glass-dark border-b border-white/6">
    <button onclick="openSidebar()" class="text-slate-400 hover:text-white p-2 -ml-2">
        <i class="fas fa-bars text-lg"></i>
    </button>
    <a href="/" class="flex items-center gap-2">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center">
            <i class="fas fa-cash-register text-white text-xs"></i>
        </div>
        <span class="font-bold text-sm text-white">{{ config('app.name', 'POS ERP') }}</span>
    </a>
    <div class="w-8"></div><!-- spacer -->
</header>

<!-- ================================================================
     LAYOUT WRAPPER
================================================================ -->
<div class="flex min-h-screen pt-[52px] lg:pt-0">

    <!-- ============================================================
         SIDEBAR
    ============================================================ -->
    <aside id="sidebar" class="flex-shrink-0">
        <!-- Logo -->
        <div class="flex items-center gap-3 px-5 py-5 border-b border-white/6">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                <i class="fas fa-cash-register text-white text-sm"></i>
            </div>
            <div>
                <div class="font-bold text-sm text-white leading-tight">{{ config('app.name', 'POS ERP') }}</div>
                <div class="text-xs text-slate-500">Documentation</div>
            </div>
        </div>

        <!-- Search box -->
        <div class="px-4 py-3 border-b border-white/6">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                <input id="doc-search" type="text" placeholder="Search docs…"
                    class="w-full bg-white/5 border border-white/8 rounded-lg pl-8 pr-3 py-2 text-xs text-slate-300 placeholder-slate-600 focus:outline-none focus:border-indigo-500/50 focus:bg-white/7 transition-all">
            </div>
        </div>

        <!-- Navigation -->
        <nav class="pb-8" id="sidebar-nav">
            <div class="nav-section-label">Overview</div>
            <a href="#getting-started" class="nav-link active" data-section="getting-started">
                <i class="fas fa-rocket"></i> Getting Started
            </a>
            <a href="#quick-setup" class="nav-link" data-section="quick-setup">
                <i class="fas fa-sliders-h"></i> Quick Setup
            </a>
            <a href="#dashboard" class="nav-link" data-section="dashboard">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>

            <div class="nav-section-label">Core Modules</div>
            <a href="#pos" class="nav-link" data-section="pos">
                <i class="fas fa-cash-register"></i> Point of Sale (POS)
            </a>
            <a href="#products" class="nav-link" data-section="products">
                <i class="fas fa-box"></i> Products & Categories
            </a>
            <a href="#inventory" class="nav-link" data-section="inventory">
                <i class="fas fa-warehouse"></i> Inventory
            </a>
            <a href="#orders" class="nav-link" data-section="orders">
                <i class="fas fa-receipt"></i> Orders & Payments
            </a>
            <a href="#customers" class="nav-link" data-section="customers">
                <i class="fas fa-users"></i> Customers
            </a>
            <a href="#outlets" class="nav-link" data-section="outlets">
                <i class="fas fa-store"></i> Outlets
            </a>

            <div class="nav-section-label">Analytics</div>
            <a href="#reports" class="nav-link" data-section="reports">
                <i class="fas fa-chart-bar"></i> Reports
            </a>

            <div class="nav-section-label">Administration</div>
            <a href="#settings" class="nav-link" data-section="settings">
                <i class="fas fa-cog"></i> Settings
            </a>
            <a href="#users-roles" class="nav-link" data-section="users-roles">
                <i class="fas fa-user-shield"></i> Users & Roles
            </a>
            <a href="#tenants" class="nav-link" data-section="tenants">
                <i class="fas fa-building"></i> Tenants
            </a>
            <a href="#activity-logs" class="nav-link" data-section="activity-logs">
                <i class="fas fa-history"></i> Activity Logs
            </a>

            <div class="nav-section-label">Reference</div>
            <a href="#permissions-ref" class="nav-link" data-section="permissions-ref">
                <i class="fas fa-key"></i> Permissions Reference
            </a>
            <a href="#faq" class="nav-link" data-section="faq">
                <i class="fas fa-question-circle"></i> FAQ
            </a>
        </nav>
    </aside>

    <!-- ============================================================
         MAIN CONTENT
    ============================================================ -->
    <main class="flex-1 min-w-0 px-6 py-10 lg:px-12 lg:py-14 overflow-x-hidden" id="doc-main">
        <div class="doc-content mx-auto">

            <!-- ── Back link ── -->
            <a href="/" class="inline-flex items-center gap-2 text-slate-500 hover:text-indigo-400 text-sm mb-8 transition-colors group">
                <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform text-xs"></i>
                Back to Home
            </a>

            <!-- ── Hero ── -->
            <div class="mb-14">
                <div class="inline-flex items-center gap-2 badge badge-indigo mb-4">
                    <i class="fas fa-book-open"></i> Full Documentation
                </div>
                <h1 class="page-title mb-4">
                    <span class="gradient-text">Usage Guide</span><br>
                    & Documentation
                </h1>
                <p class="doc-p text-lg max-w-lg">
                    Everything you need to set up, manage and grow your business with <strong class="text-white">{{ config('app.name', 'POS ERP') }}</strong>. From first login to advanced reporting.
                </p>
                <div class="flex flex-wrap gap-3 mt-5">
                    <span class="badge badge-green"><i class="fas fa-circle text-[8px]"></i> Up to date</span>
                    <span class="badge badge-indigo"><i class="fas fa-layer-group"></i> All modules covered</span>
                    <span class="badge badge-violet"><i class="fas fa-mobile-alt"></i> POS + Web</span>
                </div>
            </div>

            <!-- ================================================================
                 1. GETTING STARTED
            ================================================================ -->
            <section id="getting-started" class="doc-section mb-16">
                <h2 class="section-title">
                    <i class="fas fa-rocket text-indigo-400 mr-2 text-xl"></i>
                    Getting Started
                </h2>
                <p class="doc-p">
                    Welcome to <strong class="text-white">{{ config('app.name', 'POS ERP') }}</strong> — a multi-tenant ERP & Point of Sale platform. This guide walks you through every feature so you can manage sales, inventory, customers, and finance from one place.
                </p>

                <div class="callout callout-info">
                    <i class="fas fa-info-circle callout-icon"></i>
                    <span>This system supports <strong>multiple tenants</strong>. Each tenant (business) has its own isolated data, users, and outlets. A <strong>super_admin</strong> manages all tenants from a central panel.</span>
                </div>

                <h3 class="subsection-title mt-6">Prerequisites</h3>
                <ul class="checklist">
                    <li><i class="fas fa-check-circle"></i> A valid account created by your administrator (or self-registered during trial)</li>
                    <li><i class="fas fa-check-circle"></i> A modern web browser (Chrome, Edge, Firefox, Safari)</li>
                    <li><i class="fas fa-check-circle"></i> Internet connection (or LAN for on-premise installations)</li>
                    <li><i class="fas fa-check-circle"></i> At least one <strong class="text-slate-300">Outlet</strong> configured before processing sales</li>
                </ul>

                <h3 class="subsection-title mt-6">First Login</h3>
                <div class="space-y-3">
                    <div class="step-card">
                        <div class="step-num">1</div>
                        <div>
                            <div class="font-semibold text-slate-200 text-sm mb-1">Open the Login Page</div>
                            <p class="text-slate-400 text-sm leading-relaxed">Go to <code>/login</code> in your browser. Enter your email address and password provided by your administrator.</p>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-num">2</div>
                        <div>
                            <div class="font-semibold text-slate-200 text-sm mb-1">Choose or Activate Your Plan</div>
                            <p class="text-slate-400 text-sm leading-relaxed">New accounts start with a free trial. If your trial has expired you will be redirected to <code>/subscription/plans</code> to select a plan before accessing the dashboard.</p>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-num">3</div>
                        <div>
                            <div class="font-semibold text-slate-200 text-sm mb-1">Land on the Dashboard</div>
                            <p class="text-slate-400 text-sm leading-relaxed">After successful authentication you are redirected to <code>/admin/dashboard</code> — your central command centre.</p>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-num">4</div>
                        <div>
                            <div class="font-semibold text-slate-200 text-sm mb-1">Forgot Password?</div>
                            <p class="text-slate-400 text-sm leading-relaxed">Click <em>Forgot password</em> on the login page. Enter your registered email and follow the reset link sent to your inbox.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ================================================================
                 2. QUICK SETUP
            ================================================================ -->
            <section id="quick-setup" class="doc-section mb-16">
                <h2 class="section-title">
                    <i class="fas fa-sliders-h text-violet-400 mr-2 text-xl"></i>
                    Quick Setup Checklist
                </h2>
                <p class="doc-p">Follow these steps to get your store operational as fast as possible.</p>

                <div class="glass rounded-2xl overflow-hidden mb-6">
                    <table class="doc-table w-full">
                        <thead>
                            <tr>
                                <th style="width:36px">#</th>
                                <th>Task</th>
                                <th>Where</th>
                                <th>Required</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td class="font-mono text-indigo-400">01</td><td class="text-slate-200 font-medium">Configure business settings</td><td><code>Settings → General</code></td><td><span class="badge badge-red">Required</span></td></tr>
                            <tr><td class="font-mono text-indigo-400">02</td><td class="text-slate-200 font-medium">Set currency & tax</td><td><code>Settings → Currency / Tax</code></td><td><span class="badge badge-red">Required</span></td></tr>
                            <tr><td class="font-mono text-indigo-400">03</td><td class="text-slate-200 font-medium">Create at least one Outlet</td><td><code>Outlets → New</code></td><td><span class="badge badge-red">Required</span></td></tr>
                            <tr><td class="font-mono text-indigo-400">04</td><td class="text-slate-200 font-medium">Add product categories</td><td><code>Categories → New</code></td><td><span class="badge badge-amber">Recommended</span></td></tr>
                            <tr><td class="font-mono text-indigo-400">05</td><td class="text-slate-200 font-medium">Add products with prices & stock</td><td><code>Products → New</code></td><td><span class="badge badge-red">Required</span></td></tr>
                            <tr><td class="font-mono text-indigo-400">06</td><td class="text-slate-200 font-medium">Create staff accounts & assign roles</td><td><code>Users → New, Roles</code></td><td><span class="badge badge-amber">Recommended</span></td></tr>
                            <tr><td class="font-mono text-indigo-400">07</td><td class="text-slate-200 font-medium">Add customers (optional loyalty)</td><td><code>Customers → New</code></td><td><span class="badge badge-green">Optional</span></td></tr>
                            <tr><td class="font-mono text-indigo-400">08</td><td class="text-slate-200 font-medium">Open POS and make a test sale</td><td><code>POS</code></td><td><span class="badge badge-amber">Recommended</span></td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="callout callout-tip">
                    <i class="fas fa-lightbulb callout-icon"></i>
                    <span>Complete steps 1–5 before inviting staff. Staff members will see accurate product lists and prices from day one.</span>
                </div>
            </section>

            <!-- ================================================================
                 3. DASHBOARD
            ================================================================ -->
            <section id="dashboard" class="doc-section mb-16">
                <h2 class="section-title">
                    <i class="fas fa-tachometer-alt text-emerald-400 mr-2 text-xl"></i>
                    Dashboard
                </h2>
                <p class="doc-p">The dashboard gives you an instant snapshot of your business health. Navigate to <code>/admin/dashboard</code> after login.</p>

                <div class="grid sm:grid-cols-2 gap-4 mb-6">
                    <div class="glass rounded-2xl p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-xl bg-indigo-500/15 flex items-center justify-center">
                                <i class="fas fa-dollar-sign text-indigo-400 text-sm"></i>
                            </div>
                            <span class="font-semibold text-slate-200 text-sm">Revenue Summary</span>
                        </div>
                        <p class="text-slate-400 text-sm leading-relaxed">Today's sales, weekly trend, and monthly total visible at a glance with coloured change indicators.</p>
                    </div>
                    <div class="glass rounded-2xl p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-500/15 flex items-center justify-center">
                                <i class="fas fa-receipt text-emerald-400 text-sm"></i>
                            </div>
                            <span class="font-semibold text-slate-200 text-sm">Recent Orders</span>
                        </div>
                        <p class="text-slate-400 text-sm leading-relaxed">Latest transactions with order status badges (pending, paid, refunded) and quick-action links.</p>
                    </div>
                    <div class="glass rounded-2xl p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-xl bg-amber-500/15 flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-amber-400 text-sm"></i>
                            </div>
                            <span class="font-semibold text-slate-200 text-sm">Low Stock Alerts</span>
                        </div>
                        <p class="text-slate-400 text-sm leading-relaxed">Products that have fallen below their minimum stock threshold are highlighted here for immediate attention.</p>
                    </div>
                    <div class="glass rounded-2xl p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-xl bg-violet-500/15 flex items-center justify-center">
                                <i class="fas fa-chart-line text-violet-400 text-sm"></i>
                            </div>
                            <span class="font-semibold text-slate-200 text-sm">Sales Chart</span>
                        </div>
                        <p class="text-slate-400 text-sm leading-relaxed">Interactive line chart showing the last 30 days of sales volume to identify trends quickly.</p>
                    </div>
                </div>
            </section>

            <!-- ================================================================
                 4. POS
            ================================================================ -->
            <section id="pos" class="doc-section mb-16">
                <h2 class="section-title">
                    <i class="fas fa-cash-register text-indigo-400 mr-2 text-xl"></i>
                    Point of Sale (POS)
                </h2>
                <p class="doc-p">The POS screen (<code>/admin/pos</code>) is optimised for fast, touch-friendly checkout. Cashiers spend most of their time here.</p>

                <div class="module-box">
                    <div class="module-box-header">
                        <div class="module-icon bg-indigo-500/15">
                            <i class="fas fa-shopping-cart text-indigo-400"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-slate-200 text-sm">Processing a Sale</div>
                            <div class="text-slate-500 text-xs mt-0.5">Step-by-step checkout workflow</div>
                        </div>
                    </div>
                    <div class="module-box-body space-y-3">
                        <div class="step-card">
                            <div class="step-num">1</div>
                            <div>
                                <div class="font-semibold text-slate-200 text-sm mb-1">Search or Browse Products</div>
                                <p class="text-slate-400 text-sm leading-relaxed">Use the search bar to find a product by name or SKU, or browse by category tabs at the top. Click a product card to add it to the cart.</p>
                            </div>
                        </div>
                        <div class="step-card">
                            <div class="step-num">2</div>
                            <div>
                                <div class="font-semibold text-slate-200 text-sm mb-1">Adjust Quantities & Discounts</div>
                                <p class="text-slate-400 text-sm leading-relaxed">Tap <kbd class="kbd">+</kbd> / <kbd class="kbd">−</kbd> on a cart item to change quantity. Apply a line discount or an order-level discount using the discount field.</p>
                            </div>
                        </div>
                        <div class="step-card">
                            <div class="step-num">3</div>
                            <div>
                                <div class="font-semibold text-slate-200 text-sm mb-1">Select Customer (Optional)</div>
                                <p class="text-slate-400 text-sm leading-relaxed">Search and attach a loyalty customer to the order so points are earned automatically.</p>
                            </div>
                        </div>
                        <div class="step-card">
                            <div class="step-num">4</div>
                            <div>
                                <div class="font-semibold text-slate-200 text-sm mb-1">Choose Payment Method</div>
                                <p class="text-slate-400 text-sm leading-relaxed">Select <strong class="text-slate-200">Cash</strong>, <strong class="text-slate-200">Card</strong>, or <strong class="text-slate-200">QRIS / Digital Wallet</strong>. For cash, enter the amount tendered and change is calculated instantly.</p>
                            </div>
                        </div>
                        <div class="step-card">
                            <div class="step-num">5</div>
                            <div>
                                <div class="font-semibold text-slate-200 text-sm mb-1">Complete & Print Receipt</div>
                                <p class="text-slate-400 text-sm leading-relaxed">Click <strong class="text-slate-200">Charge</strong> to finalise the order. A thermal-friendly receipt is available via <code>Orders → Print</code>.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="callout callout-warning">
                    <i class="fas fa-exclamation-triangle callout-icon"></i>
                    <span>The POS requires at least <strong>one outlet</strong> to be active. If no outlet is configured, the cashier will see an error. Go to <code>Outlets → New</code> first.</span>
                </div>
            </section>

            <!-- ================================================================
                 5. PRODUCTS & CATEGORIES
            ================================================================ -->
            <section id="products" class="doc-section mb-16">
                <h2 class="section-title">
                    <i class="fas fa-box text-amber-400 mr-2 text-xl"></i>
                    Products & Categories
                </h2>
                <p class="doc-p">Manage your entire product catalogue including prices, stock levels, and categories from <code>/admin/products</code> and <code>/admin/categories</code>.</p>

                <div class="module-box">
                    <div class="module-box-header">
                        <div class="module-icon bg-amber-500/15">
                            <i class="fas fa-tag text-amber-400"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-slate-200 text-sm">Creating a Product</div>
                        </div>
                    </div>
                    <div class="module-box-body">
                        <table class="doc-table">
                            <thead>
                                <tr><th>Field</th><th>Description</th><th>Required</th></tr>
                            </thead>
                            <tbody>
                                <tr><td class="text-slate-300 font-medium">Name</td><td>Display name shown in POS and reports</td><td><span class="badge badge-red">Yes</span></td></tr>
                                <tr><td class="text-slate-300 font-medium">SKU</td><td>Unique stock-keeping unit code (auto-generated if blank)</td><td><span class="badge badge-green">No</span></td></tr>
                                <tr><td class="text-slate-300 font-medium">Category</td><td>Links product to a browsable category in POS</td><td><span class="badge badge-amber">Recommended</span></td></tr>
                                <tr><td class="text-slate-300 font-medium">Price</td><td>Selling price (exclusive of tax unless tax-inclusive is enabled)</td><td><span class="badge badge-red">Yes</span></td></tr>
                                <tr><td class="text-slate-300 font-medium">Cost Price</td><td>Used for profit margin calculations in reports</td><td><span class="badge badge-green">No</span></td></tr>
                                <tr><td class="text-slate-300 font-medium">Stock Qty</td><td>Initial stock level at the assigned outlet</td><td><span class="badge badge-red">Yes</span></td></tr>
                                <tr><td class="text-slate-300 font-medium">Min Stock</td><td>Threshold that triggers low-stock alert on dashboard</td><td><span class="badge badge-amber">Recommended</span></td></tr>
                                <tr><td class="text-slate-300 font-medium">Image</td><td>Product photo displayed on the POS card grid</td><td><span class="badge badge-green">No</span></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <h3 class="subsection-title mt-6">Stock Adjustment</h3>
                <p class="doc-p">Go to <code>Products → &lt;product&gt; → Adjust Stock</code> to add or remove stock manually (e.g. receiving a delivery or writing off damaged goods). Every adjustment is logged in the stock movement history.</p>

                <h3 class="subsection-title">Stock Transfer</h3>
                <p class="doc-p">Use <code>Products → Transfer Stock</code> to move quantity from one outlet to another. Both source and destination movements are recorded automatically.</p>

                <div class="callout callout-tip">
                    <i class="fas fa-lightbulb callout-icon"></i>
                    <span>Create categories <em>before</em> adding products. A well-organised category tree makes the POS faster for cashiers and easier to filter in reports.</span>
                </div>
            </section>

            <!-- ================================================================
                 6. INVENTORY
            ================================================================ -->
            <section id="inventory" class="doc-section mb-16">
                <h2 class="section-title">
                    <i class="fas fa-warehouse text-teal-400 mr-2 text-xl"></i>
                    Inventory Management
                </h2>
                <p class="doc-p">Track real-time stock levels and every movement across all outlets from the Inventory section.</p>

                <div class="grid sm:grid-cols-2 gap-4 mb-6">
                    <div class="glass rounded-2xl p-5">
                        <div class="font-semibold text-slate-200 text-sm mb-2 flex items-center gap-2">
                            <i class="fas fa-layer-group text-teal-400"></i> Stock Overview
                        </div>
                        <p class="text-slate-400 text-sm leading-relaxed">Navigate to <code>Inventory → Stock</code> to see current quantity, cost value, and alert status for every product per outlet.</p>
                    </div>
                    <div class="glass rounded-2xl p-5">
                        <div class="font-semibold text-slate-200 text-sm mb-2 flex items-center gap-2">
                            <i class="fas fa-exchange-alt text-teal-400"></i> Movement Log
                        </div>
                        <p class="text-slate-400 text-sm leading-relaxed">Every sale, manual adjustment, and transfer is recorded at <code>Inventory → Movements</code> with timestamps and reasons.</p>
                    </div>
                </div>

                <h3 class="subsection-title">Movement Types</h3>
                <div class="glass rounded-2xl overflow-hidden">
                    <table class="doc-table">
                        <thead><tr><th>Type</th><th>Triggered By</th><th>Effect</th></tr></thead>
                        <tbody>
                            <tr><td><span class="badge badge-red">sale</span></td><td>POS / order checkout</td><td>Decreases stock at the selling outlet</td></tr>
                            <tr><td><span class="badge badge-amber">refund</span></td><td>Order refund</td><td>Returns stock to the outlet</td></tr>
                            <tr><td><span class="badge badge-green">adjustment_in</span></td><td>Manual stock adjustment (+)</td><td>Increases stock</td></tr>
                            <tr><td><span class="badge badge-red">adjustment_out</span></td><td>Manual stock adjustment (−)</td><td>Decreases stock (write-off)</td></tr>
                            <tr><td><span class="badge badge-indigo">transfer_in</span></td><td>Stock transfer (destination)</td><td>Increases at destination outlet</td></tr>
                            <tr><td><span class="badge badge-violet">transfer_out</span></td><td>Stock transfer (source)</td><td>Decreases at source outlet</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- ================================================================
                 7. ORDERS & PAYMENTS
            ================================================================ -->
            <section id="orders" class="doc-section mb-16">
                <h2 class="section-title">
                    <i class="fas fa-receipt text-rose-400 mr-2 text-xl"></i>
                    Orders & Payments
                </h2>
                <p class="doc-p">Every transaction created through the POS or manually via <code>/admin/orders</code> appears here. Orders follow a simple lifecycle.</p>

                <div class="flex flex-wrap gap-2 mb-6 items-center">
                    <span class="badge badge-amber"><i class="fas fa-circle text-[8px]"></i> pending</span>
                    <i class="fas fa-arrow-right text-slate-600 text-xs"></i>
                    <span class="badge badge-indigo"><i class="fas fa-circle text-[8px]"></i> processing</span>
                    <i class="fas fa-arrow-right text-slate-600 text-xs"></i>
                    <span class="badge badge-green"><i class="fas fa-circle text-[8px]"></i> paid</span>
                    <i class="fas fa-arrow-right text-slate-600 text-xs"></i>
                    <span class="badge badge-red"><i class="fas fa-circle text-[8px]"></i> refunded</span>
                </div>

                <div class="module-box">
                    <div class="module-box-header">
                        <div class="module-icon bg-rose-500/15">
                            <i class="fas fa-money-bill-wave text-rose-400"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-slate-200 text-sm">Order Actions</div>
                        </div>
                    </div>
                    <div class="module-box-body">
                        <ul class="checklist">
                            <li><i class="fas fa-check-circle"></i> <span><strong class="text-slate-300">Process Payment</strong> — Attach a payment record and move the order from <em>pending</em> to <em>paid</em>. Accessible at <code>Orders → Process Payment</code>.</span></li>
                            <li><i class="fas fa-check-circle"></i> <span><strong class="text-slate-300">Refund</strong> — Issue a full or partial refund. Stock is automatically returned to the outlet and the order status changes to <em>refunded</em>.</span></li>
                            <li><i class="fas fa-check-circle"></i> <span><strong class="text-slate-300">Add / Remove Items</strong> — Modify open orders before payment is processed.</span></li>
                            <li><i class="fas fa-check-circle"></i> <span><strong class="text-slate-300">Print Receipt</strong> — Generate a printable receipt from <code>Orders → Print</code>. Optimised for 80 mm thermal printers.</span></li>
                            <li><i class="fas fa-check-circle"></i> <span><strong class="text-slate-300">Payments Tab</strong> — Review all payment transactions separate from orders at <code>Orders → Payments</code>.</span></li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- ================================================================
                 8. CUSTOMERS
            ================================================================ -->
            <section id="customers" class="doc-section mb-16">
                <h2 class="section-title">
                    <i class="fas fa-users text-sky-400 mr-2 text-xl"></i>
                    Customers
                </h2>
                <p class="doc-p">Build a customer database and run a loyalty programme to increase repeat visits. Manage customers at <code>/admin/customers</code>.</p>

                <div class="module-box">
                    <div class="module-box-header">
                        <div class="module-icon bg-sky-500/15">
                            <i class="fas fa-id-card text-sky-400"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-slate-200 text-sm">Customer Features</div>
                        </div>
                    </div>
                    <div class="module-box-body">
                        <table class="doc-table">
                            <thead><tr><th>Feature</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td class="text-slate-300 font-medium">Profile</td><td>Name, phone, email, address and notes stored per customer.</td></tr>
                                <tr><td class="text-slate-300 font-medium">Order History</td><td>All orders linked to the customer are visible on their profile page.</td></tr>
                                <tr><td class="text-slate-300 font-medium">Loyalty Enrolment</td><td>Click <strong>Enroll</strong> on a customer to activate the loyalty programme. Points are credited on each purchase.</td></tr>
                                <tr><td class="text-slate-300 font-medium">Unenroll</td><td>Remove a customer from the loyalty programme without deleting their record.</td></tr>
                                <tr><td class="text-slate-300 font-medium">Search & Filter</td><td>Instantly search by name, phone, or email across thousands of records.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="callout callout-tip">
                    <i class="fas fa-lightbulb callout-icon"></i>
                    <span>During checkout in the POS, start typing the customer's name or phone to attach them to the transaction and automatically credit loyalty points.</span>
                </div>
            </section>

            <!-- ================================================================
                 9. OUTLETS
            ================================================================ -->
            <section id="outlets" class="doc-section mb-16">
                <h2 class="section-title">
                    <i class="fas fa-store text-orange-400 mr-2 text-xl"></i>
                    Outlets
                </h2>
                <p class="doc-p">An outlet represents a physical store or branch. Stock, cashier shifts, and sales are managed per outlet. Manage at <code>/admin/outlets</code>.</p>

                <div class="grid sm:grid-cols-2 gap-4 mb-6">
                    <div class="glass rounded-2xl p-5">
                        <div class="font-semibold text-slate-200 text-sm mb-2">Creating an Outlet</div>
                        <p class="text-slate-400 text-sm leading-relaxed">Provide a name, address, phone number and assign a manager. Each outlet has its own independent stock levels.</p>
                    </div>
                    <div class="glass rounded-2xl p-5">
                        <div class="font-semibold text-slate-200 text-sm mb-2">Multiple Outlets</div>
                        <p class="text-slate-400 text-sm leading-relaxed">Staff can be restricted to specific outlets via the Roles & Permissions system. Reports can be filtered per outlet.</p>
                    </div>
                </div>

                <div class="callout callout-warning">
                    <i class="fas fa-exclamation-triangle callout-icon"></i>
                    <span>Deleting an outlet is <strong>permanent</strong> and will affect historical sales data. Deactivate an outlet instead of deleting it if you plan to re-open it later.</span>
                </div>
            </section>

            <!-- ================================================================
                 10. REPORTS
            ================================================================ -->
            <section id="reports" class="doc-section mb-16">
                <h2 class="section-title">
                    <i class="fas fa-chart-bar text-purple-400 mr-2 text-xl"></i>
                    Reports
                </h2>
                <p class="doc-p">The Reports module (<code>/admin/reports</code>) provides four analytical views to help you make data-driven decisions.</p>

                <div class="space-y-4">
                    <div class="glass rounded-2xl p-5 flex gap-4">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/15 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-chart-line text-purple-400 text-sm"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-slate-200 text-sm mb-1">Sales Report <code class="text-xs">/reports/sales</code></div>
                            <p class="text-slate-400 text-sm leading-relaxed">Revenue, order count, average order value, and payment method breakdown. Filter by date range and outlet. Export to CSV.</p>
                        </div>
                    </div>
                    <div class="glass rounded-2xl p-5 flex gap-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/15 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-box text-amber-400 text-sm"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-slate-200 text-sm mb-1">Product Report <code class="text-xs">/reports/products</code></div>
                            <p class="text-slate-400 text-sm leading-relaxed">Units sold, revenue per product, gross margin, and top-selling items ranked. Identify slow-moving stock quickly.</p>
                        </div>
                    </div>
                    <div class="glass rounded-2xl p-5 flex gap-4">
                        <div class="w-10 h-10 rounded-xl bg-sky-500/15 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-users text-sky-400 text-sm"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-slate-200 text-sm mb-1">Customer Report <code class="text-xs">/reports/customers</code></div>
                            <p class="text-slate-400 text-sm leading-relaxed">Purchase frequency, lifetime value, loyalty point balances, and top spenders. Great for targeted promotions.</p>
                        </div>
                    </div>
                    <div class="glass rounded-2xl p-5 flex gap-4">
                        <div class="w-10 h-10 rounded-xl bg-teal-500/15 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-warehouse text-teal-400 text-sm"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-slate-200 text-sm mb-1">Inventory Report <code class="text-xs">/reports/inventory</code></div>
                            <p class="text-slate-400 text-sm leading-relaxed">Current stock valuation, turnover rates, and products below minimum threshold. Essential for purchase planning.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ================================================================
                 11. SETTINGS
            ================================================================ -->
            <section id="settings" class="doc-section mb-16">
                <h2 class="section-title">
                    <i class="fas fa-cog text-slate-400 mr-2 text-xl"></i>
                    Settings
                </h2>
                <p class="doc-p">Configure your store, email, tax, and currency from <code>/admin/settings</code>. Requires the <code>manage settings</code> permission.</p>

                <div class="glass rounded-2xl overflow-hidden mb-6">
                    <table class="doc-table">
                        <thead><tr><th>Section</th><th>Route</th><th>What You Configure</th></tr></thead>
                        <tbody>
                            <tr><td class="text-slate-200 font-medium">General</td><td><code>settings/</code></td><td>Business name, logo, address, phone, timezone</td></tr>
                            <tr><td class="text-slate-200 font-medium">Email</td><td><code>settings/email</code></td><td>SMTP credentials for sending receipts and notifications</td></tr>
                            <tr><td class="text-slate-200 font-medium">Tax</td><td><code>settings/tax</code></td><td>Tax percentage, tax name, inclusive vs exclusive mode</td></tr>
                            <tr><td class="text-slate-200 font-medium">Currency</td><td><code>settings/currency</code></td><td>Currency symbol, decimal separator, thousand separator</td></tr>
                            <tr><td class="text-slate-200 font-medium">Backup</td><td><code>settings/backup</code></td><td>Automated backup schedule and destination</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="callout callout-info">
                    <i class="fas fa-info-circle callout-icon"></i>
                    <span>Changes to tax settings affect new orders only. Existing orders retain the tax rate that was active when they were created.</span>
                </div>
            </section>

            <!-- ================================================================
                 12. USERS & ROLES
            ================================================================ -->
            <section id="users-roles" class="doc-section mb-16">
                <h2 class="section-title">
                    <i class="fas fa-user-shield text-rose-400 mr-2 text-xl"></i>
                    Users & Roles
                </h2>
                <p class="doc-p">Control who can do what inside your system. Requires the <code>manage users</code> permission. Manage at <code>/admin/users</code>, <code>/admin/roles</code>, and <code>/admin/permissions</code>.</p>

                <div class="module-box">
                    <div class="module-box-header">
                        <div class="module-icon bg-rose-500/15">
                            <i class="fas fa-sitemap text-rose-400"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-slate-200 text-sm">How It Works</div>
                            <div class="text-slate-500 text-xs mt-0.5">Users → Roles → Permissions</div>
                        </div>
                    </div>
                    <div class="module-box-body space-y-4">
                        <p class="text-slate-400 text-sm leading-relaxed">The system uses a <strong class="text-slate-300">Role-Based Access Control (RBAC)</strong> model. Each user is assigned one or more roles. Each role has one or more permissions attached to it.</p>
                        <div class="grid sm:grid-cols-3 gap-3">
                            <div class="glass rounded-xl p-4">
                                <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Users</div>
                                <p class="text-slate-400 text-xs leading-relaxed">Individual staff accounts with login credentials. Assign roles during creation or edit.</p>
                            </div>
                            <div class="glass rounded-xl p-4">
                                <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Roles</div>
                                <p class="text-slate-400 text-xs leading-relaxed">Groups of permissions (e.g. <em>Cashier</em>, <em>Manager</em>, <em>Accountant</em>). Assign multiple permissions per role.</p>
                            </div>
                            <div class="glass rounded-xl p-4">
                                <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Permissions</div>
                                <p class="text-slate-400 text-xs leading-relaxed">Granular access controls like <code>manage users</code>, <code>view reports</code>, <code>manage settings</code>.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <h3 class="subsection-title mt-6">Built-in Roles</h3>
                <div class="glass rounded-2xl overflow-hidden">
                    <table class="doc-table">
                        <thead><tr><th>Role</th><th>Typical Permissions</th><th>Notes</th></tr></thead>
                        <tbody>
                            <tr><td><span class="badge badge-red">super_admin</span></td><td>All permissions system-wide</td><td>Can manage tenants. Only assignable by other super_admins.</td></tr>
                            <tr><td><span class="badge badge-indigo">admin</span></td><td>All tenant-level permissions</td><td>Full control within their tenant but cannot manage tenants.</td></tr>
                            <tr><td><span class="badge badge-amber">manager</span></td><td>Reports, inventory, orders, customers</td><td>Cannot change settings or manage users.</td></tr>
                            <tr><td><span class="badge badge-green">cashier</span></td><td>POS, orders, customers (read)</td><td>POS-only access; cannot view reports or adjust settings.</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- ================================================================
                 13. TENANTS
            ================================================================ -->
            <section id="tenants" class="doc-section mb-16">
                <h2 class="section-title">
                    <i class="fas fa-building text-indigo-400 mr-2 text-xl"></i>
                    Tenants
                    <span class="badge badge-red ml-3 text-xs align-middle">super_admin only</span>
                </h2>
                <p class="doc-p">A <strong class="text-slate-200">Tenant</strong> is a completely isolated business account (e.g. a franchise, a client company, or a separate brand). Only users with the <code>super_admin</code> role can manage tenants at <code>/admin/tenants</code>.</p>

                <div class="grid sm:grid-cols-2 gap-4 mb-6">
                    <div class="glass rounded-2xl p-5">
                        <div class="font-semibold text-slate-200 text-sm mb-2 flex items-center gap-2">
                            <i class="fas fa-plus-circle text-indigo-400"></i> Create Tenant
                        </div>
                        <p class="text-slate-400 text-sm leading-relaxed">Provide the business name, owner email, and subscription plan. A fresh isolated database/schema is provisioned automatically.</p>
                    </div>
                    
                    <div class="glass rounded-2xl p-5">
                        <div class="font-semibold text-slate-200 text-sm mb-2 flex items-center gap-2">
                            <i class="fas fa-toggle-on text-indigo-400"></i> Activate / Suspend
                        </div>
                        <p class="text-slate-400 text-sm leading-relaxed">Deactivating a tenant prevents all logins for that business. No data is deleted. Reactivate at any time.</p>
                    </div>
                    <div class="glass rounded-2xl p-5">
                        <div class="font-semibold text-slate-200 text-sm mb-2 flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-indigo-400"></i> Subscription Management
                        </div>
                        <p class="text-slate-400 text-sm leading-relaxed">Extend or change the subscription plan for any tenant without affecting their data. Expired tenants see the plan selection page on next login.</p>
                    </div>
                    <div class="glass rounded-2xl p-5">
                        <div class="font-semibold text-slate-200 text-sm mb-2 flex items-center gap-2">
                            <i class="fas fa-eye text-indigo-400"></i> Tenant Isolation
                        </div>
                        <p class="text-slate-400 text-sm leading-relaxed">Each tenant's data is completely separate. Users, products, orders, and settings are never shared between tenants.</p>
                    </div>
                </div>
            </section>

            <!-- ================================================================
                 14. ACTIVITY LOGS
            ================================================================ -->
            <section id="activity-logs" class="doc-section mb-16">
                <h2 class="section-title">
                    <i class="fas fa-history text-cyan-400 mr-2 text-xl"></i>
                    Activity Logs
                </h2>
                <p class="doc-p">Every significant action — creating an order, adjusting stock, changing settings — is recorded in the audit log. Requires the <code>view activity logs</code> permission. Accessible at <code>/admin/logs</code>.</p>

                <div class="glass rounded-2xl overflow-hidden mb-6">
                    <table class="doc-table">
                        <thead><tr><th>Column</th><th>Description</th></tr></thead>
                        <tbody>
                            <tr><td class="text-slate-300 font-medium">User</td><td>The staff member who performed the action</td></tr>
                            <tr><td class="text-slate-300 font-medium">Action</td><td>The event type (e.g. <code>order.created</code>, <code>product.updated</code>)</td></tr>
                            <tr><td class="text-slate-300 font-medium">Subject</td><td>The record that was affected (model type + ID)</td></tr>
                            <tr><td class="text-slate-300 font-medium">Changes</td><td>Before/after values for edits (where applicable)</td></tr>
                            <tr><td class="text-slate-300 font-medium">IP Address</td><td>The IP from which the action was performed</td></tr>
                            <tr><td class="text-slate-300 font-medium">Timestamp</td><td>Date and time of the event</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-wrap gap-3">
                    <div class="glass rounded-xl px-4 py-3 flex items-center gap-3 text-sm">
                        <i class="fas fa-download text-cyan-400"></i>
                        <span class="text-slate-300">Export logs to CSV via <code>Logs → Export</code></span>
                    </div>
                    <div class="glass rounded-xl px-4 py-3 flex items-center gap-3 text-sm">
                        <i class="fas fa-trash text-rose-400"></i>
                        <span class="text-slate-300">Clear all logs via <code>Logs → Clear</code> (irreversible)</span>
                    </div>
                </div>

                <div class="callout callout-danger mt-4">
                    <i class="fas fa-radiation-alt callout-icon"></i>
                    <span>Clearing logs is <strong>permanent and cannot be undone</strong>. Export a copy before clearing if you need them for compliance or auditing.</span>
                </div>
            </section>

            <!-- ================================================================
                 15. PERMISSIONS REFERENCE
            ================================================================ -->
            <section id="permissions-ref" class="doc-section mb-16">
                <h2 class="section-title">
                    <i class="fas fa-key text-yellow-400 mr-2 text-xl"></i>
                    Permissions Reference
                </h2>
                <p class="doc-p">All available permission strings that can be assigned to roles.</p>

                <div class="glass rounded-2xl overflow-hidden">
                    <table class="doc-table">
                        <thead><tr><th>Permission</th><th>Grants Access To</th></tr></thead>
                        <tbody>
                            <tr><td><code>manage settings</code></td><td>View and edit all settings pages</td></tr>
                            <tr><td><code>manage users</code></td><td>CRUD for users, roles, and permissions</td></tr>
                            <tr><td><code>view activity logs</code></td><td>Read, export, and clear activity logs</td></tr>
                            <tr><td><code>manage products</code></td><td>Create, edit, delete products and categories</td></tr>
                            <tr><td><code>manage inventory</code></td><td>Stock adjustments and transfers</td></tr>
                            <tr><td><code>manage orders</code></td><td>Create, edit, refund, and process orders</td></tr>
                            <tr><td><code>manage customers</code></td><td>CRUD for customers and loyalty enrolment</td></tr>
                            <tr><td><code>manage outlets</code></td><td>Create and manage outlet profiles</td></tr>
                            <tr><td><code>view reports</code></td><td>Access all report pages and data exports</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- ================================================================
                 16. FAQ
            ================================================================ -->
            <section id="faq" class="doc-section mb-16">
                <h2 class="section-title">
                    <i class="fas fa-question-circle text-green-400 mr-2 text-xl"></i>
                    Frequently Asked Questions
                </h2>

                <div class="space-y-3" id="faq-list">

                    <details class="glass rounded-2xl overflow-hidden group">
                        <summary class="flex items-center justify-between p-5 cursor-pointer list-none select-none">
                            <span class="font-semibold text-slate-200 text-sm">How do I reset a user's password?</span>
                            <i class="fas fa-chevron-down text-slate-500 text-xs transition-transform group-open:rotate-180"></i>
                        </summary>
                        <div class="px-5 pb-5 text-slate-400 text-sm leading-relaxed border-t border-white/6 pt-4">
                            Go to <code>Users → &lt;user&gt; → Edit</code> and set a new password. Alternatively, the user can request a reset link from the login page at <code>/forgot-password</code>.
                        </div>
                    </details>

                    <details class="glass rounded-2xl overflow-hidden group">
                        <summary class="flex items-center justify-between p-5 cursor-pointer list-none select-none">
                            <span class="font-semibold text-slate-200 text-sm">Can I use the POS on a tablet?</span>
                            <i class="fas fa-chevron-down text-slate-500 text-xs transition-transform group-open:rotate-180"></i>
                        </summary>
                        <div class="px-5 pb-5 text-slate-400 text-sm leading-relaxed border-t border-white/6 pt-4">
                            Yes. The POS interface is fully responsive and optimised for touch screens. Open <code>/admin/pos</code> in a tablet browser. For a kiosk-like experience, use the browser's full-screen mode.
                        </div>
                    </details>

                    <details class="glass rounded-2xl overflow-hidden group">
                        <summary class="flex items-center justify-between p-5 cursor-pointer list-none select-none">
                            <span class="font-semibold text-slate-200 text-sm">How do I print receipts without a printer dialog?</span>
                            <i class="fas fa-chevron-down text-slate-500 text-xs transition-transform group-open:rotate-180"></i>
                        </summary>
                        <div class="px-5 pb-5 text-slate-400 text-sm leading-relaxed border-t border-white/6 pt-4">
                            Configure your operating system to set the thermal printer as the default printer. The receipt page includes a <code>@media print</code> stylesheet that hides UI chrome automatically. Save the receipt as PDF by choosing "Save as PDF" in the browser's print dialog.
                        </div>
                    </details>

                    <details class="glass rounded-2xl overflow-hidden group">
                        <summary class="flex items-center justify-between p-5 cursor-pointer list-none select-none">
                            <span class="font-semibold text-slate-200 text-sm">What happens when my subscription expires?</span>
                            <i class="fas fa-chevron-down text-slate-500 text-xs transition-transform group-open:rotate-180"></i>
                        </summary>
                        <div class="px-5 pb-5 text-slate-400 text-sm leading-relaxed border-t border-white/6 pt-4">
                            You will be redirected to <code>/subscription/expired</code> upon login. You can still access the plan selection page at <code>/subscription/plans</code> to renew without losing any data.
                        </div>
                    </details>

                    <details class="glass rounded-2xl overflow-hidden group">
                        <summary class="flex items-center justify-between p-5 cursor-pointer list-none select-none">
                            <span class="font-semibold text-slate-200 text-sm">Can I have multiple cashiers on the same outlet?</span>
                            <i class="fas fa-chevron-down text-slate-500 text-xs transition-transform group-open:rotate-180"></i>
                        </summary>
                        <div class="px-5 pb-5 text-slate-400 text-sm leading-relaxed border-t border-white/6 pt-4">
                            Yes. Multiple staff accounts with the <em>cashier</em> role can be active simultaneously. Each sale is recorded with the staff member's user ID for accountability in the activity log.
                        </div>
                    </details>

                    <details class="glass rounded-2xl overflow-hidden group">
                        <summary class="flex items-center justify-between p-5 cursor-pointer list-none select-none">
                            <span class="font-semibold text-slate-200 text-sm">How do I migrate data from another POS system?</span>
                            <i class="fas fa-chevron-down text-slate-500 text-xs transition-transform group-open:rotate-180"></i>
                        </summary>
                        <div class="px-5 pb-5 text-slate-400 text-sm leading-relaxed border-t border-white/6 pt-4">
                            Currently, bulk import is via CSV using the database seeder pipeline or a direct database import. Contact your system administrator to run a data migration script. Product and customer imports are the most commonly requested.
                        </div>
                    </details>

                </div>
            </section>

            <!-- ── Footer ── -->
            <div class="section-divider mb-8" style="height:1px; background: linear-gradient(90deg, transparent, rgba(99,102,241,0.3), transparent);"></div>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-16">
                <div>
                    <div class="text-sm text-slate-400">Was this documentation helpful?</div>
                    <div class="flex gap-2 mt-2">
                        <button onclick="showFeedback(true)" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 hover:bg-emerald-500/20 transition-all">
                            <i class="fas fa-thumbs-up"></i> Yes, helpful
                        </button>
                        <button onclick="showFeedback(false)" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold text-slate-400 bg-white/5 border border-white/10 hover:bg-white/10 transition-all">
                            <i class="fas fa-thumbs-down"></i> Needs improvement
                        </button>
                    </div>
                    <div id="feedback-msg" class="hidden text-xs text-slate-500 mt-2 italic">Thanks for your feedback!</div>
                </div>
                <a href="/" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-br from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 transition-all shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:-translate-y-0.5">
                    <i class="fas fa-home text-xs"></i> Back to Home
                </a>
            </div>

        </div><!-- /doc-content -->
    </main>

    <!-- ============================================================
         RIGHT: On-page Table of Contents (large screens)
    ============================================================ -->
    <aside class="hidden xl:block w-56 flex-shrink-0 py-14 pr-6">
        <div class="sticky top-14">
            <div class="text-xs font-bold text-slate-600 uppercase tracking-wider mb-3">On This Page</div>
            <nav class="space-y-1" id="toc-nav">
                <a href="#getting-started" class="block text-xs text-slate-500 hover:text-indigo-400 py-1 transition-colors border-l-2 border-transparent hover:border-indigo-500/40 pl-2">Getting Started</a>
                <a href="#quick-setup" class="block text-xs text-slate-500 hover:text-indigo-400 py-1 transition-colors border-l-2 border-transparent hover:border-indigo-500/40 pl-2">Quick Setup</a>
                <a href="#dashboard" class="block text-xs text-slate-500 hover:text-indigo-400 py-1 transition-colors border-l-2 border-transparent hover:border-indigo-500/40 pl-2">Dashboard</a>
                <a href="#pos" class="block text-xs text-slate-500 hover:text-indigo-400 py-1 transition-colors border-l-2 border-transparent hover:border-indigo-500/40 pl-2">POS</a>
                <a href="#products" class="block text-xs text-slate-500 hover:text-indigo-400 py-1 transition-colors border-l-2 border-transparent hover:border-indigo-500/40 pl-2">Products & Categories</a>
                <a href="#inventory" class="block text-xs text-slate-500 hover:text-indigo-400 py-1 transition-colors border-l-2 border-transparent hover:border-indigo-500/40 pl-2">Inventory</a>
                <a href="#orders" class="block text-xs text-slate-500 hover:text-indigo-400 py-1 transition-colors border-l-2 border-transparent hover:border-indigo-500/40 pl-2">Orders & Payments</a>
                <a href="#customers" class="block text-xs text-slate-500 hover:text-indigo-400 py-1 transition-colors border-l-2 border-transparent hover:border-indigo-500/40 pl-2">Customers</a>
                <a href="#outlets" class="block text-xs text-slate-500 hover:text-indigo-400 py-1 transition-colors border-l-2 border-transparent hover:border-indigo-500/40 pl-2">Outlets</a>
                <a href="#reports" class="block text-xs text-slate-500 hover:text-indigo-400 py-1 transition-colors border-l-2 border-transparent hover:border-indigo-500/40 pl-2">Reports</a>
                <a href="#settings" class="block text-xs text-slate-500 hover:text-indigo-400 py-1 transition-colors border-l-2 border-transparent hover:border-indigo-500/40 pl-2">Settings</a>
                <a href="#users-roles" class="block text-xs text-slate-500 hover:text-indigo-400 py-1 transition-colors border-l-2 border-transparent hover:border-indigo-500/40 pl-2">Users & Roles</a>
                <a href="#tenants" class="block text-xs text-slate-500 hover:text-indigo-400 py-1 transition-colors border-l-2 border-transparent hover:border-indigo-500/40 pl-2">Tenants</a>
                <a href="#activity-logs" class="block text-xs text-slate-500 hover:text-indigo-400 py-1 transition-colors border-l-2 border-transparent hover:border-indigo-500/40 pl-2">Activity Logs</a>
                <a href="#permissions-ref" class="block text-xs text-slate-500 hover:text-indigo-400 py-1 transition-colors border-l-2 border-transparent hover:border-indigo-500/40 pl-2">Permissions Ref</a>
                <a href="#faq" class="block text-xs text-slate-500 hover:text-indigo-400 py-1 transition-colors border-l-2 border-transparent hover:border-indigo-500/40 pl-2">FAQ</a>
            </nav>
        </div>
    </aside>

</div><!-- /flex wrapper -->


<script>
// ── Read Progress Bar ──────────────────────────────────────
const progress = document.getElementById('read-progress');
window.addEventListener('scroll', () => {
    const el = document.documentElement;
    const pct = (el.scrollTop / (el.scrollHeight - el.clientHeight)) * 100;
    progress.style.width = Math.min(pct, 100) + '%';
});

// ── Mobile Sidebar ─────────────────────────────────────────
function openSidebar() {
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('sidebar-overlay').classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebar-overlay').classList.remove('active');
    document.body.style.overflow = '';
}

// Close sidebar on nav link click (mobile)
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', () => {
        if (window.innerWidth < 1024) closeSidebar();
    });
});

// ── Active nav link on scroll ──────────────────────────────
const sections = document.querySelectorAll('.doc-section');
const navLinks = document.querySelectorAll('.nav-link[data-section]');
const tocLinks = document.querySelectorAll('#toc-nav a');

const intersectMap = new Map();
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => intersectMap.set(entry.target.id, entry.isIntersecting));

    let active = null;
    sections.forEach(s => { if (intersectMap.get(s.id)) active = s.id; });

    navLinks.forEach(link => {
        link.classList.toggle('active', link.dataset.section === active);
    });
    tocLinks.forEach(link => {
        const isActive = link.getAttribute('href') === '#' + active;
        link.classList.toggle('text-indigo-400', isActive);
        link.classList.toggle('border-indigo-500', isActive);
        link.classList.toggle('text-slate-500', !isActive);
        link.classList.toggle('border-transparent', !isActive);
    });
}, { rootMargin: '-20% 0px -60% 0px', threshold: 0 });

sections.forEach(s => observer.observe(s));

// ── Smooth scroll for anchors ──────────────────────────────
document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
        const target = document.querySelector(a.getAttribute('href'));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

// ── Live search ────────────────────────────────────────────
const searchInput = document.getElementById('doc-search');
searchInput.addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    if (!q) {
        // Remove all highlights
        document.querySelectorAll('mark').forEach(m => {
            m.outerHTML = m.innerHTML;
        });
        document.querySelectorAll('.doc-section').forEach(s => s.style.display = '');
        return;
    }

    document.querySelectorAll('.doc-section').forEach(s => {
        const text = s.textContent.toLowerCase();
        s.style.display = text.includes(q) ? '' : 'none';
    });
});

// Reset search when cleared
searchInput.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        this.value = '';
        this.dispatchEvent(new Event('input'));
    }
});

// ── Feedback ───────────────────────────────────────────────
function showFeedback(positive) {
    const msg = document.getElementById('feedback-msg');
    msg.classList.remove('hidden');
    msg.textContent = positive
        ? 'Thanks! Glad it was helpful.'
        : 'Thanks for letting us know. We\'ll improve it.';
}

// ── details/summary accordion smooth ──────────────────────
document.querySelectorAll('details').forEach(d => {
    d.addEventListener('toggle', () => {
        if (d.open) {
            // Close siblings
            d.closest('#faq-list')?.querySelectorAll('details').forEach(other => {
                if (other !== d) other.open = false;
            });
        }
    });
});
</script>

</body>
</html>
