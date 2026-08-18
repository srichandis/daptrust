<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminCredential;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $admin = AdminCredential::ensureSeeded();

        if (hash_equals($admin->username, $credentials['username'])
            && Hash::check($credentials['password'], $admin->password)) {
            $request->session()->regenerate();
            $request->session()->put('admin_authenticated', true);

            if ($request->boolean('remember')) {
                $token = Str::random(60);
                $admin->update(['remember_token' => Hash::make($token)]);
                cookie()->queue(cookie('admin_remember', $token, 60 * 24 * 30, '/', null, $request->secure(), true, false, 'lax'));
            }

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()
            ->withInput($request->only('username'))
            ->withErrors(['username' => 'Invalid username or password.']);
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('admin_authenticated');
        $request->session()->regenerateToken();

        AdminCredential::query()->update(['remember_token' => null]);
        cookie()->queue(cookie('admin_remember', null, -1));

        return redirect()->route('admin.login');
    }

    public function showForgotForm(): View|RedirectResponse
    {
        // Recovery is disabled until an answer is configured in .env.
        if ((string) config('admin.security_answer') === '') {
            return redirect()->route('admin.login')
                ->with('status', 'Password recovery is not enabled.');
        }

        return view('admin.forgot', [
            'question' => AdminCredential::ensureSeeded()->security_question,
        ]);
    }

    public function forgot(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'security_answer' => ['required', 'string'],
        ]);

        $admin = AdminCredential::ensureSeeded();

        $answerMatches = Hash::check($data['security_answer'], (string) $admin->security_answer);

        if (! hash_equals($admin->username, $data['username']) || ! $answerMatches) {
            return back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => 'Those details do not match our records.']);
        }

        $request->session()->put('admin_reset_allowed', true);

        return redirect()->route('admin.reset');
    }

    public function showResetForm(Request $request): View|RedirectResponse
    {
        if ($request->session()->get('admin_reset_allowed') !== true) {
            return redirect()->route('admin.forgot');
        }

        return view('admin.reset');
    }

    public function reset(Request $request): RedirectResponse
    {
        if ($request->session()->get('admin_reset_allowed') !== true) {
            return redirect()->route('admin.forgot');
        }

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        AdminCredential::ensureSeeded()->update([
            'password' => Hash::make($data['password']),
            'remember_token' => null,
        ]);
        $request->session()->forget('admin_reset_allowed');
        cookie()->queue(cookie('admin_remember', null, -1));

        return redirect()
            ->route('admin.login')
            ->with('status', 'Password updated. You can now sign in with the new password.');
    }
}
