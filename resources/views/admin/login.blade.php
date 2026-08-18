<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in — {{ config('admin.trust_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-900 px-4 py-12 font-sans antialiased">
<div class="w-full max-w-md">
    <div class="mb-6 text-center">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-500 text-2xl font-bold text-white shadow-xl shadow-emerald-500/30">D</div>
        <h1 class="mt-4 text-2xl font-semibold text-white">{{ config('admin.trust_name') }}</h1>
        <p class="text-sm text-slate-300">Trust accounts — admin sign in</p>
    </div>

    <div class="rounded-2xl bg-white p-8 shadow-2xl">
        @if (session('status'))
            <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}" class="space-y-5">
            @csrf
            <div>
                <label for="username" class="mb-1.5 block text-sm font-medium text-slate-700">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus autocomplete="username"
                       class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            </div>
            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
                <div class="relative">
                    <input type="password" id="password" name="password" required autocomplete="current-password"
                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 pr-11 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    <button type="button" onclick="togglePassword('password', this)"
                            class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-slate-400 transition hover:text-slate-600"
                            aria-label="Show password">
                        <svg id="eye-open" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg id="eye-closed" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex cursor-pointer items-center gap-2 text-slate-600">
                    <input type="checkbox" name="remember" value="1"
                           class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    Remember me
                </label>
                <a href="{{ route('admin.forgot') }}" class="font-medium text-emerald-600 transition hover:text-emerald-700">
                    Forgot password?
                </a>
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/25 transition hover:bg-emerald-700 active:scale-[0.99]">
                Sign in
            </button>
        </form>
    </div>

    <p class="mt-6 text-center text-xs text-slate-400">
        {{ config('admin.trust_name') }} · Trust Accounts
    </p>
</div>

<script>
    function togglePassword(id, button) {
        const input = document.getElementById(id);
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        button.querySelector('#eye-open').classList.toggle('hidden', isPassword);
        button.querySelector('#eye-closed').classList.toggle('hidden', !isPassword);
    }
</script>
</body>
</html>
