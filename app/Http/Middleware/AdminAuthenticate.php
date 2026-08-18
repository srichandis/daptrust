<?php

namespace App\Http\Middleware;

use App\Models\AdminCredential;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('admin_authenticated') === true) {
            return $next($request);
        }

        // "Remember me": re-authenticate from the persistent cookie.
        $token = $request->cookie('admin_remember');
        if ($token) {
            $admin = AdminCredential::query()->first();
            if ($admin && $admin->remember_token !== null && Hash::check($token, $admin->remember_token)) {
                $request->session()->regenerate();
                $request->session()->put('admin_authenticated', true);

                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('admin.login'));
    }
}
