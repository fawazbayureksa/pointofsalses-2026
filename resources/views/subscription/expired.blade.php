<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subscription Expired — {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #060b18; }
        .gradient-text {
            background: linear-gradient(135deg, #818cf8 0%, #6366f1 25%, #10b981 60%, #34d399 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .glass-dark {
            background: rgba(13,17,35,0.8); backdrop-filter: blur(16px);
            border: 1px solid rgba(99,102,241,0.2);
        }
        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            transition: all 0.3s ease; box-shadow: 0 4px 20px rgba(99,102,241,0.35);
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 30px rgba(99,102,241,0.5); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4">
    <!-- Background orbs -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute w-96 h-96 bg-indigo-600/15 rounded-full blur-3xl -top-20 -left-20"></div>
        <div class="absolute w-80 h-80 bg-violet-600/10 rounded-full blur-3xl bottom-20 right-0"></div>
    </div>

    <div class="w-full max-w-lg relative z-10 text-center">
        <!-- Logo -->
        <a href="/" class="flex items-center justify-center gap-3 mb-10">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                <i class="fas fa-cash-register text-white"></i>
            </div>
            <span class="font-bold text-xl text-white tracking-tight">{{ config('app.name', 'POS ERP') }}</span>
        </a>

        <div class="glass-dark rounded-3xl p-10 shadow-2xl shadow-indigo-900/30">
            <!-- Icon -->
            <div class="w-20 h-20 rounded-2xl bg-amber-500/15 border border-amber-500/20 flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-hourglass-end text-amber-400 text-3xl"></i>
            </div>

            <h1 class="text-3xl font-black text-white mb-3">
                @if(auth()->user()?->tenant?->subscription_status === 'trial')
                    Your <span class="gradient-text">Free Trial</span> Has Ended
                @else
                    Your <span class="gradient-text">Subscription</span> Has Expired
                @endif
            </h1>

            <p class="text-slate-400 text-sm leading-relaxed mb-8">
                @if(auth()->user()?->tenant?->subscription_status === 'trial')
                    Your 14-day free trial has ended. Upgrade to a paid plan to continue using
                    {{ config('app.name') }} and keep all your data.
                @else
                    Your subscription has expired. Renew your plan to restore full access to
                    {{ config('app.name') }} and continue managing your business.
                @endif
            </p>

            <!-- Plan options -->
            <div class="grid grid-cols-2 gap-4 mb-8">
                <div class="bg-white/5 border border-white/10 rounded-2xl p-4 text-left">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Professional</p>
                    <p class="text-2xl font-black text-white mb-1">Rp 299K<span class="text-sm font-normal text-slate-400">/mo</span></p>
                    <ul class="space-y-1.5 mt-3">
                        <li class="text-xs text-slate-400 flex items-center gap-1.5"><i class="fas fa-check text-emerald-400"></i> Up to 5 Outlets</li>
                        <li class="text-xs text-slate-400 flex items-center gap-1.5"><i class="fas fa-check text-emerald-400"></i> All Modules</li>
                        <li class="text-xs text-slate-400 flex items-center gap-1.5"><i class="fas fa-check text-emerald-400"></i> Priority Support</li>
                    </ul>
                </div>
                <div class="bg-white/5 border border-white/10 rounded-2xl p-4 text-left">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Enterprise</p>
                    <p class="text-2xl font-black text-white mb-1">Custom</p>
                    <ul class="space-y-1.5 mt-3">
                        <li class="text-xs text-slate-400 flex items-center gap-1.5"><i class="fas fa-check text-emerald-400"></i> Unlimited Outlets</li>
                        <li class="text-xs text-slate-400 flex items-center gap-1.5"><i class="fas fa-check text-emerald-400"></i> All Modules</li>
                        <li class="text-xs text-slate-400 flex items-center gap-1.5"><i class="fas fa-check text-emerald-400"></i> Dedicated Support</li>
                    </ul>
                </div>
            </div>

            <a href="mailto:hello@posnerp.com?subject=Subscription%20Upgrade%20Request"
               class="btn-primary w-full py-4 rounded-2xl text-white font-bold flex items-center justify-center gap-2 text-sm">
                <i class="fas fa-credit-card"></i> Contact Us to Subscribe
            </a>

            <div class="flex gap-3 mt-4">
                <a href="/" class="flex-1 py-3 rounded-2xl bg-white/5 border border-white/10 text-slate-400 hover:text-white hover:bg-white/10 text-sm font-medium transition-colors text-center">
                    Back to Home
                </a>
                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full py-3 rounded-2xl bg-white/5 border border-white/10 text-slate-400 hover:text-white hover:bg-white/10 text-sm font-medium transition-colors">
                        Sign Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
