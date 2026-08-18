<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot password — {{ config('admin.trust_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-900 px-4 py-12 font-sans antialiased">
<div class="w-full max-w-md">
    <div class="mb-6 text-center">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-500 text-2xl font-bold text-white shadow-xl shadow-emerald-500/30">D</div>
        <h1 class="mt-4 text-2xl font-semibold text-white">{{ config('admin.trust_name') }}</h1>
        <p class="text-sm text-slate-300">Recover your password</p>
    </div>

    <div class="rounded-2xl bg-white p-8 shadow-2xl">
        @if ($errors->any())
            <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <p class="mb-5 text-sm text-slate-500">
            Answer your security question to set a new password.
        </p>

        <form method="POST" action="{{ route('admin.forgot.submit') }}" class="space-y-5">
            @csrf
            <div>
                <label for="username" class="mb-1.5 block text-sm font-medium text-slate-700">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus autocomplete="username"
                       class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            </div>
            <div>
                <label for="security_answer" class="mb-1.5 block text-sm font-medium text-slate-700">{{ $question ?: 'Security question' }}</label>
                <input type="text" id="security_answer" name="security_answer" required autocomplete="off"
                       class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/25 transition hover:bg-emerald-700 active:scale-[0.99]">
                Continue
            </button>
        </form>

        <p class="mt-5 text-center text-sm">
            <a href="{{ route('admin.login') }}" class="font-medium text-emerald-600 transition hover:text-emerald-700">Back to sign in</a>
        </p>
    </div>
</div>
</body>
</html>
