<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Start Free Trial - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        .input-field {
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            color: #e2e8f0; transition: border-color 0.2s;
        }
        .input-field:focus { border-color: #6366f1; outline: none; box-shadow: 0 0 0 2px rgba(99,102,241,0.2); }
        .input-field::placeholder { color: #64748b; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen px-4 py-12">

    <!-- Background orbs -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute w-96 h-96 bg-indigo-600/15 rounded-full blur-3xl -top-20 -left-20"></div>
        <div class="absolute w-80 h-80 bg-violet-600/10 rounded-full blur-3xl bottom-20 right-0"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
        <!-- Logo -->
        <a href="/" class="flex items-center justify-center gap-3 mb-8">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                <i class="fas fa-cash-register text-white"></i>
            </div>
            <span class="font-bold text-xl text-white tracking-tight">{{ config('app.name', 'POS ERP') }}</span>
        </a>

        <div class="glass-dark rounded-3xl p-8 shadow-2xl shadow-indigo-900/30">
            <!-- Trial badge -->
            <div class="flex items-center justify-center gap-2 bg-emerald-500/10 border border-emerald-500/20 rounded-full px-4 py-2 mb-6 w-fit mx-auto">
                <i class="fas fa-gift text-emerald-400 text-sm"></i>
                <span class="text-xs font-semibold text-emerald-300">14-Day Free Trial — No Credit Card Required</span>
            </div>

            <h1 class="text-2xl font-black text-white text-center mb-1">Start your free trial</h1>
            <p class="text-slate-400 text-sm text-center mb-6">Full access to Professional features for 14 days.</p>

            @if ($errors->any())
                <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 mb-6">
                    <ul class="text-sm text-red-400 space-y-1 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-xs font-medium text-slate-400 mb-1.5">Your Full Name</label>
                    <input id="name" type="text" name="name"
                           value="{{ old('name') }}"
                           class="input-field w-full px-4 py-3 rounded-xl text-sm"
                           placeholder="e.g. Budi Santoso"
                           required autofocus autocomplete="name">
                </div>

                <div>
                    <label for="business_name" class="block text-xs font-medium text-slate-400 mb-1.5">
                        Business Name <span class="text-slate-600">(optional)</span>
                    </label>
                    <input id="business_name" type="text" name="business_name"
                           value="{{ old('business_name') }}"
                           class="input-field w-full px-4 py-3 rounded-xl text-sm"
                           placeholder="e.g. Kopi Nusantara"
                           autocomplete="organization">
                </div>

                <div>
                    <label for="email" class="block text-xs font-medium text-slate-400 mb-1.5">Work Email</label>
                    <input id="email" type="email" name="email"
                           value="{{ old('email') }}"
                           class="input-field w-full px-4 py-3 rounded-xl text-sm"
                           placeholder="you@company.com"
                           required autocomplete="email">
                </div>

                <div>
                    <label for="password" class="block text-xs font-medium text-slate-400 mb-1.5">Password</label>
                    <input id="password" type="password" name="password"
                           class="input-field w-full px-4 py-3 rounded-xl text-sm"
                           placeholder="Min. 8 characters"
                           required autocomplete="new-password">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-medium text-slate-400 mb-1.5">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation"
                           class="input-field w-full px-4 py-3 rounded-xl text-sm"
                           placeholder="Re-enter your password"
                           required autocomplete="new-password">
                </div>

                <button type="submit" class="btn-primary w-full py-3.5 rounded-2xl text-white text-sm font-bold mt-2 flex items-center justify-center gap-2">
                    <i class="fas fa-rocket"></i> Start My Free Trial
                </button>
            </form>

            <p class="text-center text-xs text-slate-500 mt-6">
                Already have an account?
                <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 font-medium">Sign in</a>
            </p>
        </div>

        <!-- What you get -->
        <div class="mt-6 grid grid-cols-3 gap-3 text-center">
            <div class="glass-dark rounded-2xl p-4">
                <i class="fas fa-check-circle text-emerald-400 text-lg mb-1"></i>
                <p class="text-xs text-slate-400">Full POS Access</p>
            </div>
            <div class="glass-dark rounded-2xl p-4">
                <i class="fas fa-check-circle text-emerald-400 text-lg mb-1"></i>
                <p class="text-xs text-slate-400">All Reports</p>
            </div>
            <div class="glass-dark rounded-2xl p-4">
                <i class="fas fa-check-circle text-emerald-400 text-lg mb-1"></i>
                <p class="text-xs text-slate-400">Cancel Anytime</p>
            </div>
        </div>
    </div>
</body>
</html>
